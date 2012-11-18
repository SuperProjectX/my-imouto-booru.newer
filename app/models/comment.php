<?php
// require 'translate'
# FIXME: god, why I need this. Anyway, the required helper functions should be
#        moved to library instead. It's not really "view" helper anymore.
// include ApplicationHelper

class Comment extends ActiveRecord_Base
{
    static public function generate_sql($params)
    {
        $params = (array)$params; // because of comment/index
        if (empty($params['post_id']))
            return array();
        
        return array('conditions' => 'post_id = ?', array($params['post_id']));
    }

    static public function updated(User $user)
    {
        if(!$user->is_anonymous)
            $conds = array("user_id <> ?", $user->id);
        else
            $conds = array();

        if (!$newest_comment = Comment::find_first(array('order' => "id desc", 'limit' => 1, 'select' => "created_at", 'conditions' => $conds)))
            return false;
        
        !$user->last_comment_read_at && $user->last_comment_read_at = '0000-00-00 00:00:00';
        return $newest_comment->created_at > $user->last_comment_read_at;
    }
    
    public function update_last_commented_at()
    {
        # return if self.do_not_bump_post
        
        $comment_count = self::select_value("SELECT COUNT(*) FROM comments WHERE post_id = ?", $this->post_id);
        if ($comment_count <= CONFIG()->comment_threshold) {
            self::execute_sql(["UPDATE posts SET last_commented_at = (SELECT created_at FROM comments WHERE post_id = :post_id ORDER BY created_at DESC LIMIT 1) WHERE posts.id = :post_id", 'post_id' => $this->post_id]);
        }
    }

    public function get_formatted_body()
    {
        return $this->format_inlines($this->format_text($this->body, ['mode' => 'comment']), $this->id);
    }

    public function update_fragments()
    {
        return;
    }

    # Get the comment translated into the requested language.    Languages in source_langs
    # will be left untranslated.
    public function get_translated_formatted_body_uncached($target_lang, $source_langs)
    {
            return $this->get_formatted_body();
            // return $this->get_formatted_body, array();
    }

    public function get_translated_formatted_body($target_lang, array $source_langs)
    {
        $source_lang_list = implode(',', $source_langs);
        $key = "comment:" . $this->id . ":" . strtotime($this->updated_at) . ":" . $target_lang . ":" . $source_lang_list;
        # TODO
        // return Rails::cache()->fetch($key) {
            return $this->get_translated_formatted_body_uncached($target_lang, $source_langs);
        // }
    }

    public function author()
    {
        return $this->user->name;
    }

    public function pretty_author()
    {
        return str_replace("_", " ", $this->author);
    }

    public function author2()
    {
        return $this->user->name;
    }

    public function pretty_author2()
    {
        return str_replace("_", " ", $this->author2());
    }

    public function api_attributes()
    {
        return array(
            'id'                 => $this->id,
            'created_at' => $this->created_at,
            'post_id'        => $this->post_id,
            'creator'        => $this->author(),
            'creator_id' => $this->user_id,
            'body'             => $this->body
        );
    }

    public function to_xml(array $options = array())
    {   // TODO:
        // parent::to_xml($options, $this->api_attributes());
        // return Rails::to_xml($this->api_attributes(), array_merge(array('root' => 'comment'), $options));
    }
    
    public function to_json(array $params = array())
    {
        return json_encode($this->api_attributes());
    }

    public function as_json($args = array())
    {
        return $this->to_json();
    }
    
    protected function _validations()
    {
        return array(
            'body' => array(
                'format' => array('/\S/', 'message' => 'has no content')
            )
        );
    }
    
    protected function _associations()
    {
        return array(
            'belongs_to' => array(
                'post',
                'user'
            )
        );
    }
    
    protected function _callbacks()
    {
        return array(
            'after_save' => array('update_last_commented_at', 'update_fragments'),
            'after_destroy' => array('update_last_commented_at')
        );
    }
}