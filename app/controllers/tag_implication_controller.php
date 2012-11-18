<?php
class TagImplicationController extends ApplicationController
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
        $tag_implication = $this->params()->tag_implication;
        $tag_implication['new_predicate'] = $tag_implication['predicate'];
        $tag_implication['new_consequent'] = $tag_implication['consequent'];
        $tag_implication['is_pending'] = true;
        $ti = new TagImplication($tag_implication);

        if ($ti->save())
            $this->_notice("Tag implication created");
        else
            $this->_notice("Error: " . $ti->errors()->full_messages(', '));

        $this->_redirect_to("#index");
    }
    
    public function index()
    {
        $this->_set_title("Tag Implications");

        if ($this->params()->commit == "Search Aliases")
            $this->_redirect_to(array('tag_alias#index', 'query' => $this->params()->query));

        if ($this->params()->query) {
            $name = "%" . $this->params()->query . "%";
            $this->implications = TagImplication::paginate(array('calc_rows', 'order' => "is_pending DESC, (SELECT name FROM tags WHERE id = tag_implications.predicate_id), (SELECT name FROM tags WHERE id = tag_implications.consequent_id)", 'per_page' => 20, 'conditions' => array("predicate_id IN (SELECT id FROM tags WHERE name LIKE ?) OR consequent_id IN (SELECT id FROM tags WHERE name LIKE ?)", $name, $name), 'page' => $this->params()->page));
        } else {
            $this->implications = TagImplication::paginate(array('calc_rows', 'order' => "is_pending DESC, (SELECT name FROM tags WHERE id = tag_implications.predicate_id), (SELECT name FROM tags WHERE id = tag_implications.consequent_id)", 'per_page' => 20, 'page' => $this->params()->page));
        }

        $this->_respond_to_list("implications");
    }
    
    public function update()
    {
        !is_array($this->params()->implications) && $this->params()->implications = [];
        $ids = array_keys($this->params()->implications);

        switch($this->params()->commit) {
            case "Delete":
                $can_delete = true;
                
                # iTODO:
                # 'creator_id' column isn't, apparently, filled when creating implications or aliases.
                foreach ($ids as $x) {
                    $ti = TagImplication::find($x);
                    // $can_delete = ($ti->is_pending && $ti->creator_id == current_user()->id);
                    $tis[] = $ti;
                }
                
                if (current_user()->is_mod_or_higher() && $can_delete) {
                    foreach ($tis as $ti)
                        $ti->destroy_and_notify(current_user(), $this->params()->reason);
                
                    $this->_notice("Tag implications deleted");
                    $this->_redirect_to("#index");
                } else
                    $this->_access_denied();
                break;
            
            case "Approve":
                if (current_user()->is_mod_or_higher()) {
                    foreach ($ids as $x) {
                        if (CONFIG()->enable_asynchronous_tasks) {
                            // JobTask.create(:task_type => "approve_tag_implication", :status => "pending", :data => {"id" => x, "updater_id" => @current_user.id, "updater_ip_addr" => $this->request.remote_ip})
                        } else {
                            $ti = TagImplication::find($x);
                            $ti->approve(current_user(), $this->request()->remote_ip());
                        }
                    }
                    
                    $this->_notice("Tag implication approval jobs created");
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