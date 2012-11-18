<?php
ActiveRecord::load_model(array('Note', 'FlaggedPostDetail', 'PostVote', 'TagImplication', 'Tag', 'Favorite'));
foreach (glob(dirname(__FILE__).'/post/*.php') as $trait) require $trait;

class Post extends ActiveRecord_Base
{
    use PostSqlMethods, PostCommentMethods, PostImageStoreMethods,
        PostVoteMethods, PostTagMethods, PostCountMethods,
        PostCacheMethods, PostParentMethods, PostFileMethods,
        PostChangeSequenceMethods, PostRatingMethods, PostStatusMethods,
        PostApiMethods, PostMirrorMethods, PostFrameMethods,
        PostFilenameParsingMethods;
    
    public function __call($method, $params)
    {
        switch(true) {
            # Checking status: $paramsost->is_pending();
            case (strpos($method, 'is_') === 0):
                $status = str_replace('is_', '', $method);
                return $this->status == $status;
            default:
                return parent::__call($method, $params);
        }
    }

    public function next_id()
    {
        !$this->next_id && $this->next_id = Post::select_value('SELECT id FROM posts WHERE id > ? LIMIT 1', $this->id);
        return $this->next_id;
    }

    public function previous_id()
    {
        !$this->previous_id && $this->previous_id = Post::select_value('SELECT id FROM posts WHERE id < ? ORDER BY id DESC LIMIT 1', $this->id);
        return $this->previous_id;
    }
    
    public function author()
    {
        return $this->user ? $this->user->name : null;
    }
    
    public function can_be_seen_by($user = null, array $options = array())
    {
        if (empty($options['show_deleted']) && $this->status == 'deleted')
            return;
        
        return CONFIG()->can_see_post($user, $this);
    }
    
    public function normalized_source()
    {
        if (preg_match('/pixiv\.net\/img/', $this->source)) {
            if (preg_match('/(\d+)(_s|_m|(_big)?_p\d+)?\.\w+(\?\d+)?\z/', $this->source, $m))
                $img_id = $m[1];
            else
                $img_id = null;
            return "http://www.pixiv.net/member_illust.php?mode=medium&illust_id=" . $img_id;
        } elseif (strpos($this->source, 'http://') === 0 || strpos($this->source, 'https://') === 0)
            return $this->source;
        else
            return 'http://' . $this->source;
    }
    
    public function clear_avatars()
    {
        User::clear_avatars($this->id);
    }
    
    public function approve($approver_id)
    {
        $old_status = $this->status;

        if ($this->flag_detail)
            $this->flag_detail->update_attribute('is_resolved', true);
        
        $this->update_attributes(array('status' => "active", 'approver_id' => $approver_id));

        # Don't bump posts if the status wasn't "pending"; it might be "flagged".
        if ($old_status == "pending" and CONFIG()->hide_pending_posts) {
            // $this->touch_index_timestamp();
            $this->save();
        }
    }
    
    public function voted_by($score = null)
    {
        # Cache results
        if (!$this->voted_by) {
            foreach (range(1, 3) as $v) {
                $this->voted_by[$v] = User::find_all(array('joins' => "JOIN post_votes v ON v.user_id = users.id", 'select' => "users.name, users.id", 'conditions' => array("v.post_id = ? and v.score = ?", $this->id, $v), 'order' => "v.updated_at DESC"))->attributes(['id', 'name']) ?: array();
            }
        }
        
        if (func_num_args())
            return $this->voted_by[$score];
        return $this->voted_by;
    }

    public function can_user_delete(User $user = null)
    {
        if (!$user)
            $user = current_user();
        
        if (!$user->has_permission($this))
            return false;
        elseif (!$user->is_mod_or_higher() && !$this->is_held() && (strtotime(gmd()) - strtotime($this->created_at)) > 60*60*24)
            return false;
        
        return true;
    }
    
    public function favorited_by()
    {
        return $this->voted_by(3);
    }
    
    public function active_notes()
    {
        return $this->notes ? $this->notes->select(function($x){return $x->is_active;}) : array();
    }
    
    public function set_flag_detail($reason, $creator_id)
    {
        if ($this->flag_detail) {
            $this->flag_detail->update_attributes(array('reason' => $reason, 'user_id' => $creator_id, 'created_at' => gmd()));
        } else {
            FlaggedPostDetail::create(array('post_id' => $this->id, 'reason' => $reason, 'user_id' => $creator_id, 'is_resolved' => false));
        }
    }
    
    public function flag($reason, $creator_id)
    {
        $this->update_attribute('status', "flagged");
        $this->set_flag_detail($reason, $creator_id);
    }
    
    public function destroy_with_reason($reason, $current_user)
    {
        // Post.transaction do
            if ($this->flag_detail)
                $this->flag_detail->update_attribute('is_resolved', true);
            $this->flag($reason, $current_user->id);
            $this->first_delete();
        // end
    }

    static public function static_destroy_with_reason($id, $reason, $current_user)
    {
        $post = Post::find($id);
        return $post->destroy_with_reason($reason, $current_user);
    }

    public function first_delete()
    {
        $this->update_attributes(array('status' => "deleted"));
        $this->_run_callbacks('after_delete');
    }

    public function delete_from_database()
    {
        $this->delete_file();
        self::execute_sql('UPDATE pools SET post_count = post_count - 1 WHERE id IN (SELECT pool_id FROM pools_posts WHERE post_id = '.$this->id.')');
        self::execute_sql('UPDATE tags SET post_count = post_count - 1 WHERE id IN (SELECT tag_id FROM posts_tags WHERE post_id = '.$this->id.')');
        self::execute_sql("DELETE FROM posts WHERE id = ?", $this->id);
    }
    
