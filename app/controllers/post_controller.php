<?php
class PostController extends ApplicationController
{
    public function activate()
    {
        $ids = is_array($this->params()->post_ids) ? array_map(function($id){return (int)$id;}) : array();
        $changed = Post::batch_activate(current_user()->is_mod_or_higher() ? null: current_user()->id, $ids);
        $this->_respond_to_success("Posts activated", '#moderate', ['api' => ['count' => $changed]]);
    }

    public function upload_problem()
    {
    }

    public function upload()
    {
        $this->_set_title('Upload');
        
        $this->deleted_posts = FlaggedPostDetail::new_deleted_posts(current_user());
#        if $this->params()->url
#            $this->post = Post.find(:first, 'conditions' => ["source = ?", $this->params()->url])
#        end
        $this->default_rating = CONFIG()->default_rating_upload ?: "q";
        if (empty($this->post)) {
            $this->post = new Post();
        }
    }

    public function create()
    {
        if (current_user()->is_member_or_lower() && Post::count(array('conditions' => array("user_id = ? AND created_at > ? ", current_user()->id, strtotime('-1 day')))) >= CONFIG()->member_post_limit) {
            $this->_respond_to_error("Daily limit exceeded", '#error', array('status' => 421));
            return;
        }
        if (current_user()->is_privileged_or_higher()) {
            $status = "active";
        } else {
            $status = "pending";
        }

        if ($this->params()->anonymous == '1' and current_user()->is_contributor_or_higher()) {
            $user_id = null;
            # FIXME: someone track down the user of Thread evilry here and nuke
            #                it please?
            $this->session('danbooru-user', null);
            $this->session('danbooru-user_id', null);
            $this->session('danbooru-ip_addr', $this->request()->remote_ip());
        } else {
            $user_id = current_user()->id;
        }
        
        # iTODO
        $post_params = array_merge($this->params()->post ?: array(), array(
            'updater_user_id' => current_user()->id,
            'updater_ip_addr' => $this->request()->remote_ip(),
            'user_id'         => current_user()->id,
            'ip_addr'         => $this->request()->remote_ip(),
            'status'          => $status,
            'tempfile_path'   => $_FILES['post']['tmp_name']['file'],
            'tempfile_name'   => $_FILES['post']['name']['file'],
            'is_upload'       => true
        ));
        
        $this->post = Post::create($post_params);
        
        if ($this->post->errors()->blank()) {
            if ($this->params()->md5 && $this->post->md5 != strtolower($this->params()->md5)) {
                $this->post->destroy();
                $this->_respond_to_error("MD5 mismatch", '#error', array('status' => 420));
            } else {
                $api_data = array('post_id' => $this->post->id, 'location' => $this->url_for(array('post#show', 'id' => $this->post->id)));
                if (CONFIG()->dupe_check_on_upload && $this->post->image() && !$this->post->parent_id) {
                    if ($this->params()->format == "xml" || $this->params()->format == "json") {
                        # iTODO
                        // $options = array('services' => SimilarImages::get_services("local"), 'type' => 'post', 'source' => $this->post);

                        // $res = SimilarImages::similar_images($options);
                        // if (!empty($res['posts'])( {
                            // $this->post.>tags = $this->post->tags . " possible_duplicate";
                            // $this->post->save();
                            // $api_data['has_similar_hits'] = true;
                        // }
                    }

                    $api_data['similar_location'] = $this->url_for(array('post#similar', 'id' => $this->post->id, 'initial' => 1));
                    $this->_respond_to_success("Post uploaded", array('post#similar', 'id' => $this->post->id, 'initial' => 1), array('api' => $api_data));
                } else {
                    $this->_respond_to_success("Post uploaded", array('post#show', 'id' => $this->post->id, 'tag_title' => $this->post->tag_title()), array('api' => $api_data));
                }
            }
        } elseif ($this->post->errors()->on('md5')) {
            $p = Post::find_by_md5($this->post->md5);
            
            $update = array('tags' => $p->tags . " " . (isset($this->params()->post['tags']) ? $this->params()->post['tags'] : ''), 'updater_user_id' => $this->session('user_id'), 'updater_ip_addr' => $this->request()->remote_ip());
            if (!$p->source && $this->post->source)
                $update['source'] = $this->post->source;
            $p->update_attributes($update);

            $api_data = array(
                'location' => $this->url_for(array('post#show', 'id' => $p->id)),
                'post_id' => $p->id
            );
            $this->_respond_to_error("Post already exists", array('post#show', 'id' => $p->id, 'tag_title' => $this->post->tag_title()), array('api' => $api_data, 'status' => 423));
        } else {
            $this->_respond_to_error($this->post, '#error');
        }
    }

