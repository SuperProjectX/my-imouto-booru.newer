<?php
class TagController extends ApplicationController
{
    protected function _filters()
    {
        return array(
            'before' => [
                ['mod_only', 'only' => array('mass_edit', 'edit_preview')],
                ['member_only', 'only' => array('update', 'edit')]
            ]
        );
    }

    // public function cloud()
    // {
        // $this->_set_title('Tags');
        
        // $this->tags = Tag.find(:all, 'conditions' => "post_count > 0", 'order' => "post_count DESC", 'limit' => 100).sort {|a, b| a.name <=> b.name}
    // }

    // # Generates list of tag names matching parameter term.
    // # Used by jquery.ui.autocomplete.
    // public function autocomplete_name()
    // {
        // $this->tags = Tag.where(['name ILIKE ?', "*#{$this->params()->term}*".to_escaped_for_sql_like]).pluck(:name)
        // $this->respond_to(array(
            // format.json { $this->render(array('json' => $this->tags })
        // ));
    // }

    public function summary()
    {
         if ($this->params()->version) {
            # HTTP caching is unreliable for XHR.    If a version is supplied, and the version
            # hasn't changed since then, return; an empty response.
            $version = Tag::get_summary_version();
             if ((int)$this->params()->version == $version) {
                $this->_render(array('json' => array('version' => $version, 'unchanged' => true)));
                return;
            }
        }

        # This string is already JSON-encoded, so don't call to_json.
        $this->_render(array('json' => Tag::get_json_summary()));
    }

    public function index()
    {
        $this->_set_title('Tags');
        
        # TODO: convert to nagato
        if ($this->params()->limit === "0")
            $limit = null;
        elseif (!$this->params()->limit)
            $limit = 50;
        else
            $limit = (int)$this->params()->limit;

        switch ($this->params()->order) {
            case "name":
                $order = "name";
                break;
            case "count":
                $order = "post_count desc";
                break;
            case "date":
                $order = "id desc";
                break;
            default:
                $order = "name";
                break;
        }

        $conds = array("true");
        $cond_params = array();

        if ($this->params()->name) {
            $conds[] = "name LIKE ?";
            if (is_int(strpos($this->params()->name, '*')))
                $cond_params[] = str_replace('*', '%', $this->params()->name);
            else
                $cond_params[] = '%' . str_replace('*', '%', $this->params()->name) . '%';
        }

        if (ctype_digit($this->params()->type)) {
            $this->params()->type = (int)$this->params()->type;
            $conds[] = 'tag_type = ?';
            $cond_params[] = $this->params()->type;
        }

        if (!empty($this->params()->after_id)) {
            $conds[] = 'id >= ?';
            $cond_params[] = $this->params()->after_id;
        }

        if (!empty($this->params()->id)) {
            $conds[] = 'id = ?';
            $cond_params[] = $this->params()->id;
        }

        $this->_respond_to(array(
            'html' => function () use ($order, $conds, $cond_params) {
                $this->can_delete_tags = CONFIG()->enable_tag_deletion && current_user()->is_mod_or_higher();
                $this->tags = Tag::paginate(array('order' => $order, 'per_page' => 50, 'conditions' => array_merge(array(implode(' AND ', $conds)), $cond_params), 'page' => $this->page_number()));
            },
            'xml' => function () use ($order, $limit, $conds, $cond_params) {
                if (!$this->params()->order)
                    $order = null;
                $conds = implode(" AND ", $conds);
                if ($conds == "true" && CONFIG()->web_server == "nginx" && file_exists(RAILS_ROOT."/public/tags.xml")) {
                    # Special case: instead of rebuilding a list of every tag every time, cache it locally and tell the web
                    # server to stream it directly. This only works on Nginx.
                    $this->response()->headers("X-Accel-Redirect", RAILS_ROOT . "/public/tags.xml");
                    $this->_render(array('nothing' => true));
                } else {
                    $this->_render(array('xml' => Tag::find_all(array('order' => $order, 'limit' => $limit, 'conditions' => array_merge(array($conds), $cond_params))), array('root' => "tags")));
                }
            },
            'json' => function ($s) use ($order, $limit, $conds, $cond_params) {
                $tags = Tag::find_all(array('order' => $order, 'limit' => $limit, 'conditions' => array_merge(array(implode(' AND ', $conds)), $cond_params)));
                $this->_render(array('json' => $tags));
            }
        ));
    }

    public function mass_edit()
    {
        $this->_set_title('Mass Edit Tags');
        
        if ($this->request()->post()) {
            if (!$this->params()->start) {
                $this->_respond_to_error("Start tag missing", ['#mass_edit'], ['status' => 424]);
                return;
            }

            if (CONFIG()->enable_asynchronous_tasks) {
                $task = JobTask::create(['task_type' => "mass_tag_edit", 'status' => "pending", 'data' => ["start_tags" => $this->params()->start, "result_tags" => $this->params()->result, "updater_id" => $this->current_user->id, "updater_ip_addr" => $this->request()->remote_ip()]]);
                $this->_respond_to_success("Mass tag edit job created", 'job_task#index');
            } else {
                Tag::mass_edit($this->params()->start, $this->params()->result, current_user()->id, $this->request()->remote_ip());
            }
        }
    }

