<?php
trait PostTagMethods
{
    public $tags, $new_tags, $old_tags, $old_cached_tags, $parsed_cached_tags = array();

    static public function find_by_tags($tags, array $options = array())
    {
        list ($sql, $params) = Post::generate_sql($tags, $options);
        return Post::find_by_sql($sql, $params);
    }

    static public function recalculate_cached_tags($id = null)
    {
        // conds = array()
        // cond_params = array()

        // sql = %{
            // UPDATE posts p SET cached_tags = (
                // SELECT array_to_string(coalesce(array(
                    // SELECT t.name
                    // FROM tags t, posts_tags pt
                    // WHERE t.id = pt.tag_id AND pt.post_id = p.id
                    // ORDER BY t.name
                // ), 'array()'::textarray()), ' ')
            // )
        // }
        // if ((id) {) {
            // conds << "WHERE p.id = ?"
            // cond_params << id
        // }

        // sql = [sql, conds].join(" ")
        // execute_sql sql, *cond_params
    }

    # new, previous and latest are History objects for cached_tags.  Split
    # the tag changes apart.
    static public function tag_changes($new, $previous, $latest)
    {
        // new_tags = new.value.scan(/\S+/)
        // old_tags = (previous.value rescue "").scan(/\S+/)
        // latest_tags = latest.value.scan(/\S+/)

        // {
            // 'added_tags' => new_tags - old_tags,
            // 'removed_tags' => old_tags - new_tags,
            // 'unchanged_tags' => new_tags & old_tags,
            // 'obsolete_added_tags' => (new_tags - old_tags) - latest_tags,
            // 'obsolete_removed_tags' => (old_tags - new_tags) & latest_tags,
        // }
    }

    public function cached_tags_undo($change, $redo_changes = false)
    {
        $current_tags = array_keys($this->parsed_cached_tags);
        $prev = $change->previous();
        
        if ($redo_changes)
                list($change, $prev) = array($prev, $change);
        
        $changes = Post::tag_changes($change, $prev, $change->latest());
        $diff = array_diff($current_tags, $changes['added_tags']);
        $new_tags = array_intersect($diff, $changes['removed_tags']);
        $this->tags = implode(' ', $new_tags);
    }

    public function cached_tags_redo($change)
    {
        $this->cached_tags_undo($change, true);
    }

    # === Parameters
    # * :tag<String>:: the tag to search for
    public function has_tag($tag)
    {
        return isset($this->parsed_cached_tags[$tag]);
    }

    # Returns the tags in a URL suitable string
    public function tag_title()
    {
        return substr(preg_replace('/\W+/', '-', $this->tags), 0, 50);
    }

    # Return the tags we display in URLs, page titles, etc.
    public function title_tags()
    {
        return $this->tags;
    }

    // public function tags()
    // {
        // return $this->cached_tags;
    // }

  # Sets the tags for the post. Does not actually save anything to the database when called.
  #
  # === Parameters
  # * :tags<String>:: a whitespace delimited list of tags
  // public function tags()
  // {=(tags)
    // self.new_tags = Tag.scan_tags(tags)

    // current_tags = cached_tags.scan(/\S+/)
    // self.touch_change_seq! if new_tags != current_tags
  // }

    # Returns all versioned tags and metatags.
    public function cached_tags_versioned()
    {
        return "rating:" . $this->rating . ' ' . $this->tags;
    }

    # Commit metatags; this is done before save, so any changes are stored normally.
    protected function commit_metatags()
    {
        foreach ($this->tags as $k => $tag) {
            switch ($tag) {
                case 'hold':
                    $this->hold();
                    unset($this->tags[$k]);
                    break;
                
                case 'unhold':
                    $this->unhold();
                    unset($this->tags[$k]);
                    break;
                
                case 'show':
                    $this->is_shown_in_index = true;
                    unset($this->tags[$k]);
                    break;
                
                case 'hide':
                    $this->is_shown_in_index = false;
                    unset($this->tags[$k]);
                    break;
                # TODO
                // case '+flag':
                    // $this->metatag_flagged = "moderator flagged";
                    // break;
                
                // case 'e':
                // case 'q':
                // case 's':
                    // $this->rating = $tag;
                    // unset($this->tags[$k]);
                    // break;
            }
        }
    }