    public function moderate()
    {
        $this->_set_title('Moderation Queue');
        
        if ($this->request()->post()) {
            $posts = new ActiveRecord_Collection();
            
            if ($this->params()->ids) {
                foreach (array_keys($this->params()->ids) as $post_id) {
                    $post = Post::find($post_id);
                    
                    if ($this->params()->commit == "Approve")
                        $post->approve(current_user()->id);
                    elseif ($this->params()->commit == "Delete") {
                        $post->destroy_with_reason(($this->params()->reason ? $this->params()->reason : $this->params()->reason2), current_user());

                        # Include post data for the parent: deleted posts aren't counted as children, so
                        # their has_children attribute may change.
                        if ($post->parent_id)
                            $posts[] = $post->get_parent();
                    }
                    $post->reload();
                    $posts[] = $post;
                }
            }
            
            $posts->unique();
            
            if ($this->request()->format() == "json" || $this->request()->format() == "xml")
                $api_data = Post::batch_api_data($posts->all());
            else
                $api_data = array();

            if ($this->params()->commit == "Approve")
                $this->_respond_to_success("Post approved", "#moderate", array('api' => $api_data));
            elseif ($this->params()->commit == "Delete")
                $this->_respond_to_success("Post deleted", "#moderate", array('api' => $api_data));
            
        } else {
            if ($this->params()->query) {
                list($sql, $params) = Post::generate_sql($this->params()->query, array('pending' => true, 'order' => "id desc"));
                $this->pending_posts = Post::find_by_sql($sql, $params);
                list($sql, $params) = Post::generate_sql($this->params()->query, array('flagged' => true, 'order' => "id desc"));
                $this->flagged_posts = Post::find_by_sql($sql, $params);
            } else {
                $this->pending_posts = Post::find_all(array('conditions' => "status = 'pending'", 'order' => "id desc"));
                $this->flagged_posts = Post::find_all(array('conditions' => "status = 'flagged'", 'order' => "id desc"));
            }
        }
    }

    public function update()
    {
        $this->post = Post::find($this->params()->id);
        if ($this->post->is_deleted() and !current_user()->is_mod_or_higher()) {
            $this->_respond_to_error('Post Locked', array('#show', 'id' => $this->params()->id), array('status' => 422));
            return;
        }
        $user_id = current_user()->id;
        
        $post = $this->params()->post;
        Post::filter_api_changes($post);
        
        $post['updater_user_id'] = current_user()->id;
        $post['updater_ip_addr'] = $this->request()->remote_ip();
        
        if ($this->post->update_attributes($post)) {
            # Reload the post to send the new status back; not all changes will be reflected in
            # $this->post due to after_save changes.
            $this->post->reload();

            if ($this->params()->format == "json" || $this->params()->format == "xml")
                $api_data = $this->post->api_data();
            else
                $api_data = [];
            $this->_respond_to_success("Post updated", array('#show', 'id' => $this->post->id, 'tag_title' => $this->post->tag_title()), array('api' => $api_data));
        } else {
            $this->_respond_to_error($this->post, array('#show', 'id' => $this->params()->id));
        }
    }

    public function update_batch()
    {
        $user_id = current_user()->id;

        $ids = array();
        if (!is_array($this->params()->post))
            $this->params('post', []);
        foreach ($this->params()->post as $post) {
            if (isset($post[0])) {
                # We prefer { :id => 1, :rating => 's' }, but accept ["123", {:rating => 's'}], since that's
                # what we'll get from HTML forms.
                $post_id = $post[0];
                $post = $post[1];
            } else {
                $post_id = $post['id'];
                unset($post['id']);
            }

            $p = Post::find($post_id);
            $ids[] = $p->id;
            
            # If an entry has only an ID, it was just included in the list to receive changes to
            # a post without changing it (for example, to receive the parent's data after reparenting
            # a post under it).
            if (empty($post)) continue;

            $old_parent_id = $p->parent_id;

            Post::filter_api_changes($post);
            
            if ($p->update_attributes(array_merge($post, array('updater_user_id' => $user_id, 'updater_ip_addr' => $this->request()->remote_ip())))) {
                // post.merge(:updater_user_id => user_id, :updater_ip_addr => request.remote_ip))
                # Reload the post to send the new status back; not all changes will be reflected in
                # @post due to after_save changes.
                // $p->reload();
            }

            if ($p->parent_id != $old_parent_id) {
                $p->parent_id && $ids[] = $p->parent_id;
                $old_parent_id && $ids[] = $old_parent_id;
            }
        }

        # Updates to one post may affect others, so only generate the return list after we've already
        # updated everything.
        # TODO: need better SQL functions.
        $ids = implode(', ', $ids);

        $posts = Post::find_all(array('conditions' => array("id IN ($ids)")));
        $api_data = Post::batch_api_data($posts->all());

        $url = $this->params()->url ?: '#index';
        $this->_respond_to_success("Posts updated", $url, array('api' => $api_data));
        
        // user_id = current_user()->id

        // ids = array()
        // (params['post'] || array()).each do |post|
             // if (post.is_a?(Array) then) {
                // # We prefer { 'id' => 1, 'rating' => 's' }, but accept ["123", {'rating' => 's'}], since that's
                // # what we'll get from HTML forms.
                // post_id = post[0]
                // post = post[1]
            // } else {
                // post_id = post[:id]
                // post.delete(:id)
            // }

            // $this->post = Post.find(post_id)
            // ids[$this->post.id] = true

            // # If an entry has only an ID, it was just included in the list to receive changes to
            // # a post without changing it (for example, to receive the parent's data after reparenting
            // # a post under it).
            // next if post.empty?

            // old_parent_id = $this->post.parent_id

            // Post.filter_api_changes(post)

             // if ($this->post.update_attributes(post.merge('updater_user_id' => user_id, 'updater_ip_addr' => $this->request()->remote_ip()))) {
                // # Reload the post to send the new status back; not all changes will be reflected in
                // # $this->post due to after_save changes.
                // $this->post.reload
            // }

            // if ($this->post.parent_id != old_parent_id) {
                // ids[$this->post.parent_id] = true if $this->post.parent_id
                // ids[old_parent_id] = true if old_parent_id
            // }
        // end

        // # Updates to one post may affect others, so only generate the return; list after we've already
        // # updated everything.
        // posts = Post.find_by_sql(["SELECT * FROM posts WHERE id IN (?)", ids.map { |id, t| id }])
        // api_data = Post.batch_api_data(posts)

        // url = $this->params()->url
        // url = {'#index'} if !url
        // $this->_respond_to_success("Posts updated", url, 'api' => api_data)
    }

