<?php
trait PostApiMethods
{
    public $similarity;

    public function api_attributes()
    {
        $api_attributes = array('id', 'tags', 'created_at', 'creator_id', 'author', 'change', 'source', 'score', 'md5', 'file_size', 'file_url', 'is_shown_in_index', 'preview_url', 'preview_width', 'preview_height', 'actual_preview_width', 'actual_preview_height', 'sample_url', 'sample_width', 'sample_height', 'sample_file_size', 'jpeg_url', 'jpeg_width', 'jpeg_height', 'jpeg_file_size', 'rating', 'has_children', 'parent_id', 'status', 'width', 'height', 'is_held', 'frames_pending_string', 'frames_string');
        
        $api_attributes = array_fill_keys($api_attributes, '');
        
        # Creating these manually because they're not implemented yet.
        $api_attributes['frames_pending'] = $api_attributes['frames'] = array();
        # Column ´change_seq´ still not created in database.
        $api_attributes['change'] = 0;
        
        foreach (array_keys($this->attributes()) as $name) {
            if ($name == 'user_id')
                $api_attributes['creator_id'] = $this->$name;
            elseif ($name == 'sample_size')
                $api_attributes['sample_file_size'] = $this->$name;
            elseif ($name == 'jpeg_size')
                $api_attributes['jpeg_file_size'] = $this->$name;
            elseif ($name == 'cached_tags')
                $api_attributes['tags'] = $this->tags;
            elseif ($name == 'created_at')
                $api_attributes['created_at'] = strtotime($this->created_at);
            elseif ($name == 'has_children')
                $api_attributes['has_children'] = $this->has_children;
            elseif (array_key_exists($name, $api_attributes))
                $api_attributes[$name] = $this->$name;
        }
        
        if ($this->status == "deleted") {
            unset($api_attributes['sample_url']);
            unset($api_attributes['jpeg_url']);
            unset($api_attributes['file_url']);
        }

        if (($this->status == "flagged" or $this->status == "deleted" or $this->status == "pending") && $this->flag_detail) {
            $api_attributes['flag_detail'] = $this->flag_detail->api_attributes();
            $this->flag_detail->hide_user = ($this->status == "deleted" and current_user()->is_mod_or_higher());
        }
        
        # For post/similar results:
        if ($this->similarity)
            $ret['similarity'] = $this->similarity;
        
        return $api_attributes;
    }
    
    public function to_json($args = array())
    {
        return json_encode($this->api_attributes());
    }
    
    public function to_xml(array $params = [])
    {
        $params['root'] = 'post';
        $params['attributes'] = $this->api_attributes();
        return parent::to_xml($params);
    }
    
    public function api_data()
    {
        return array(
            'post' => $this,
            'tags' => Tag::batch_get_tag_types_for_posts(array($this))
        );
    }
    
    # Remove attribute from params that shouldn't be changed through the API.
    static public function filter_api_changes(&$params)
    {
        unset($params['frames']);
        unset($params['frames_warehoused']);
    }

    static public function batch_api_data(array $posts, $options = array())
    {
        foreach ($posts as $post)
            $result['posts'][] = $post->api_attributes();
        
        if (empty($options['exclude_pools'])) {
            $pool_posts = Pool::get_pool_posts_from_posts($posts);
            
            $result['pools'] = Pool::get_pools_from_pool_posts($pool_posts);
            
            foreach ($pool_posts as $pp) {
                $result['pool_posts'][] = $pp->api_attributes();
            }
        }
        
        if (empty($options['exclude_tags']))
            $result['tags'] = Tag::batch_get_tag_types_for_posts($posts);
        
        if (!empty($options['user']))
            $user = $options['user'];
        else
            $user = current_user();

        # Allow loading votes along with the posts.
        #
        # The post data is cachable and vote data isn't, so keep this data separate from the
        # main post data to make it easier to cache API output later.
        if (empty($options['exclude_votes'])) {
            $vote_map = array();
            
            if ($posts) {
                $post_ids = array();
                foreach ($posts as $p) {
                    $post_ids[] = $p->id;
                }
                
                if ($post_ids) {
                    $post_ids = implode(',', $post_ids);
                    
                    $sql = sprintf("SELECT v.* FROM post_votes v WHERE v.user_id = %d AND v.post_id IN (%s)", $user->id, $post_ids);
                    
                    $votes = PostVote::find_by_sql($sql);
                    foreach ($votes as $v) {
                        $vote_map[$v->post_id] = $v->score;
                    }
                }
            }
            $result['votes'] = $vote_map;
        }
        
        return $result;
    }
}