  # Commit any tag changes to the database.  This is done after save, so any changes
  # must be made directly to the database.
    protected function commit_tags()
    {
        if ($this->new_record())
            return;
        
        $this->tags = array_filter(explode(' ', str_replace(array("\r", "\n"), '', $this->tags)));
        $this->current_tags = array_keys($this->parsed_cached_tags);
        
        if (empty($this->old_tags))
            $this->old_tags = $this->current_tags;
        elseif (!is_array($this->old_tags))
            $this->old_tags = array_filter(explode(' ', $this->old_tags));
        
        $this->commit_metatags();
        
        $meta_tags = array('-pool:', 'pool:', 'rating:', 'parent:', 'child:', 'source:');
        $ratings = array('q', 's', 'e');
        
        foreach ($this->tags as $k => $tag) {
            # To avoid preg_match.
            $is_mt = false;
            foreach ($meta_tags as $mt) {
                if (strpos($tag, $mt) === 0 || in_array($tag, $ratings)) {
                    $is_mt = true;
                    break;
                }
            }
            if (!$is_mt)
                continue;
            
            if (in_array($tag, $ratings))
                $tag = 'rating:'.$tag;
            
            $subparam = explode(':', $tag, 3);
            $metatag = array_shift($subparam);
            $param = array_shift($subparam);
            $subparam = empty($subparam) ? null : array_shift($subparam);
            
            switch($metatag) {
                case 'rating':
                    # Change rating. This will override rating selected on radio buttons.
                    if (in_array($param, $ratings))
                        $this->rating = $param;
                    unset($this->tags[$k]);
                break;
                
                case 'pool':
                    try {
                        $name = $param;
                        $seq = $subparam;
                        
                        # Workaround: I don't understand how can the pool be found when finding_by_name
                        # using the id.
                        if (ctype_digit($name))
                            $pool = Pool::find_by_id($name);
                        else
                            $pool = Pool::find_by_name($name);
                        
                        # Set :ignore_already_exists, so pool:1:2 can be used to change the sequence number
                        # of a post that already exists in the pool.
                        $options = array('user' => User::find_first(array('conditions' => array('id = ?', $this->updater_user_id))), 'ignore_already_exists' => true);
                        if ($seq)
                            $options['sequence'] = $seq;
                        
                        if (!$pool and !ctype_digit($name))
                            $pool = Pool::create(array('name' => $name, 'is_public' => false, 'user_id' => $this->updater_user_id));
                        
                        if (!$pool)
                            continue;
                        
                        if (!$pool->can_change(current_user(), null))
                            continue;
                        
                        $pool->add_post($this->id, $options);
                        
                    } catch(PostAlreadyExistsError $e) {
                    } catch (AccessDeniedError $e) {
                    }
                    unset($this->tags[$k]);
                break;
                    
                case '-pool':
                    unset($this->tags[$k]);
                    
                    $name = $param;
                    $cmd = $subparam;

                    $pool = Pool::find_by_name($name);
                    if (!$pool->can_change(current_user(), null))
                        break;

                    if ($cmd == "parent") {
                        # If we have a parent, remove ourself from the pool and add our parent in
                        # our place.    If we have no parent, do nothing and leave us in the pool.
                        if (!empty($this->parent_id)) {
                            $pool->transfer_post_to_parent($this->id, $this->parent_id);
                            break;
                        }
                    }
                    $pool && $pool->remove_post($id);
                break;
                    
                case 'source':
                    $this->source = $param;
                    unset($this->tags[$k]);
                break;
                    
                case 'parent':
                    if (is_numeric($param)) {
                        $this->parent_id = (int)$param;;
                    }
                    unset($this->tags[$k]);
                break;
                
                case 'child':
                    unset($this->tags[$k]);
                break;
                    
                default:
                    unset($this->tags[$k]);
                break;
            }
        }
        
        $new_tags = array_diff($this->tags, $this->old_tags);
        $new_tags = array_merge($new_tags, TagAlias::to_aliased($new_tags));
        $new_tags = array_merge($new_tags, TagImplication::with_implied($new_tags));
        $new_tags = array_values(array_unique($new_tags));
        
        $old_tags = array_diff($this->old_tags, $this->tags);
        
        if (empty($new_tags) && $old_tags == $this->current_tags) {
            if (!in_array('tagme', $new_tags))
                $new_tags[] = 'tagme';
            if (in_array('tagme', $old_tags)) {
                unset($old_tags[array_search('tagme', $old_tags)]);
            }
        }
        
        foreach ($old_tags as $tag) {
            if (array_key_exists($tag, $this->parsed_cached_tags))
                unset($this->parsed_cached_tags[$tag]);
            
            $tag = Tag::find_by_name($tag);
            if ($tag)
                self::execute_sql('DELETE FROM posts_tags WHERE post_id = ? AND tag_id = ?', $this->id, $tag->id);
        }
        
        foreach ($new_tags as $tag) {
            $tag = Tag::find_or_create_by_name($tag);
            $this->parsed_cached_tags[$tag->name] = $tag->type_name;
            
            self::execute_sql('INSERT IGNORE INTO posts_tags VALUES (?, ?)', $this->id, $tag->id);
        }
        
        $this->tags = $this->tag_names();
        
        $this->generate_cached_tags();
    }

    public function save_post_history()
    {
        // $new_cached_tags = $this->cached_tags_versioned();
        // if (!$this->tag_history or $this->tag_history->first()->tags != $new_cached_tags) {
          // PostTagHistory.create('post_id' => id, 'tags' => new_cached_tags,
                                // 'user_id' => Thread.current["danbooru-user_id"],
                                // 'ip_addr' => Thread.current["danbooru-ip_addr"] || "127.0.0.1")
        // }
    }

    
    public function tag_names()
    {
        $methodames = array();
        foreach(array_keys($this->parsed_cached_tags) as $tag)
            $methodames[] = $tag;
        return implode(' ', $methodames);
    }
    
    private function _parse_cached_tags($tags = null)
    {
        !$tags && $tags = $this->cached_tags;
        
        $tags = explode(' ', $tags);
        sort($tags);
        $parsed = array();
        foreach($tags as $tag) {
            $tag_type = explode(':', $tag);
            if (!isset($tag_type[1]) || $tag_type[1] === '') {
                continue;
            }
            
            $tag = $tag_type[0];
            
            $type = Tag::type_name_from_value($tag_type[1]);
            $parsed[$tag] = $type;
        }
        
        if (!$parsed)
            $parsed['tagme'] = 'general';
        
        $this->parsed_cached_tags = $parsed;
    }
    
    protected function generate_cached_tags()
    {
        $string = array();
        foreach ($this->parsed_cached_tags as $name => $type_name) {
            $type_id = isset(CONFIG()->tag_types[$name]) ? CONFIG()->tag_types[$name] : '0';
            $string[] = $name.":".$type_id;
        }
        $this->cached_tags = implode(' ', $string);
    }
}