    public function delete()
    {
        $this->post = Post::find($this->params()->id);

        if ($this->post && $this->post->parent_id)
            $this->post_parent = Post::find($this->post->parent_id);
        else
            $this->post_parent = null;
    }
    
    public function destroy()
    {
        if ($this->params()->commit == "Cancel") {
            $this->_redirect_to(array('#show', 'id' => $this->params()->id));
            return;
        }

        $this->post = Post::find($this->params()->id);

        if ($this->post->can_user_delete(current_user())) {
            if ($this->post->status == "deleted") {
                if ($this->params()->destroy) {
                    if (current_user()->is_mod_or_higher()) {
                        $this->post->delete_from_database();
                        $this->_respond_to_success("Post deleted permanently", array('#show', 'id' => $this->params()->id));
                    } else {
                        $this->_access_denied();
                    }
                } else {
                    $this->_respond_to_success("Post already deleted", array('#delete', 'id' => $this->params()->id));
                }
            } else {
                Post::static_destroy_with_reason($this->post->id, $this->params()->reason, current_user());
                
                # Destroy in one request.
                if ($this->params()->destroy) {
                    $this->post->delete_from_database();
                    $notice = "Post deleted permanently";
                } else
                    $notice = "Post deleted";
                
                $this->_respond_to_success($notice, array('#show', 'id' => $this->params()->id));
            }
        } else {
            $this->_access_denied();
        }
    }

    public function deleted_index()
    {
         if (!current_user()->is_anonymous() && $this->params()->user_id && (int)$this->params()->user_id == current_user()->id) {
            current_user()->update_attribute('last_deleted_post_seen_at', gmd());
        }

        $page = $this->page_number();
        if ($this->params()->user_id) {
            $this->params()->user_id = (int)$this->params()->user_id;
            $this->posts = Post::paginate(['per_page' => 25, 'order' => "flagged_post_details.created_at DESC", 'joins' => "JOIN flagged_post_details ON flagged_post_details.post_id = posts.id JOIN posts_tags pt ON posts.id = pt.post_id JOIN tags t ON pt.tag_id = t.id", 'conditions' => ["posts.status = 'deleted' AND posts.user_id = ? ", $this->params()->user_id], 'page' => page]);
        } else {
            $this->posts = Post::paginate(['per_page' => 25, 'order' => "flagged_post_details.created_at DESC", 'joins' => "JOIN flagged_post_details ON flagged_post_details.post_id = posts.id JOIN posts_tags pt ON posts.id = pt.post_id JOIN tags t ON pt.tag_id = t.id", 'conditions' => ["posts.status = 'deleted'"], 'page' => $page]);
        }
    }
    
    public function acknowledge_new_deleted_posts()
    {
        if (!current_user()->is_anonymous())
            current_user()->update_attribute('last_deleted_post_seen_at', gmd());
        $this->_respond_to_success("Success", array());
    }
    
