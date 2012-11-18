<?php
class DmailController extends ApplicationController
{
    protected function _filters()
    {
        return [
            'before' => ['blocked_only']
        ];
    }

    public function preview()
    {
        $this->_layout(false);
    }

    public function auto_complete_for_dmail_to_name()
    {
        $this->users = isset($this->params()->dmail['to_name']) ? User::name_starts_with($this->params()->dmail['to_name']) : [];
        $this->_layout(false);
    }

    public function show_previous_messages()
    {
        $this->dmails = Dmail::find_all(['conditions' => ["(to_id = ? or from_id = ?) and parent_id = ? and id < ?", $this->current_user->id, $this->current_user->id, $this->params()->parent_id, $this->params()->id], 'order' => "id asc"]);
        $this->_layout(false);
    }

    public function compose()
    {
        $this->dmail = new Dmail();
    }

    public function create()
    {
        if (Dmail::count(['conditions' => ['from_id = ? AND created_at > ?', $this->current_user->id, date('Y-m-d H:i:s', time()-3600)]]) > 10) {
            $this->_notice("You can't send more than 10 dmails per hour.");
            $this->_redirect_to('#inbox');
            return;
        }
        $this->dmail = Dmail::create(array_merge($this->params()->dmail, ['from_id' => $this->current_user->id]));

        if ($this->dmail->errors()->blank()) {
            $this->_notice("Message sent to ".$this->params()->dmail['to_name']);
            $this->_redirect_to("#inbox");
        } else {
            $this->_notice("Error: " . $this->dmail->errors()->full_messages(", "));
            $this->_render(['action' => "compose"]);
        }
    }

    public function inbox()
    {
        $this->dmails = Dmail::paginate(['conditions' => ["to_id = ? or from_id = ?", $this->current_user->id, $this->current_user->id], 'order' => "created_at desc", 'per_page' => 25, 'page' => $this->page_number()]);
    }

    public function show()
    {
        $this->dmail = Dmail::find($this->params()->id);

        if ($this->dmail->to_id != $this->current_user->id && $this->dmail->from_id != $this->current_user->id) {
            $this->_notice("Access denied");
            $this->_redirect_to("user#login");
            return;
        }

        if ($this->dmail->to_id == $this->current_user->id) {
            $this->dmail->mark_as_read($this->current_user);
        }
    }

    public function mark_all_read()
    {
        if ($this->params()->commit == "Yes") {
            foreach (Dmail::find_all(['conditions' => ["to_id = ? and has_seen = false", $this->current_user->id]]) as $dmail)
                $dmail->update_attribute('has_seen', true);

            $this->current_user->update_attribute('has_mail', false);
            $this->_respond_to_success("All messages marked as read", ['action' => "inbox"]);
        } else {
            $this->_redirect_to("#inbox");
        }
    }
}