    public function undelete()
    {
        if ($this->status == "active")
            return;
        $this->update_attributes(array('status' => "active"));
        $this->_run_callbacks('after_undelete');
    }
    
    protected function _init()
    {
        $paramsrefix = !CONFIG()->download_filename_prefix ? null : CONFIG()->download_filename_prefix.' ';
        $abmd5 = substr($this->md5, 0, 2);
        
        if ($this->id) {
            $row = self::select_row("SELECT u.name AS author, GROUP_CONCAT(CONCAT(t.name,':',t.tag_type) SEPARATOR ' ') AS cached_tags
                FROM posts p
                JOIN posts_tags pt ON p.id = pt.post_id
                JOIN tags t ON pt.tag_id = t.id
                JOIN users u ON p.user_id = u.id
                WHERE pt.post_id = " . $this->id);
            
            $this->cached_tags = $row['cached_tags'];
            $this->author = $row['author'];
        }
        $this->_parse_cached_tags();
        
        $this->tags = $this->tag_names();
        
        $this->parent_id = $this->parent_id ? (int)$this->parent_id : null;
        
        $this->file_url = $this->file_url();
        $this->jpeg_url = $this->jpeg_url();
        
        $this->sample_url = $this->sample_url();
        $this->preview_url = $this->preview_url();
        
        if (!$this->source)
            $this->source = '';
        
        $bools = array('is_held', 'has_children', 'is_shown_in_index');
        foreach ($bools as $bool)
            $this->attr_exists($bool) && $this->$bool = (bool)$this->$bool;
        
        foreach($this->attributes() as $method => $params) {
            if (is_numeric($params))
                $this->$method = (int)$params;
        }
        
        # For /post/browse
        !$this->sample_width  && $this->sample_width  = $this->width;
        !$this->sample_height && $this->sample_height = $this->height;
        !$this->jpeg_width    && $this->jpeg_width    = $this->width;
        !$this->jpeg_height   && $this->jpeg_height   = $this->height;
    }
    
    protected function _callbacks()
    {
        return array(
            'before_save'   => array('commit_tags', '_filter_parent_id'),
            'before_create' => array('_set_index_timestamp'),
            'after_create'  => array('_after_creation'),
            'after_delete'  => array('clear_avatars', 'give_favorites_to_parent'),
            'after_save'    => array('update_parent'),
            'after_validation_on_create'  => array('_before_creation'),
            'before_validation_on_create' => array(
                'download_source', '_ensure_tempfile_exists', 'determine_content_type',
                '_validate_content_type', 'generate_hash', 'set_image_dimensions',
                'set_image_status', 'check_pending_count', 'generate_sample',
                'generate_jpeg', 'generate_preview', 'move_file')
        );
    }
    
    protected function _associations()
    {
        return array(
            'has_one'    => array('flag_detail' => array('class_name' => "FlaggedPostDetail")),
            'belongs_to' => array(
                'user',
                'approver' => array('class_name' => 'User')
            ),
            'has_many' => array(
                'notes'    => array('order' => 'id DESC', 'conditions' => array('is_active = 1')),
                'comments' => array('order' => "id"),
                'children' => array('class_name' => 'Post', 'order' => 'id', 'foreign_key' => 'parent_id', 'conditions' => array("status != 'deleted'"))
            )
        );
    }
    
    # TODO: this function could be somewhere else.
    # Tries to verify that the requests are for /post/browse AND if CONFIG()->fake_sample_url is enabled.
    protected function _fake_samples_for_browse()
    {
        $req = Rails::application()->dispatcher()->request();
        if (!CONFIG()->fake_sample_url)
            return false;
        elseif (CONFIG()->fake_sample_url && $req->get() && $req->controller() == 'post' && $req->action() == 'index' && $req->format() == 'json')
            return true;
    }
    
    protected function _before_creation()
    {
        $this->upload = !empty($_FILES['post']['tmp_name']['file']) ? true : false;
        
        if (CONFIG()->tags_from_filename)
            $this->_get_tags_from_filename();
        
        if (CONFIG()->source_from_filename)
            $this->_get_source_from_filename();
        
        if (!$this->rating)
            $this->rating = CONFIG()->default_rating_upload;
        
        $this->rating = strtolower(substr($this->rating, 0, 1));
        
        if (!empty($this->tags)) {
            if ($this->gif() && CONFIG()->add_gif_tag_to_gif)
                $this->tags .= ' gif';
            if ($this->flash() && CONFIG()->add_flash_tag_to_swf)
                $this->tags .= ' flash';
            $this->_creation_tags = $this->tags;
        }
        
        $this->cached_tags = 'tagme:0';
        $this->parsed_cached_tags = $this->_parse_cached_tags();
        
        !$this->parent_id && $this->parent_id = null;
        !$this->source && $this->source = null;
        
        $this->random = mt_rand();
    }
    
    protected function _after_creation()
    {
        $tagme = Tag::find_or_create_by_name('tagme');
        self::execute_sql('INSERT INTO posts_tags VALUES (?, ?)', $this->id, $tagme->id);
        
        if (!empty($this->_creation_tags)) {
            $this->old_tags = 'tagme';
            $this->tags = $this->_creation_tags;
        }
        
        $this->save();
    }
    
    protected function _set_index_timestamp()
    {
        $this->index_timestamp = gmd();
    }
    
    # Added to avoid SQL constraint errors if parent_id passed isn't a valid post.
    protected function _filter_parent_id()
    {
        if (($parent_id = trim($this->parent_id)) && Post::find_by_id($parent_id))
            $this->parent_id = $parent_id;
        else
            $this->parent_id = null;
    }
}