    public function index()
    {
        $tags = $this->params()->tags;
        $split_tags = $tags ? array_filter(explode(' ', $tags)) : array();
        $page = $this->page_number();
        $this->tag_suggestions = $this->searching_pool = array();

/*        if $this->current_user.is_member_or_lower? && count(split_tags) > 2 
#            $this->_respond_to_error("You can only search up to two tags at once with a basic account", 'action' => "error")
#            return;
#        elseif count(split_tags) > 6
*/
         if (count($split_tags) > 6) {
            $this->_respond_to_error("You can only search up to six tags at once", "#error");
            return;
        }

        $q = Tag::parse_query($tags);

        $limit = (int)$this->params()->limit;
        isset($q['limit']) && $limit = (int)$q['limit'];
        $limit <= 0 && $limit = 16;
        $limit > 1000 && $limit = 1000;

        $count = 0;
        
        $this->_set_title("/" . str_replace("_", " ", $tags));
        
        // try {
        $count = Post::fast_count($tags);
        // } catch(Exception $x) {
            // $this->_respond_to_error("Error: " . $x->getMessage(), "#error");
            // return;
        // }

        if ($count < 16 && count($split_tags) == 1) {
            $this->tag_suggestions = Tag::find_suggestions($tags);
        }
            

        $this->ambiguous_tags = Tag::select_ambiguous($split_tags);
        if (isset($q['pool']) and is_int($q['pool']))
            $this->searching_pool = Pool::find_by_id($q['pool']);

        $from_api = ($this->params()->format == "json" || $this->params()->format == "xml");

        // $this->posts = Post::find_all(array('page' => $page, 'per_page' => $limit, $count));
        // $this->posts = WillPaginate::Collection.new(page, limit, count);
        
        // $offset = $this->posts->offset();
        // $posts_to_load = $this->posts->per_page();
        $per_page = $limit;
        $offset = ($page - 1) * $per_page;
        $posts_to_load = $per_page;
        
        if (!$from_api) {
            # For forward preloading:
            // $posts_to_load += $this->posts->per_page();
            $posts_to_load += $per_page;

            # If we're not on the first page, load the previous page for prefetching.    Prefetching
            # the previous page when the user is scanning forward should be free, since it'll already
            # be in cache, so this makes scanning the index from back to front as responsive as from
            # front to back.
             if ($page and $page > 1) {
                // $offset -= $this->posts->per_page();
                // $posts_to_load += $this->posts->per_page();
                $offset -= $per_page;
                $posts_to_load += $per_page;
            }
        }

        $this->showing_holds_only = isset($q['show_holds']) && $q['show_holds'] == 'only';
        list ($sql, $params) = Post::generate_sql($q, array('original_query' => $tags, 'from_api' => $from_api, 'order' => "p.id DESC", 'offset' => $offset, 'limit' => $posts_to_load));
        $results = Post::find_by_sql($sql, $params, array('page' => $page, 'per_page' => $per_page));
        
        // $results = call_user_func_array('Post::find_by_sql', array_merge(Post::generate_sql($q, array('original_query' => $tags, 'from_api' => $from_api, 'order' => "p.id DESC", 'offset' => $offset, 'limit' => $posts_to_load)), array(array('page' => $page, 'per_page' => $per_page))));
        
        $this->preload = new ActiveRecord_Collection();
        if (!$from_api) {
            if ($page && $page > 1) {
                $this->preload = $results->slice(0, $limit);
                $results = $results->slice($limit);
            }
            
            $this->preload->merge($results->slice($limit));

            $results = $results->slice(0, $limit);
        }

        # Apply can_be_seen_by filtering to the results.    For API calls this is optional, and
        # can be enabled by specifying filter=1.
        if (!$from_api or $this->params()->filter) {
            $results->delete_if(function($post){return !$post->can_be_seen_by(current_user(), array('show_deleted' => true));});
            // $this->preload = $this->preload->delete_if(function($post){return !$post->can_be_seen_by(current_user());});
        }

        if ($from_api and $this->params()->api_version == "2" and $this->params()->format != "json") {
            $this->_respond_to_error("V2 API is JSON-only", array(), array('status' => 424));
            return;
        }
        $this->posts = $results;

        $this->_respond_to(array(
            'html' => function() use ($split_tags, $tags) {
                 if ($split_tags) {
                    $this->tags = Tag::parse_query($tags);
                } else {
                    # TODO
                    $this->tags = array('include' => Tag::count_by_period(date('Y-m-d', strtotime('-'.CONFIG()->post_index_tags_limit)), gmd(), array('limit' => 25, 'exclude_types' => CONFIG()->exclude_from_tag_sidebar)));
                    // $s->tags = Rails::cache()->fetch("$poptags", 'expires_in' => 1.hour) do
                        // {'include' => Tag.count_by_period(1.day.ago, Time.now, 'limit' => 25, 'exclude_types' => CONFIG["exclude_from_tag_sidebar"])}
                    // end
                }
            },
            'xml' => function() {
                $this->_layout(false);
            },
            'json' => function() {
                if ($this->params()->api_version != "2") {
                    $this->_render(array('json' => array_map(function($p){return $p->api_attributes();}, $this->posts->all())));
                    return;
                }

                $api_data = Post::batch_api_data($this->posts->all(), array(
                    'exclude_tags' => $this->params()->include_tags != "1",
                    'exclude_votes' => $this->params()->include_votes != "1",
                    'exclude_pools' => $this->params()->include_pools != "1"
                ));
                
                $this->_render(array('json' => json_encode($api_data)));
            },
            'atom'
        ));
        
    }

    // public function atom()
    // {
        // $this->posts = Post.find_by_sql(Post.generate_sql($this->params()->tags, 'limit' => 20, 'order' => "p.id DESC"))
        // $this->respond_to(array(
            // format.atom { render 'index' }
        // ));
    // }

    // public function piclens()
    // {
        // $this->posts = WillPaginate::Collection.create(page_number, 16, Post.fast_count($this->params()->tags)) do |pager|
            // pager.replace(Post.find_by_sql(Post.generate_sql($this->params()->tags, 'order' => "p.id DESC", 'offset' => pager.offset, 'limit' => pager.per_page)))
        // end

        // $this->respond_to(array(
            // format.rss
        // ));
    // }

