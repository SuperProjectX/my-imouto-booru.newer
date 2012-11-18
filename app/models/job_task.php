<?php
class JobTask extends ActiveRecord_Base
{
    static public function execute_once()
    {
        foreach (self::find_all(array('conditions' => array("status = ?", "pending"), 'order' => "id desc")) as $task) {
        // foreach (self::find_all(array('order' => "id desc")) as $task) {
            $task->execute();
            sleep(1);
        }
    }
    
    public function pretty_data()
    {
        switch ($this->task_type) {
            // case "mass_tag_edit":
                // start = data["start_tags"]
                // result = data["result_tags"]
                // user = User.find_name(data["updater_id"])
                
                // "start:#{start} result:#{result} user:#{user}"
                // break;
            // case "approve_tag_alias"
                // ta = TagAlias.find(data["id"])
                // "start:#{ta.name} result:#{ta.alias_name}"
                // break;
            // case "approve_tag_implication"
                // ti = TagImplication.find(data["id"])
                // "start:#{ti.predicate.name} result:#{ti.consequent.name}"
                
            // case "calculate_tag_subscriptions"
                // last_run = data["last_run"]
                // "last run:#{last_run}"

            // case "upload_posts_to_mirrors"
                // ret = ""
                // if data["post_id"]
                    // ret << "uploading post_id #{data["post_id"]}"
                // elsif data["left"]
                    // ret << "sleeping"
                // else
                    // ret << "idle"
                // end
                // ret << (" (%i left) " % data["left"]) if data["left"]
                // ret
            
            case "periodic_maintenance":
                if ($this->status == "processing")
                    return !empty($this->data->step) ? $this->data->step : 'unknown';
                elseif ($this->status != "error") {
                    $next_run = (!empty($this->data->next_run) ? strtotime($this->data->next_run) : 0) - time();
                    $next_run_in_minutes = $next_run / 60;
                    if ($next_run_in_minutes > 0)
                        $eta = "next run in ".round($next_run_in_minutes / 60.0)." hours";
                    else
                        $eta = "next run imminent";
                    return "sleeping (".$eta.")";
                }
                break;

            // case "upload_batch_posts"
                // if status == "pending" then
                    // return "idle"
                // elsif status == "processing" then
                    // user = User.find_name(data["user_id"])
                    // return "uploading #{data["url"]} for #{user}" 
                // end
            // case "update_post_frames"
                // if status == "pending" then
                    // return "idle"
                // elsif status == "processing" then
                    // return data["status"]
                // end
            // end
        }
    }
    
    public function execute()
    {
        if ($this->repeat_count > 0)
            $count = $this->repeat_count - 1;
        else
            $count = $this->repeat_count;
        
        try {
            // self::execute_sql("SET statement_timeout = 0");
            $this->update_attribute('status', "processing");
            $task_function = 'execute_'.$this->task_type;
            $this->$task_function();
            
            if ($count == 0)
                $this->update_attribute('status', "finished");
            else
                $this->update_attributes(array('status' => "pending", 'repeat_count' => $count));
            
        } catch (SystemExit $x) {
            // update_attributes(:status => "pending")
            // raise x
        } catch (Exception $x) {
            // text = "\n\n"
            // text << "Error executing job: #{task_type}\n"
            // text << "        "
            // text << x.backtrace.join("\n        ")
            // logger.fatal(text)

            // update_attributes(:status => "error", :status_message => "#{x.class}: #{x}")
        }
    }
    
    public function execute_periodic_maintenance()
    {
        if (!empty($this->data->next_run) && $this->data->next_run > gmd())
            return;
        
        // $this->_update_data(array("step" => "recalculating post count"));
        // Post::recalculate_row_count();
        $this->_update_data(array("step" => "recalculating tag post counts"));
        Tag::recalculate_post_count();
        $this->_update_data(array("step" => "purging old tags"));
        Tag::purge_tags();
        
        // $next_run = new DateTime(gmd());
        // $next_run->add(new DateInterval('PT6H'));
        // $this->_update_data(array("next_run" => $next_run->format('Y-m-d H:i:s'), "step" => null));
        
        $next_run = strtotime('+6 hours');
        $this->_update_data(array("next_run" => date('Y-m-d H:i:s', $next_run), "step" => null));
    }
    
    protected function _init()
    {
        $this->data = $this->data_as_json ? json_decode($this->data_as_json) : new stdClass();
    }
    
    protected function _on_data_change($data)
    {
        $this->data_as_json = json_encode($data);
        return (object)$data;
    }

    private function _update_data($data)
    {
        $data = array_merge((array)$this->data, $data);
        $this->update_attributes(array('data' => $data));
    }
}