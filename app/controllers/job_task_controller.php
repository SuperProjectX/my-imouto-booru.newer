<?php
class JobTaskController extends ApplicationController
{
    public function index()
    {
        $this->job_tasks = JobTask::paginate(['per_page' => 25, 'order' => "id DESC", 'page' => $this->page_number()]);
    }

    public function show()
    {
        $this->job_task = JobTask::find($this->params()->id);

        if ($this->job_task->task_type == "upload_post" && $this->job_task->status == "finished") {
            $this->_redirect_to(['controller' => "post", 'action' => "show", 'id' => $this->job_task->status_message]);
        }
    }

    public function destroy()
    {
        $this->job_task = JobTask::find($this->params()->id);

        if ($this->request()->post()) {
            $this->job_task->destroy();
            $this->_redirect_to(['action' => "index"]);
        }
    }

    public function restart()
    {
        $this->job_task = JobTask::find($this->params()->id);

        if ($this->request()->post()) {
            $this->job_task->update_attributes(['status' => "pending", 'status_message' => ""]);
            $this->_redirect_to(['action' => "show", 'id' => $this->job_task->id]);
        }
    }
    
    protected function _filters()
    {
        return [
            'before' => [
                ['admin_only', 'only' => ['destroy', 'restart']]
            ]
        ];
    }
}