    public function show()
    {
        ActionView::add_helper('avatar');
        try {
            if ($this->params()->cache)
                $this->response()->headers("Cache-Control", "max-age=300");
            $this->cache = $this->params()->cache; # temporary
            $this->body_only = (int)$this->params()->body == 1;
            // $this->post = Post::includes('comments' => array('user'));
            if ($this->params()->md5) {
                if (!$this->post = Post::find_by_md5(strtolower($this->params())))
                    throw ActiveRecord::RecordNotFound();
                // $this->post = $this->post::find_by_md5($this->params()->md5.downcase) || raise(ActiveRecord::RecordNotFound)
            } else {
                $this->post = Post::find($this->params()->id);
            }
            # CHANGED: change in conditions: removed "active"
            $this->pools = Pool::find_all(array('joins' => "JOIN pools_posts ON pools_posts.pool_id = pools.id", 'conditions' => "pools_posts.post_id = {$this->post->id}", 'order' => "pools.name", 'select' => "pools.name, pools.id"));
            
             if ($this->params()->pool_id) {
                $this->following_pool_post = PoolPost::find_first(array('conditions' => array("pool_id = ? AND post_id = ?", $this->params()->pool_id, $this->post->id)));
            } else {
                $this->following_pool_post = PoolPost::find_first(array('conditions' => array("post_id = ?", $this->post->id)));
            }
            // $this->tags = array('include' => explode(' ', $this->post->cached_tags));
            $this->tags = array('include' => $this->post->parsed_cached_tags);
            $this->include_tag_reverse_aliases = true;
            $this->_set_title(str_replace('_', ' ', $this->post->title_tags()));
            $this->_respond_to([
                'html'
            ]);
        } catch (ActiveRecord_Exception_RecordNotFound $e) {
            $this->_respond_to([
                'html' => function() {
                    $this->_render(array('action' => 'show_empty'), array('status' => 404));
                }
            ]);
        }
    }

    public function browse()
    {
        $this->response()->headers("Cache-Control", "max-age=300");
        $this->_render(array('layout' => "bare"));
    }

    public function view()
    {
        $this->_redirect_to(["#show", 'id' => $this->params()->id]);
    }

    public function popular_recent()
    {
        switch($this->params()->period) {
            case "1w":
                $this->period_name = "last week";
                $period = '1 week';
                break;
            case "1m":
                $this->period_name = "last month";
                $period = '1 month';
                break;
            case "1y":
                $this->period_name = "last year";
                $period = '1 year';
                break;
            default:
                $this->params()->period = "1d";
                $this->period_name = "last 24 hours";
                $period = '1 day';
                break;
        }

        $this->post_params = $this->params()->all();
        $end = time();
        $this->start = strtotime('-'.$period);

        $this->_set_title('Exploring ' . $this->period_name);
        
        
        $this->posts = Post::find_all(array('conditions' => array("status <> 'deleted' AND posts.index_timestamp >= ? AND posts.index_timestamp <= ? ", date('Y-m-d', $this->start), date('Y-m-d', $end)), 'order' => "score DESC", 'limit' => 20));

        $this->_respond_to_list("posts");
    }

    public function popular_by_day()
    {
        if (!$this->params()->year || !$this->params()->month || !$this->params()->day ||
            !($this->day = @strtotime($this->params()->year . '-' . $this->params()->month . '-' . $this->params()->day))) {
            $this->day = strtotime('this day');
        }

        $this->_set_title('Exploring '.date('Y', $this->day).'/'.date('m', $this->day).'/'.date('d', $this->day));
        
        $this->posts = Post::find_all(['conditions' => ['created_at BETWEEN ? AND ?', date('Y-m-d', $this->day), date('Y-m-d', strtotime('+1 day', $this->day))], 'order' => 'score DESC', 'limit' => 20]);
        
        $this->_respond_to_list("posts");
    }

    public function popular_by_week()
    {
        if (!$this->params()->year || !$this->params()->month || !$this->params()->day ||
            !($this->start = strtotime('this week', @strtotime($this->params()->year . '-' . $this->params()->month . '-' . $this->params()->day)))) {
            $this->start = strtotime('this week');
        }

        $this->end = strtotime('next week', $this->start);
        
        $this->_set_title('Exploring '.date('Y', $this->start).'/'.date('m', $this->start).'/'.date('d', $this->start) . ' - '.date('Y', $this->end).'/'.date('m', $this->end).'/'.date('d', $this->end));

        $this->posts = Post::find_all(['conditions' => ['created_at BETWEEN ? AND ?', date('Y-m-d', $this->start), date('Y-m-d', $this->end)], 'order' => 'score DESC', 'limit' => 20]);

        $this->_respond_to_list("posts");
    }

    public function popular_by_month()
    {
        if (!$this->params()->year || !$this->params()->month || !($this->start = @strtotime($this->params()->year . '-' . $this->params()->month . '-1'))) {
            $this->start = strtotime('first day of this month');
        }

        $this->end = strtotime('+1 month', $this->start);
        
        $this->_set_title('Exploring '.date('Y', $this->start).'/'.date('m', $this->start));

        $this->posts = Post::find_all(['conditions' => ['created_at BETWEEN ? AND ?', date('Y-m-d', $this->start), date('Y-m-d', $this->end)], 'order' => 'score DESC', 'limit' => 20]);

        $this->_respond_to_list("posts");
    }

    // public function revert_tags()
    // {
        // user_id = current_user()->id
        // $this->post = Post.find($this->params()->id)
        // $this->post.update_attributes('tags' => (int)PostTagHistory.find($this->params()->history_id).tags, 'updater_user_id' => user_id, 'updater_ip_addr' => $this->request()->remote_ip())

        // $this->_respond_to_success("Tags reverted", '#show', 'id' => $this->post.id, 'tag_title' => $this->post.tag_title)
    // }

