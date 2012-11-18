<?php
# encoding: utf-8
ActionView::add_helper('wiki');

class ArtistController extends ApplicationController
{
    protected function _filters()
    {
        return [
            'before' => [
                ['post_member_only', 'only' => ['create', 'update']],
                ['post_privileged_only', 'only' => ['destroy']]
            ]
        ];
    }
    
    public function preview()
    {
        $this->_render(['inline' => "<h4>Preview</h4><?= \$this->format_text(\$this->params()->artist['notes']) ?>"]);
    }

    public function destroy()
    {
        $this->artist = Artist::find($this->params()->id);

         if ($this->request()->post()) {
             if ($this->params()->commit == "Yes") {
                $this->artist->destroy();
                $this->_respond_to_success("Artist deleted", ['#index', 'page' => $this->page_number()]);
            } else {
                $this->_redirect_to(['#index', 'page' => $this->page_number()]);
            }
        }
    }

    public function update()
    {
        if ($this->request()->post()) {
             if ($this->params()->commit == "Cancel") {
                $this->redirect_to(['#show', 'id' => $this->params()->id]);
                return;
            }

            $artist = Artist::find($this->params()->id);
            $artist->update_attributes(array_merge($this->params()->artist, ['updater_ip_addr' => $this->request()->remote_ip(), 'updater_id' => current_user()->id]));

            if ($artist->errors()->blank()) {
                $this->_respond_to_success("Artist updated", ['#show', 'id' => $artist->id]);
            } else {
                $this->_respond_to_error($artist, ['#update', 'id' => $artist->id]);
            }
        } else {
            $this->artist = Artist::find($this->params()->id);
        }
    }

    public function create()
    {
         if ($this->request()->post()) {
            $artist = Artist::create(array_merge($this->params()->artist, ['updater_ip_addr' => $this->request()->remote_ip(), 'updater_id' => current_user()->id]));

             if ($artist->errors()->blank()) {
                $this->_respond_to_success("Artist created", ['#show', 'id' => $artist->id]);
            } else {
                $this->_respond_to_error($artist, ['#create', 'alias_id' => $this->params()->alias_id]);
            }
        } else {
            $this->artist = new Artist();

            if ($this->params()->name) {
                $this->artist->name = $this->params()->name;

                $post = $post = Post::find_first(['conditions' => ["tags.name = ? AND source LIKE 'http%'", $this->params()->name], 'joins' => 'JOIN posts_tags pt ON posts.id = pt.post_id JOIN tags ON pt.tag_id = tags.id', 'select' => 'posts.*']);
                if ($post && $post->source)
                    $this->artist->urls = $post->source;
            }

            if ($this->params()->alias_id) {
                $this->artist->alias_id = $this->params()->alias_id;
            }
        }
    }

    public function index()
    {
        $this->_set_title('Artists');
        
        if ($this->params()->order == "date")
            $order = "updated_at DESC";
        else
            $order = "name";

        if ($this->params()->name)
            $find_params = array_merge(Artist::generate_sql($this->params()->name), ['per_page' => 50, 'page' => $this->params()->page, 'order' => $order]);
        elseif ($this->params()->url)
            $find_params = array_merge(Artist::generate_sql($this->params()->url), ['per_page' => 50, 'page' => $this->params()->page, 'order' => $order]);
        else
            $find_params = ['order' => $order, 'per_page' => 25, 'page' => $this->page_number()];
        
        $this->artists = Artist::paginate($find_params);

        $this->_respond_to_list("artists");
    }

    public function show()
    {
         if ($this->params()->name) {
            $this->artist = Artist::find_by_name($this->params()->name);
        } else {
            $this->artist = Artist::find($this->params()->id);
        }

         if (!$this->artist) {
            $this->_redirect_to(['#create', 'name' => $this->params()->name]);
        } else {
            $this->_redirect_to(['wiki#show', 'title' => $this->artist->name]);
        }
    }
}