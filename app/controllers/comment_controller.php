<?php
ActionView::add_helpers(array('avatar', 'post'));

class CommentController extends ApplicationController
{
    protected function _filters()
    {
        return array(
            'before' => [
                ['member_only', 'only' => array('create', 'destroy', 'update')],
                ['janitor_only', 'only' => array('moderate')]
            ]
        );
    }

    public function edit()
    {
        $this->comment = Comment::find($this->params()->id);
    }

    public function update()
    {
        $comment = Comment::find($this->params()->id);
         if (current_user()->has_permission($comment)) {
            $comment->update_attributes($this->params()->comment);
            $this->_respond_to_success("Comment updated", '#index');
        } else {
            $this->_access_denied();
        }
    }

    public function destroy()
    {
        $comment = Comment::find($this->params()->id);
        if (current_user()->has_permission($comment)) {
            $comment->destroy();
            $this->_respond_to_success("Comment deleted", array('post#show', 'id' => $comment->post_id));
        } else {
            $this->_access_denied();
        }
    }

    public function create()
    {
         if (current_user()->is_member_or_lower() && $this->params()->commit == "Post" && Comment::count(array('conditions' => array("user_id = ? AND created_at > ?", current_user()->id, strtotime('-1 hour')) >= CONFIG()->member_comment_limit))) {
            # TODO: move this to the model
            $this->_respond_to_error("Hourly limit exceeded", '#index', array('status' => 421));
            return;
        }

        $user_id = current_user()->id;

        $comment = new Comment(array_merge($this->params()->comment, array('ip_addr' => $this->request()->remote_ip(), 'user_id' => $user_id)));
        if ($this->params()->commit == "Post without bumping") {
            $comment->do_not_bump_post = true;
        }

        if ($comment->save()) {
            $this->_respond_to_success("Comment created", '#index');
        } else {
            $this->_respond_to_error($comment, '#index');
        }
    }

    public function show()
    {
        $this->_set_title('Comment');
        $this->comment = Comment::find($this->params()->id);
        $this->_respond_to_list("comment");
    }

    public function index()
    {
        $this->_set_title('Comments');
        
        if ($this->params()->format == "json" || $this->params()->format == "xml") {
            $this->comments = Comment::paginate(Comment::generate_sql(array_merge($this->params()->all(), array('per_page' => 25, 'page' => $this->page_number(), 'order' => "id DESC"))));
            $this->respond_to_list("comments");
        } else {
            $this->posts = Post::paginate(array('order' => "last_commented_at DESC", 'conditions' => "last_commented_at IS NOT NULL", 'per_page' => 10, 'page' => $this->page_number()));

            $comments = new ActiveRecord_Collection();
            $this->posts->each(function($post)use($comments){$comments->merge($post->recent_comments());});

            $newest_comment = $comments->max(function($a, $b){return $a->created_at > $b->created_at ? $a : $b;});
            if (!current_user()->is_anonymous() && $newest_comment && current_user()->last_comment_read_at < $newest_comment->created_at) {
                current_user()->update_attribute('last_comment_read_at', $newest_comment->created_at);
            }

            $this->posts->delete_if(function($x){return !$x->can_be_seen_by(current_user(), array('show_deleted' => true));});
        }
    }

    public function search()
    {
        $options = array('order' => "id desc", 'per_page' => 30, 'page' => params()->page);
        $conds = $cond_params = $search_terms = array();
        if ($this->params()->query) {
            $keywords = array();
            foreach (explode(' ', params()->query) as $s) {
                if (!$s) continue;
                
                if (strpos($s, 'user:') === 0 && strlen($s) > 5) {
                    list($search_type, $param) = explode(':', $s);
                    if ($user_id = User::find_id_by_name($param)) {
                        $conds[] = 'user_id = ?';
                        $cond_params[] = $user_id;
                    } else {
                        $conds[] = 'false';
                    }
                    continue;
                }

                $search_terms[] = $s;
            }
            $conds[] = 'body LIKE ?';
            $cond_params[] = '%' . implode('%', $search_terms) . '%';
            $options['conditions'] = array_merge(array(implode(' AND ', $conds)), $cond_params);
        } else
            $options['conditions'] = array("false");

        $comments = Comment::paginate($options);

        $this->_respond_to_list("comments");
    }

    public function moderate()
    {
        $this->_set_title('Moderate Comments');
        if ($this->request()->post()) {
            $ids = array_keys($this->params()->c);
            $coms = Comment::find_all(array('conditions' => array("id IN (?)", $ids)));

            if ($this->params()->commit == "Delete") {
                $coms->each('destroy');
            } elseif ($this->params()->commit == "Approve") {
                $coms->each('update_attribute', array('is_spam', false));
            }

            $this->_redirect_to('#moderate');
        } else {
            $this->comments = Comment::find_all(array('conditions' => "is_spam = TRUE", 'order' => "id DESC"));
        }
    }

    public function mark_as_spam()
    {
        $this->comment = Comment::find($this->params()->id);
        $this->comment->update_attributes(array('is_spam' => true));
        $this->_respond_to_success("Comment marked as spam", '#index');
    }
}