    public function vote()
    {
        if (!$this->params()->score) {
            $vote = PostVote::find_by_user_id_and_post_id(current_user()->id, $this->params()->id);
            $score = $vote ? $vote->score : 0;
            $this->_respond_to_success("", array(), array('vote' => $score));
            return;
        }
        
        $p = Post::find($this->params()->id);
        $score = (int)$this->params()->score;

        if (!current_user()->is_mod_or_higher() && ($score < 0 || $score > 3)) {
            $this->_respond_to_error("Invalid score", array("#show", 'id' => $this->params()->id, 'tag_title' => $p->tag_title(), 'status' => 424));
            return;
        }

        $vote_successful = $p->vote($score, current_user());

        $api_data = Post::batch_api_data(array($p));
        $api_data['voted_by'] = $p->voted_by();

        if ($vote_successful)
            $this->_respond_to_success("Vote saved", array("#show", 'id' => $this->params()->id, 'tag_title' => $p->tag_title()), array('api' => $api_data));
        else
            $this->_respond_to_error("Already voted", array("#show", 'id' => $this->params()->id, 'tag_title' => $p->tag_title()), array('api' => $api_data, 'status' => 423));
    }

    public function flag()
    {
        $post = Post::find($this->params()->id);

        if ($this->params()->unflag == '1') {
            # Allow the user who flagged a post to unflag it.
            #
            # posts 
            # "approve" is used both to mean "unflag post" and "approve pending post".
            if ($post->status != "flagged") {
                $this->_respond_to_error("Can only unflag flagged posts", array("#show", 'id' => $this->params()->id));
                return;
            }

            if (!current_user()->is_mod_or_higher() and current_user()->id != $post->flag_detail->user_id) {
                $this->_access_denied();
                return;
            }

            $post->approve(current_user()->id);
            $message = "Post approved";
        } else {
            if ($post->status != "active") {
                $this->_respond_to_error("Can only flag active posts", array("#show", 'id' => $this->params()->id));
                return;
            }

            $post->flag($this->params()->reason, current_user()->id);
            $message = "Post flagged";
        }

        # Reload the post to pull in post.flag_reason.
        $post->reload();

        if ($this->request()->format() == "json" || $this->request()->format() == "xml")
            $api_data = Post::batch_api_data(array($post));
        else
            $api_data = [];
        $this->_respond_to_success($message, array("#show", 'id' => $this->params()->id), array('api' => $api_data));
    }

    public function random()
    {
        $max_id = Post::maximum('id');

        foreach(range(1, 10) as $i) {
            $post = Post::find_first(array('conditions' => array("id = ? AND status <> 'deleted'", rand(1, $max_id) + 1)));
            
            if ($post && $post->can_be_seen_by(current_user())) {
                $this->_redirect_to(array('#show', 'id' => $post->id, 'tag_title' => $post->tag_title));
                return;
            }
        }
        
        $this->_notice("Couldn't find a post in 10 tries. Try again.");
        $this->_redirect_to("#index");
    }

    // public function similar()
    // {
        // $this->params = params
         // if ($this->params()->file.blank? then params.delete(:file) end) {
        // if ($this->params()->url.blank? then params.delete(:url) end) {
        // if ($this->params()->id.blank? then params.delete(:id) end) {
        // if ($this->params()->search_id.blank? then params.delete(:search_id) end) {
        // if $this->params()->services.blank? then params.delete(:services) end
        // if $this->params()->threshold.blank? then params.delete(:threshold) end
        // if $this->params()->forcegray.blank? || $this->params()->forcegray == "0" then params.delete(:forcegray) end
        // if $this->params()->initial == "0" then params.delete(:initial) end
        // if !SimilarImages.valid_saved_search($this->params()->search_id) then params.delete(:search_id) end
        // $this->params()->width = $(int)this->params()->width if $this->params()->width
        // $this->params()->height = $(int)this->params()->height if $this->params()->height

        // $this->initial = $this->params()->initial
        // if $this->initial && !$this->params()->services
            // $this->params()->services = "local"
        // }

        // $this->services = SimilarImages.get_services($this->params()->services)
        // if ($this->params()->id) {
            // begin
                // $this->compared_post = Post.find($this->params()->id)
            // rescue ActiveRecord::RecordNotFound
                // $this->render(array('status' => 404)
                // return;
            // end
        // }

        // if ($this->compared_post && $this->compared_post.is_deleted?) {
            // $this->_respond_to_error("Post deleted", 'post#show', 'id' => $this->params()->id, 'tag_title' => $this->compared_post.tag_title)
            // return;
        // }

        // # We can do these kinds of searches:
        // #
        // # File: Search from a specified file.    The image is saved locally with an ID, and sent
        // # as a file to the search servers.
        // #
        // # URL: search from a remote URL.    The URL is downloaded, and then treated as a :file
        // # search.    This way, changing options doesn't repeatedly download the remote image,
        // # and it removes a layer of abstraction when an error happens during download
        // # compared to having the search server download it.
        // #
        // # Post ID: Search from a post ID.    The preview image is sent as a URL.
        // #
        // # Search ID: Search using an image uploaded with a previous File search, using
        // # the search MD5 created.    We're not allowed to repopulate filename fields in the
        // # user's browser, so we can't re-submit the form as a file search when changing search
        // # parameters.    Instead, we hide the search ID in the form, and use it to recall the
        // # file from before.    These files are expired after a while; we check for expired files
        // # when doing later searches, so we don't need a cron job.
        // def search(params)
            // options = params.merge({
                // 'services' => $this->services,
            // })

