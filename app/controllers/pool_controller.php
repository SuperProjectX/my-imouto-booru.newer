<?php
class PoolController extends ApplicationController
{
    protected function _filters()
    {
        return [
            'before' => [
                ['member_only', 'only' => ['destroy', 'update', 'add_post', 'remove_post', 'import', 'zip']],
                ['contributor_only', 'only' => ['copy', 'transfer_metadata']]
            ]
        ];
    }

    public function index()
    {
        $this->_set_title('Pools');

        $options = array( 
            'per_page' => 20,
            'page' => $this->page_number()
        );

        $order = !empty($this->params()->order) ? $this->params()->order : 'id';

        $conds = array();
        $cond_params = array();

        $search_tokens = array();

        if (!empty($this->params()->query)) {
            $this->_set_title($this->params()->query . " - Pools");
            // $query = array_map(function($v){return addslashes($v);}, explode($this->params()->query);
            // $query = Tokenize.tokenize_with_quotes($this->params[:query] || "")
            $query = explode(' ', addslashes($this->params()->query));

            foreach ($query as &$token) {
                if (preg_match('/^(order|limit|posts):(.+)$/', $token, $m)) {
                    if ($m[1] == "order") {
                        $order = $m[2];
                    } elseif ($m[1] == "limit") {
                        $options['per_page'] = (int)$m[2];
                        $options['per_page'] = min($options['per_page'], 100);
                    } elseif ($m[1] == "posts") {
                        
                        Post::generate_sql_range_helper(Tag::parse_helper($m[2]), "post_count", $conds, $cond_params);
                    }
                } else {
                    // # TODO: removing ^\w- from token.
                    // $token = preg_replace('~[^\w-]~', '', $token);
                    $search_tokens[] = $token;
                }
            }
        }

        if (!empty($search_tokens)) {
            // $value_index_query = QueryParser.escape_for_tsquery($search_tokens);
            $value_index_query = implode('_', $search_tokens);
            if ($value_index_query) {
                // $conds[] = "search_index @@ to_tsquery('pg_catalog.english', ?)";
                // $cond_params[] = implode(' & ', $value_index_query);

                # If a search keyword contains spaces, then it was quoted in the search query
                # and we should only match adjacent words.    tsquery won't do this for us; we need
                # to filter results where the words aren't adjacent.
                #
                # This has a side-effect: any stopwords, stemming, parsing, etc. rules performed
                # by to_tsquery won't be done here.    We need to perform the same processing as
                # is used to generate search_index.    We don't perform all of the stemming rules, so
                # although "jump" may match "jumping", "jump beans" won't match "jumping beans" because
                # we'll filter it out.
                #
                # This also doesn't perform tokenization, so some obscure cases won't match perfectly;
                # for example, "abc def" will match "xxxabc def abc" when it probably shouldn't.    Doing
                # this more correctly requires Postgresql support that doesn't exist right now.
                foreach ($query as $q) {
                    # Don't do this if there are no spaces in the query, so we don't turn off tsquery
                    # parsing when we don't need to.
                    // if (!strstr($q, ' ')) continue;
                    // $conds[] = "(position(LOWER(?) IN LOWER(replace_underscores(name))) > 0 OR position(LOWER(?) IN LOWER(description)) > 0)";
                    #TODO: binding.
                    $conds[] = "(position(LOWER(?) IN LOWER(REPLACE(name, '_', ' '))) > 0 OR position(LOWER(?) IN LOWER(description)) > 0)";
                    $cond_params[] = $q;
                    $cond_params[] = $q;
                }
            }
        }

        // $options['conditions'] = array(implode(' AND ', $conds), $cond_params);
        !empty($conds) && $options['conditions'][] = implode(' AND ', $conds);
        !empty($cond_params) && $options['conditions'] = array_merge($options['conditions'], $cond_params);

        if (empty($order))
            $order = empty($search_tokens) ? 'date' : 'name';

        switch ($order) {
            case "name":    
                $options['order'] = "name asc";
                break;
            case "date":
                $options['order'] = "created_at desc";
                break;
            case "updated":
                $options['order'] = "updated_at desc";
                break;
            case "id":
                $options['order'] = "id desc";
                break;
            default:
                $options['order'] = "created_at desc";
                break;
        }

        $options['calc_rows'] = 'found_pools';
        $this->pools = Pool::paginate($options);

        $samples = [];
        foreach($this->pools as $p) {
            if (!$post = $p->get_sample())
                continue;
            $p_id = (string)$p->id;
            $samples[$p_id] = $post;
        }
        $this->samples = $samples;

        $this->_respond_to_list('pools');
    }

