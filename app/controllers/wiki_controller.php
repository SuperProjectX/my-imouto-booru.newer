<?php
# encoding: utf-8
ActiveRecord::load_model('WikiPage');

class WikiController extends ApplicationController
{
    protected function _filters()
    {
        return [
            'before' => [
                ['post_member_only', 'only' => ['update', 'create', 'edit', 'revert']],
                ['mod_only', 'only' => ['lock', 'unlock', 'destroy', 'rename']]
            ]
        ];
    }

    public function destroy()
    {
        $page = WikiPage::find_page($this->params()->title);
        $page->destroy();
        $this->_respond_to_success("Page deleted", ['action' => "show", 'title' => $this->params()->title]);
    }

    public function lock()
    {
        $page = WikiPage::find_page($this->params()->title);
        $page->lock();
        $this->_respond_to_success("Page locked", ['action' => "show", 'title' => $this->params()->title]);
    }

    public function unlock()
    {
        $page = WikiPage::find_page($this->params()->title);
        $page->unlock();
        $this->_respond_to_success("Page unlocked", ['action' => "show", 'title' => $this->params()->title]);
    }

    public function index()
    {
        $this->_set_title('Wiki');
        
        $this->params = $this->params();
        if ($this->params()->order == "date") {
            $order = "updated_at DESC";
        } else {
            $order = "lower(title)";
        }

        $limit = $this->params()->limit ?: 25;
        $query = $this->params()->query ?: "";

        $search_params = [
            'order'    => $order,
            'per_page' => $limit,
            'page'     => $this->page_number()
        ];

        if ($query) {
            if (preg_match('/^title:/', $query)) {
                $search_params['conditions'] = ["title ilike ?", "%" . substr($query, 6, 1) . "%"];
            } else {
                $query = str_replace(' ', '%', $query);
                $search_params['conditions'] = ["body LIKE ?", '%' . $query . '%'];
            }
        }

        $this->wiki_pages = WikiPage::paginate($search_params);

        $this->_respond_to_list("wiki_pages");
    }

    public function preview()
    {
        $this->_render(['inline' => '<?= $this->format_text($this->params()->body) ?>']);
    }

    // public function add()
    // {
        // $this->wiki_page = WikiPage::new
        // $this->wiki_page.title = $this->params()->title || "Title"
    // }

    // public function create()
    // {
        // $page = WikiPage::create($this->params()->wiki_page.merge('ip_addr' => request.remote_ip, 'user_id' => session['user_id']));

        // if (page->errors()->empty?) {
            // $this->_respond_to_success("Page created", ['action' => "show", 'title' => page.title}, 'location' => url_for('action' => "show", 'title' => page.title));
        // } else {
            // $this->_respond_to_error(page, 'action' => "index");
        // }
    // }

    public function edit()
    {
        if (!$this->params()->title) {
            $this->render(['text' => "no title specified"]);
        } else {
            $this->wiki_page = WikiPage::find_page($this->params()->title, $this->params()->version);

            if (!$this->wiki_page) {
                $this->_redirect_to(["#add", 'title' => $this->params()->title]);
            }
        }
    }

    public function update()
    {
        $this->page = WikiPage::find_page(($this->params()->title ?: $this->params()->wiki_page['title']));

        if ($this->page->is_locked) {
            $this->_respond_to_error("Page is locked", ['action' => "show", 'title' => $this->page->title], ['status' => 422]);
        } else {
            if ($this->page->update_attributes(array_merge($this->params()->wiki_page, ['ip_addr' => $this->request()->remote_ip(), 'user_id' => $this->current_user->id]))) {
                $this->_respond_to_success("Page updated", ['action' => "show", 'title' => $this->page->title]);
            } else {
                $this->_respond_to_error($this->page, ['action' => "show", 'title' => $this->page->title]);
            }
        }
    }

    public function show()
    {
        if (!$this->params()->title) {
            $this->_render(['text' => "no title specified"]);
            return;
        }
        ActiveRecord::load_model('Artist');

        $this->title  = $this->params()->title;
        $this->page   = WikiPage::find_page($this->params()->title, $this->params()->version);
        $this->posts  = Post::find_by_tag_join($this->params()->title, ['limit' => 8])->select(function($x){return $x->can_be_seen_by(current_user());});
        $this->artist = Artist::find_by_name($this->params()->title);
        $this->tag    = Tag::find_by_name($this->params()->title);
        $this->_set_title(str_replace("_", " ", $this->params()->title));
    }

    // public function revert()
    // {
        // $this->page = WikiPage::find_page($this->params()->title)

        // if ($this->page.is_locked?) {
            // $this->_respond_to_error("Page is locked", ['action' => "show", 'title' => $this->params()->title}, 'status' => 422);
        // } else {
            // $this->page.revert_to($this->params()->version)
            // $this->page.ip_addr = request.remote_ip
            // $this->page.user_id = $this->current_user.id

            // if ($this->page.save) {
                // $this->_respond_to_success("Page reverted", 'action' => "show", 'title' => $this->params()->title);
           // ] else {
                // $this->_respond_to_error(($this->page->errors()->full_messages.first rescue "Error reverting page"), ['action' => 'show', 'title' => $this->params()->title });
            // }
        // }
   // ]

    // public function recent_changes()
    // {
        // $this->_set_title('Recent Changes');
        
        // if ($this->params()->user_id) {
            // $this->params()->user_id = $this->params()->user_id.to_i
            // $this->wiki_pages = WikiPage::paginate 'order' => "updated_at DESC", 'per_page' => ($this->params()->per_page || 25), 'page' => page_number, 'conditions' => ["user_id = ?", $this->params()->user_id]
        // } else {
            // $this->wiki_pages = WikiPage::paginate 'order' => "updated_at DESC", 'per_page' => ($this->params()->per_page || 25), 'page' => page_number
        // }
        // $this->_respond_to_list("wiki_pages");
    // }

    public function history()
    {
        $this->_set_title('Wiki History');
        
        if ($this->params()->title) {
            $wiki = WikiPage::find_by_title($this->params()->title);
            if ($wiki)
                $wiki_id = $wiki->id;
        } elseif ($this->params()->id) {
                $wiki_id = $this->params()->id;
        } else
            $wiki_id = null;
        $this->wiki_pages = WikiPageVersion::find_all(['conditions' => ['wiki_page_id' => $wiki_id ], 'order' => 'version DESC']);

        $this->_respond_to_list("wiki_pages");
    }

    public function diff()
    {
        $this->_set_title('Wiki Diff');
        
        if ($this->params()->redirect) {
            $this->_redirect_to(['action' => "diff", 'title' => $this->params()->title, 'from' => $this->params()->from, 'to' => $this->params()->to]);
            return;        }

        if (!$this->params()->title || !$this->params()->to || !$this->params()->from) {
            $this->_notice("No title was specificed");
            $this->_redirect_to("#index");
            return;
        }

        $this->oldpage = WikiPage::find_page($this->params()->title, $this->params()->from);
        $this->difference = $this->oldpage->diff($this->params()->to);
    }

    // public function rename()
    // {
        // $this->wiki_page = WikiPage::find_page($this->params()->title)
    // }
}