            // # Check search_id first, so options links that include it will use it.    If the
            // # user searches with the actual form, search_id will be cleared on submission.            if (($this->params()->search_id then) {) {
                // file_path = SimilarImages.find_saved_search($this->params()->search_id)
                // if (file_path.nil?) {
                    // # The file was probably purged.    Delete :search_id before redirecting, so the
                    // # error doesn't loop.
                    // params.delete(:search_id)
                    // return; { 'errors' => { 'error' => "Search expired" } }
                // }
            // } elseif ($this->params()->url || $this->params()->file then) {
                // # Save the file locally.
                // begin
                    // if ($this->params()->url then) {
                        // search = Timeout::timeout(30) do
                            // Danbooru.http_get_streaming($this->params()->url) do |res|
                                // SimilarImages.save_search do |f|
                                    // res.read_body do |block|
                                        // f.write(block)
                                    // end
                                // end
                            // end
                        // end
                    // else # file
                        // search = SimilarImages.save_search do |f|
                            // wrote = 0
                            // buf = ""
                            // while $this->params()->file.read(1024*64, buf) do
                                // wrote += buf.length
                                // f.write(buf)
                            // end

                            // if (wrote == 0 then) {
                                // return; { 'errors' => { 'error' => "No file received" } }
                            // }
                        // end
                    // }
                // rescue SocketError, URI::Error, SystemCallError, Moebooru::Resizer:'ResizeError' => e
                    // return; { 'errors' => { 'error' => "#{e}" } }
                // rescue Timeout:'Error' => e
                    // return; { 'errors' => { 'error' => "Download timed out" } }
                // end

                // file_path = search[:file_path]

                // # Set :search_id in params for generated URLs that point back here.
                // $this->params()->search_id = search[:search_id]

                // # The :width and :height params specify the size of the original image, for display
                // # in the results.    The user can specify them; if !specified, fill it in.
                // $this->params()->width ||= search[:original_width]
                // $this->params()->height ||= search[:original_height]
            // elseif $this->params()->id then
                // options[:source] = $this->compared_post
                // options[:type] = :post
            // }

            // if ($this->params()->search_id then) {
                // options[:source] = File.open(file_path, 'rb')
                // options[:source_filename] = $this->params()->search_id
                // options[:source_thumb] = "/data/search/#{$this->params()->search_id}"
                // options[:type] = :file
            // }
            // options[:width] = $this->params()->width
            // options[:height] = $this->params()->height

            // if (options[:type] == :file) {
                // SimilarImages.cull_old_searches
            // }

            // return; SimilarImages.similar_images(options)
        // }

        // unless $this->params()->url.nil? and $this->params()->id.nil? and $this->params()->file.nil? and $this->params()->search_id.nil? then
            // res = search(params)

            // $this->errors = res[:errors]
            // $this->searched = true
            // $this->search_id = $this->params()->search_id

            // # Never pass :file on through generated URLs.
            // params.delete(:file)
        // } else {
            // res = array()
            // $this->errors = array()
            // $this->searched = false
        // }

        // $this->posts = res[:posts]
        // $this->similar = res

        // if ($this->params()->format == "json" || $this->params()->format == "xml" then) {
            // if ($this->errors[:error]) {
                // $this->_respond_to_error($this->errors[:error], {'#index'}, 'status' => 503)
                // return;
            // }
            // if (not $this->searched) {
                // $this->_respond_to_error("no search supplied", {'#index'}, 'status' => 503)
                // return;
            // }
        // }

        // $this->respond_to(array(
            // fmt.html do
                // if ($this->initial=="1" && $this->posts.empty?) {
                    // flash.keep
                    // redirect_to 'post#show', 'id' => $this->params()->id, 'tag_title' => $this->compared_post.tag_title
                    // return;
                // }
                // if ($this->errors[:error]) {
                    // flash[:notice] = $this->errors[:error]
                // }

                // if ($this->posts then) {
                    // $this->posts = res[:posts_external] + $this->posts
                    // $this->posts = $this->posts.sort { |a, b| res[:similarity][b] <=> res[:similarity][a] }

                    // # Add the original post to the start of the list.
                     // if (res[:source]) {
                        // $this->posts = [ res[:source] ] + $this->posts
                    // } else {
                        // $this->posts = [ res[:external_source] ] + $this->posts
                    // }
                // }
            // ));
            // fmt.json do
                // $this->posts.each { |post|
                    // post.similarity = res[:similarity][post]
                // }
                // res[:posts_external].each { |post|
                    // post.similarity = res[:similarity][post]
                // }
                // api_data = {
                    // 'posts' => $this->posts + res[:posts_external],
                    // 'source' => res[:source] ? res[:source]:res[:external_source],
                    // 'search_id' => $this->search_id
                // }

                // unless res[:errors].empty?
                    // api_data[:error] = array()
                    // res[:errors].map { |server, error|
                        // api_data[:error] << { :server=>server, :message=>error[:message], :services=>error[:services].join(",") }
                    // }
                // end

                // $this->_respond_to_success("", array(), 'api' => api_data)
            // end

