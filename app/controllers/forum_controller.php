<?php
class ForumController extends ApplicationController
{
    protected function _filters()
    {
        return [
            'before' => [
                ['sanitize_id', 'only' => ['show']],
                ['mod_only', 'only' => ['stick', 'unstick', 'lock', 'unlock']],
                ['member_only', 'only' => ['destroy', 'update', 'edit', 'add', 'mark_all_read', 'preview']],
                ['post_member_only', 'only' => ['create']]
            ]
        ];
    }

    public function stick()
    {
        ForumPost::stick($this->params()->id);
        $this->_notice("Topic stickied");
        $this->_redirect_to(['action' => "show", 'id' => $this->params()->id]);
    }

    public function unstick()
    {
        ForumPost::unstick($this->params()->id);
        $this->_notice("Topic unstickied");
        $this->_redirect_to(['action' => "show", 'id' => $this->params()->id]);
    }

    public function preview()
    {
        ActionView::add_helper('avatar');
        
        if ($this->params()->forum_post) {
            $this->preview = true;
            $forum_post = new ForumPost(array_merge($this->params()->forum_post, ['creator_id' => $this->current_user->id]));
            $forum_post->created_at = gmd();
            $this->_render(['partial' => "post", 'locals' => ['post' => $forum_post]]);
        } else {
            $this->_render(['text' => ""]);
        }
    }
    
    # Changed method name from "new" to "blank".
    public function blank()
    {
        $this->forum_post = new ForumPost();

        if ($this->params()->type == "alias") {
            $this->forum_post->title = "Tag Alias: ";
            $this->forum_post->body = "Aliasing ___ to ___.\n\nReason: ";
        } elseif ($this->params()->type == "impl") {
            $this->forum_post->title = "Tag Implication: ";
            $this->forum_post->body = "Implicating ___ to ___.\n\nReason: ";
        }
    }

    public function create()
    {
        $params = $this->params()->forum_post;
        if (empty($params['parent_id']) || !ctype_digit($params['parent_id']))
            $params['parent_id'] = null;
        
        $this->forum_post = ForumPost::create(array_merge($params, ['creator_id' => $this->current_user->id]));

        if ($this->forum_post->errors()->blank()) {
            if (!$this->params()->forum_post['parent_id']) {
                $this->_notice("Forum topic created");
                $this->_redirect_to(['action' => "show", 'id' => $this->forum_post->root_id()]);
            } else {
                $this->_notice("Response posted");
                $this->_redirect_to(["#show", 'id' => $this->forum_post->root_id(), 'page' => ceil($this->forum_post->root()->response_count / 30.0)]);
            }
        } else {
            $this->_render_error($this->forum_post);
        }
    }

    public function add()
    {
    }

    public function destroy()
    {
        $this->forum_post = ForumPost::find($this->params()->id);

        if ($this->current_user->has_permission($this->forum_post, 'creator_id')) {
            $this->forum_post->destroy();
            $this->_notice("Post destroyed");

            if ($this->forum_post->is_parent()) {
                $this->_redirect_to("#index");
            } else {
                $this->_redirect_to(["#show", 'id' => $this->forum_post->root_id()]);
            }
        } else {
            $this->_notice("Access denied");
            $this->_redirect_to(["#show", 'id' => $this->forum_post->root_id()]);
        }
    }

    public function edit()
    {
        $this->forum_post = ForumPost::find($this->params()->id);

        if (!$this->current_user->has_permission($this->forum_post, 'creator_id'))
            $this->_access_denied();
    }

    public function update()
    {
        $this->forum_post = ForumPost::find($this->params()->id);

        if (!$this->current_user->has_permission($this->forum_post, 'creator_id')) {
            $this->_access_denied();
            return;
        }

        $this->forum_post->add_attributes($this->params()->forum_post);
        if ($this->forum_post->save()) {
            $this->_notice("Post updated");
            $this->_redirect_to(["#show", 'id' => $this->forum_post->root_id(), 'page' => ceil($this->forum_post->root()->response_count / 30.0)]);
        } else {
            $this->_render_error($this->forum_post);
        }
    }

    public function show()
    {
        ActionView::add_helper('avatar');
        $this->forum_post = ForumPost::find($this->params()->id);
        $this->_set_title($this->forum_post->title);
        $this->children = ForumPost::paginate(['order' => "id", 'per_page' => 30, 'conditions' => ["parent_id = ?", $this->params()->id], 'page' => $this->page_number()]);

        if (!$this->current_user->is_anonymous() && $this->current_user->last_forum_topic_read_at < $this->forum_post->updated_at && $this->forum_post->updated_at < (time() - 3)) {
            $this->current_user->update_attribute('last_forum_topic_read_at', $this->forum_post->updated_at);
        }

        $this->_respond_to_list("forum_post");
    }

    public function index()
    {
        $this->_set_title("Forum");

        if ($this->params()->parent_id) {
            $this->forum_posts = ForumPost::paginate(['order' => "is_sticky desc, updated_at DESC", 'per_page' => 100, 'conditions' => ["parent_id = ?", $this->params()->parent_id], 'page' => $this->page_number()]);
        } else {
            $this->forum_posts = ForumPost::paginate(['order' => "is_sticky desc, updated_at DESC", 'per_page' => 30, 'conditions' => "parent_id IS NULL", 'page' => $this->page_number()]);
        }

        $this->_respond_to_list("forum_posts");
    }

    public function search()
    {
        if ($this->params()->query) {
            $query = '%' . str_replace(' ', '%', $this->params()->query) . '%';
            $this->forum_posts = ForumPost::paginate(['order' => "id desc", 'per_page' => 30, 'conditions' => array('title LIKE ? OR body LIKE ?', $query, $query), 'page' => $this->params()->page]);
        } else {
            $this->forum_posts = ForumPost::paginate(['order' => "id desc", 'per_page' => 30, 'page' => $this->params()->page]);
        }

        $this->_respond_to_list("forum_posts");
    }

    public function lock()
    {
        ForumPost::lock($this->params()->id);
        $this->_notice("Topic locked");
        $this->_redirect_to(["#show", 'id' => $this->params()->id]);
    }

    public function unlock()
    {
        ForumPost::unlock($this->params()->id);
        $this->_notice("Topic unlocked");
        $this->_redirect_to(["#show", 'id' => $this->params()->id]);
    }

    public function mark_all_read()
    {
        $this->current_user->update_attribute('last_forum_topic_read_at', time());
        $this->_render(['nothing' => true]);
    }
}
