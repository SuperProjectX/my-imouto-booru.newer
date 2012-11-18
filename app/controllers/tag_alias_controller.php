<?php
class TagAliasController extends ApplicationController
{
    protected function _filters()
    {
        return [
            'before' => [
                ['member_only', 'only' => ['create']]
            ]
        ];
    }
    
    public function create()
    {
        $ta = new TagAlias($this->params()->tag_alias);

        $ta->is_pending = true;

        if ($ta->save())
            $this->_notice("Tag alias created");
        else
            $this->_notice("Error: " . $ta->errors()->full_messages(', '));

        $this->_redirect_to("#index");
    }
    
    public function index()
    {
        $this->_set_title("Tag Aliases");

        if ($this->params()->commit == "Search Implications") {
            $this->_redirect_to(array('tag_implication#index', 'query' => $this->params()->query));
            return;
        }

        if ($this->params()->query) {
            $name = "%" . $this->params()->query . "%";
            $this->aliases = TagAlias::paginate(array('order' => "is_pending DESC, name", 'per_page' => 20, 'conditions' => array("name LIKE ? OR alias_id IN (SELECT id FROM tags WHERE name LIKE ?)", $name, $name), 'page' => $this->params()->page));
        } else
            $this->aliases = TagAlias::paginate(array('order' => "is_pending DESC, name", 'per_page' => 20, 'page' => $this->params()->page));

        $this->_respond_to_list('aliases');
    }
    
    public function update()
    {
        !is_array($this->params()->aliases) && $this->params()->aliases = [];
        
        $ids = array_keys($this->params()->aliases);

        switch ($this->params()->commit) {
            case "Delete":
                $validate_all = true;
                
                foreach ($ids as $id) {
                    $ta = TagAlias::find($id);
                    if (!$ta->is_pending || $ta->creator_id != current_user()->id) {
                        $validate_all = false;
                        break;
                    }
                }
                
                if (current_user()->is_mod_or_higher() || $validate_all) {
                    foreach ($ids as $x) {
                        if ($ta = TagAlias::find($x))
                            $ta->destroy_and_notify(current_user(), $this->params()->reason);
                    }
                
                    $this->_notice("Tag aliases deleted");
                    $this->_redirect_to("#index");
                } else
                    $this->_access_denied();
                break;

            case "Approve":
                if (current_user()->is_mod_or_higher()) {
                    foreach ($ids as $x) {
                        // if (CONFIG()->enable_asynchronous_tasks) {
                            // JobTask.create(:task_type => "approve_tag_alias", :status => "pending", :data => {"id" => x, "updater_id" => @current_user.id, "updater_ip_addr" => $this->request.remote_ip})
                        // } else {
                            $ta = TagAlias::find($x);
                            $ta->approve(current_user()->id, $this->request()->remote_ip());
                        // }
                    }
                    
                    $this->_notice("Tag alias approval jobs created");
                    $this->_redirect_to('job_task#index');
                } else
                    $this->_access_denied();
                break;
            
            default:
                $this->_access_denied();
                break;
        }
    }
}