    public function show()
    {
        // required_params('id');

        if (isset($this->params()->samples) && $this->params()->samples == 0)
            unset($this->params()->samples);

        $this->pool = Pool::find(array($this->params()->id));

        $this->browse_mode = current_user()->pool_browse_mode;

        // $q = Tag::parse_query("");

        $q = [];
        $q['pool'] = (int)$this->params()->id;
        $q['show_deleted_only'] = false;
        if ($this->browse_mode == 1) {
            $q['limit'] = 1000;
            $q['order'] = "portrait_pool";
        } else {
            $q['limit'] = 24;
        }
        $page = (int)$this->page_number() > 0 ? (int)$this->page_number() : 1;
        $offset = ($page-1)*$q['limit'];
        
        list($sql, $params) = Post::generate_sql($q, array('from_api' => true, 'offset' => $offset, 'limit' => $q['limit']));
        
        $posts = Post::find_by_sql($sql, $params);
        $this->posts = new ActiveRecord_Collection($posts->all(), ['page' => $page, 'per_page' => $q['limit'], 'offset' => $offset, 'rows' => $posts->rows()]);
        
        $this->_set_title($this->pool->pretty_name());

        # iTODO:
        $this->_respond_to([
            'html',
            // 'xml' => function() {
                // $builder = new Builder_XmlMarkup(['indent' => 2]);
                // $builder->instruct();

                // $xml = $this->pool->to_xml(['builder' => $builder, 'skip_instruct' => true], function() {
                    // $builder->posts(function() use ($builder) {
                        // foreach ($this->posts as $post)
                            // $post->to_xml(['builder' => $builder, 'skip_instruct' => true]);
                    // })
                // });
                // $this->_render(['xml' => $xml]);
            // },
            'json' => function() {
                $this->_render(['json' => $this->pool->to_json()]);
            }
        ]);
    }

    public function update()
    {
        $this->pool = Pool::find($this->params()->id);

        if (!$this->pool->can_be_updated_by(current_user()))
            $this->_access_denied();

        if ($this->request()->post()) {
            $this->pool->update_attributes($this->params()->pool);
            $this->_respond_to_success("Pool updated", array(array('#show', 'id' => $this->params()->id)));
        }
    }

    public function create()
    {
        if ($this->request()->post()) {
            // required_params('pool');
            
            $pool = Pool::create(array_merge($this->params()->pool, array('user_id' => current_user()->id)));
            
            if ($pool->errors()->blank())
                $this->_respond_to_success("Pool created", array(array('#show', 'id' => $pool->id)));
            else
                $this->_respond_to_error($pool, "#index");
        } else
            $pool = new Pool(array('user_id' => current_user()->id));
    }

    // public function copy()
    // {
        // @old_pool = Pool.find_by_id(params[:id])

        // name = params[:name] || "#{@old_pool.name} (copy)"
        // @new_pool = Pool.new(:user_id => @current_user.id, :name => name, :description => @old_pool.description)

        // if request.post?
            // @new_pool.save

            // if not @new_pool.errors.empty? then
                // respond_to_error(@new_pool, :action => "index")
                // return
            // end

            // @old_pool.pool_posts.each { |pp|
                // @new_pool.add_post(pp.post_id, :sequence => pp.sequence)
            // }

            // respond_to_success("Pool created", :action => "show", :id => @new_pool.id)
        // end
    // }

    public function destroy()
    {
        $this->pool = Pool::find($this->params()->id);

        if ($this->request()->post()) {
            if ($this->pool->can_be_updated_by(current_user())) {
                $this->pool->destroy();
                $this->_respond_to_success("Pool deleted", "#index");
            } else
                $this->_access_denied();
        }
    }