    public function edit_preview()
    {
        list($sql, $params) = Post::generate_sql($this->params()->tags, ['order' => "p.id DESC", 'limit' => 500]);
        $this->posts = Post::find_by_sql($sql, $params);
        $this->_layout(false);
    }

    public function edit()
    {
        if ($this->params()->id) {
            $this->tag = Tag::find($this->params()->id) ?: new Tag();
        } else {
            $this->tag = Tag::find_by_name($this->params()->name) ?: new Tag();
        }
    }

    public function update()
    {
        $tag = Tag::find_by_name($this->params()->tag['name']);
        if ($tag)
            $tag->update_attributes($this->params()->tag);
        $this->_respond_to_success("Tag updated", '#index');
    }

    // public function related()
    // {
         // if ($this->params()->type) {
            // $this->tags = Tag.scan_tags($this->params()->tags)
            // $this->tags = TagAlias.to_aliased($this->tags)
            // $this->tags = $this->tags.inject(array()) do |all, x|
                // all[x] = Tag.calculate_related_by_type(x, CONFIG()->tag_types[$this->params()->type]).map {|y| [y["name"], y["post_count"]]}
                // all
            // end
        // } else {
            // $this->tags = Tag.scan_tags($this->params()->tags)
            // $this->patterns, $this->tags = $this->tags.partition {|x| x.include?("*")}
            // $this->tags = TagAlias.to_aliased($this->tags)
            // $this->tags = $this->tags.inject(array()) do |all, x|
                // all[x] = Tag.find_related(x).map {|y| [y[0], y[1]]}
                // all
            // end
            // $this->patterns.each do |x|
                // $this->tags[x] = Tag.find(:all, 'conditions' => ["name LIKE ? ESCAPE E'\\\\'", x.to_escaped_for_sql_like]).map {|y| [y.name, y.post_count]}
            // end
        // }

        // $this->respond_to(array(
            // fmt.xml do
                // # We basically have to do this by hand.
                // builder = Builder::XmlMarkup.new('indent' => 2)
                // builder.instruct!
                // xml = builder.tag!("tags") do
                    // $this->tags.each do |parent, related|
                        // builder.tag!("tag", 'name' => parent) do
                            // related.each do |tag, count|
                                // builder.tag!("tag", 'name' => tag, 'count' => count)
                            // end
                        // end
                    // end
                // end

                // $this->render(array('xml' => xml)
            // end
            // fmt.json {$this->render(array('json' => $this->json_encode(tags)})
        // ));
    // }

    public function popular_by_day()
    {
        if (!$this->params()->year || !$this->params()->month || !$this->params()->day ||
            !($this->day = @strtotime($this->params()->year . '-' . $this->params()->month . '-' . $this->params()->day))) {
            $this->day = strtotime('this day');
        }

        $this->tags = Tag::count_by_period(date('Y-m-d', $this->day), date('Y-m-d', strtotime('+1 day', $this->day)));
    }

    public function popular_by_week()
    {
        if (!$this->params()->year || !$this->params()->month || !$this->params()->day ||
            !($this->start = strtotime('this week', @strtotime($this->params()->year . '-' . $this->params()->month . '-' . $this->params()->day)))) {
            $this->start = strtotime('this week');
        }

        $this->end = strtotime('next week', $this->start);
        
        $this->tags = Tag::count_by_period(date('Y-m-d', $this->start), date('Y-m-d', $this->end));
    }

    public function popular_by_month()
    {
        if (!$this->params()->year || !$this->params()->month || !($this->start = @strtotime($this->params()->year . '-' . $this->params()->month . '-01'))) {
            $this->start = strtotime('first day of this month');
        }

        $this->end = strtotime('+1 month', $this->start);

        $this->tags = Tag::count_by_period(date('Y-m-d', $this->start), date('Y-m-d', $this->end));
    }

    // public function show()
    // {
        // begin
            // name = Tag.find($this->params()->id, 'select' => :name).name
        // rescue
            // raise ActionController::RoutingError.new('Not Found')        }
        // redirect_to 'controller' => :wiki, 'action' => :show, 'title' => name
    // }
    
    public function delete()
    {
        if (!CONFIG()->enable_tag_deletion) {
            $this->_respond_to_error('Access denied', '#index');
            return;
        }
        
        $tag = Tag::find($this->params()->id);
        
        if ($tag)
            $tag->delete();

        $opts = $this->params()->get();
        unset($opts['id']);

        array_unshift($opts, '#index');
        $this->_respond_to_success('Tag deleted', $opts);
    }
    
    public function fix_count()
    {
        if (!current_user()->is_mod_or_higher() || !CONFIG()->enable_tag_fix_count) {
            $this->_respond_to_error('Access denied', '#index');
        } else {
            Tag::recalculate_post_count();
            $from = $this->params()->from ? urldecode($this->params()->from) : '#index';
            $this->_respond_to_success('Count fixed', $from);
        }
    }
}