            // fmt.xml do
                // x = Builder::XmlMarkup.new('indent' => 2)
                // x.instruct!
                // $this->render(array('xml' => x.posts() {)
                 // unless res[:errors].empty?
                        // res[:errors].map { |server, error|
                            // { :server=>server, :message=>error[:message], :services=>error[:services].join(",") }.to_xml('root' => "error", 'builder' => x, 'skip_instruct' => true)
                        // }
                 // end

                     // if (res[:source]) {
                     // x.source() {
                         // res[:source].to_xml('builder' => x, 'skip_instruct' => true)
                     // }
                    // } else {
                     // x.source() {
                         // res[:external_source].to_xml('builder' => x, 'skip_instruct' => true)
                     // }
                    // }

                    // $this->posts.each { |e|
                     // x.similar(:similarity=>res[:similarity][e]) {
                         // e.to_xml('builder' => x, 'skip_instruct' => true)
                     // }
                    // }
                    // res[:posts_external].each { |e|
                     // x.similar(:similarity=>res[:similarity][e]) {
                         // e.to_xml('builder' => x, 'skip_instruct' => true)
                     // }
                    // }
                // }            }
        // }
    // }

    public function undelete()
    {
        $post = Post::find($this->params()->id);
        $post->undelete();

        $affected_posts = [$post];
        if ($post->parent_id)
            $affected_posts[] = $post->get_parent();
        if ($this->params()->format == "json" || $this->params()->format == "xml")
            $api_data = Post::batch_api_data($affected_posts);
        else
            $api_data = [];
        $this->_respond_to_success("Post was undeleted", ['#show', 'id' => $this->params()->id], ['api' => $api_data]);
    }

    public function error()
    {
    }

    public function exception()
    {
        throw new Exception();
    }
    
    public function import()
    {
        if (!$this->current_user->is_admin_or_higher()) {
            $this->_access_denied();
            return;
        }
        
        $import_dir = RAILS_ROOT . '/public/data/import/';

        if ($this->request()->post()) {
            if ($this->request()->format() == 'json') {
                foreach (explode('::', $this->params()->dupes) as $file) {
                    $file = stripslashes(utf8_decode($file));
                    $file = $import_dir . $file;
                    if (file_exists($file))
                        unlink($file);
                    else
                        $error = true;
                }
                
                $resp = !empty($error) ? array('reason' => 'Some files could not be deleted') : array('success' => true);
                $this->_render(array('json' => $resp));
            }

            $this->_layout(false);
            $this->errors = $this->dupe = false;
            $post = $this->params()->post;
            $post['filename'] = stripslashes(utf8_decode($post['filename']));
            $filepath = $import_dir . $post['filename'];
            
            # Take folders as tags
            if (is_int(strpos($post['filename'], '/'))) {
                $folders = str_replace('#', ':', $post['filename']);
                $tags = array_filter(array_unique(array_merge(explode(' ', $post['tags']), explode('/', $folders))));
                array_pop($tags);
                $post['tags'] = trim($post['tags'].' '.implode(' ', $tags));
            }
            
            $post = array_merge($post, array(
                'ip_addr'       => $this->request()->remote_ip(),
                'user_id'       => current_user()->id,
                'status'        => 'active',
                'tempfile_path' => $filepath,
                'tempfile_name' => $post['filename']
            ));
            
            $this->post = Post::create($post);
            
            if ($this->post->errors()->blank()) {
                $this->status = 'Posted';
            } elseif ($this->post->errors()->invalid('md5')) {
                $this->dupe   = true;
                $this->status = 'Already exists';
                $this->post   = Post::find_by_md5($this->post->md5);
                $this->post->status = 'flagged';
                
            } else {
                $this->errors = $this->post->errors()->full_messages('<br />');
                $this->status = 'Error';
            }
            
        } else {
            $this->_set_title('Import');
            $this->invalid_files = $this->files = [];
            
            list($this->files, $this->invalid_files, $this->invalid_folders) = Post::get_import_files($import_dir);
            
            $pools = Pool::find_all(array('conditions' => 'is_active'));
            
            if ($pools) {
                $this->pool_list = '<datalist id="pool_list">';
                foreach ($pools as $pool)
                    $this->pool_list .= '<option value="' . str_replace('_', ' ', $pool->name) . '" />';
                $this->pool_list .= '</datalist>';
            } else
                $this->pool_list = null;
        }
    }

    // public function download()
    // {
        // require 'base64'

        // data = $this->params()->data
        // filename = $this->params()->filename
        // type = $this->params()->type
        // if (filename.nil?) {
            // filename = "file"
        // }
        // if (type.nil?) {
            // type = "application/octet-stream"
        // }

        // data = Base64.decode64(data)

        // send_data data, 'filename' => filename, 'disposition' => "attachment", 'type' => type
    // }

    protected function _filters()
    {
        return array(
            'before' => [
                ['member_only', 'only' => array('create', 'destroy', 'delete', 'flag', 'revert_tags', 'activate', 'update_batch', 'vote')],
                ['post_member_only', 'only' => array('update', 'upload', 'flag')],
                ['janitor_only', 'only' => array('moderate', 'undelete')]
            ],
            'after' => [
                ['save_tags_to_cookie', 'only' => array('update', 'create')]
            ],
            # iTODO:
            // 'around' => [
                // ['cache_action' => 'only' => ['index', 'atom', 'piclens']]
            // ]
        );
    }
}