    public function add_post()
    {
        if ($this->request()->post()) {
            # iMod
            if ($this->request()->format() == 'json') {
                try {
                    $pool = Pool::find($this->params()->pool_id);
                } catch (ActiveRecord_Exception_RecordNotFound $e) {
                    $this->_render(['json' => ['reason' => 'Pool not found']]);
                    return;
                }
            } else
                $pool = Pool::find($this->params()->pool_id);
            
            $_SESSION['last_pool_id'] = $pool->id;
            
            if (isset($this->params()->pool) && !empty($this->params()->pool['sequence']))
                $sequence = $this->params()->pool['sequence'];
            else
                $sequence = null;
            
            try {
                $pool->add_post($this->params()->post_id, array('sequence' => $sequence, 'user' => current_user()->id));
            } catch (Pool_PostAlreadyExistsError $e) {
                $this->_respond_to_error($e->getMessage(), array('post#show', 'id' => $this->params()->post_id), array('status' => 423));
            } catch (Pool_AccessDeniedError $e) {
                $this->_access_denied();
            } catch (Exception $e) {
                $this->_respond_to_error($e->getMessage(), array('post#show', 'id' => $this->params()->post_id));
            }
            // } catch (Exception $e) {
                // if ($e->getMessage() == 'Post already exists')
                    // $this->_respond_to_error($e->getMessage(), array('post#show', 'id' => $this->params()->post_id), array('status' => 423));
                // elseif ($e->getMessage() == 'Access Denied')
                    // $this->_access_denied();
                // else
                    // $this->_respond_to_error($e->getMessage(), array('post#show', 'id' => $this->params()->post_id));
            // }
            
            $this->_respond_to_success('Post added', array(array('post#show', 'id' => $this->params()->post_id)));
            
        } else {
            if (current_user()->is_anonymous)
                $pools =    Pool::find_all(array('order' => "name", 'conditions' => "is_active = TRUE AND is_public = TRUE"));
            else
                $pools = Pool::find_all(array('order' => "name", 'conditions' => array("is_active = TRUE AND (is_public = TRUE OR user_id = ?)", current_user()->id)));
            
            $post = Post::find($this->params()->post_id);
        }
    }

    public function remove_post()
    {
        $pool = Pool::find($this->params()->pool_id);
        $post = Post::find($this->params()->post_id);
        if (!$pool || !$post)
            return 404;

        if ($this->request()->post()) {
            
            try {
                $pool->remove_post($this->params()->post_id, array('user' => current_user()));
            } catch (Exception $e) {
                if ($e->getMessage() == 'Access Denied')
                    $this->_access_denied();
            }
            
            $api_data = Post::batch_api_data(array($post));

            $this->response()->headers("X-Post-Id", $this->params()->post_id);
            $this->_respond_to_success("Post removed", array('post#show', 'id' => $this->params()->post_id), array('api' => $api_data));
        }
    }

    public function order()
    {
        $this->pool = Pool::find($this->params()->id);

        if (!$this->pool->can_be_updated_by(current_user()))
            $this->_access_denied();

        if ($this->request()->post()) {
            foreach ($this->params()->pool_post_sequence as $i => $seq)
                PoolPost::update($i, array('sequence' => $seq));
            
            $this->pool->reload();
            $this->pool->update_pool_links();
            
            $this->_notice("Ordering updated");
            $this->_redirect_to(array('#show', 'id' => $this->params()->id));
        } else
            $this->pool_posts = $this->pool->pool_posts;
    }

    public function select()
    {
        if (current_user()->is_anonymous())
            $this->pools = Pool::find_all(array('order' => "name", 'conditions' => "is_active = TRUE AND is_public = TRUE"));
        else
            $this->pools = Pool::find_all(array('order' => "name", 'conditions' => array("is_active = TRUE AND (is_public = TRUE OR user_id = ?)", current_user()->id)));

        $options = array('(000) DO NOT ADD' => 0);

        foreach ($this->pools as $p) {
            $options[str_replace('_', ' ', $p->name)] = $p->id;
        }
        $this->options = $options;
        $this->last_pool_id = !empty($_SESSION['last_pool_id']) ? $_SESSION['last_pool_id'] : null;
        $this->_layout(false);
    }

// public function zip()
// {
        // if (!CONFIG()->pool_zips) {
                // throw new ActiveRecord_RecordNotFound();
        // }
// }

// public function transfer_metadata()
// {

// }
}