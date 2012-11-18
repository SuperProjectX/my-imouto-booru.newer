<h4>Job Tasks</h4>

<table width="100%" class="highlightable">
  <thead>
    <tr>
      <th width="5%">Id</th>
      <th width="15%">Type</th>
      <th width="10%">Status</th>
      <th width="40%">Data</th>
      <?php if (current_user()->is_mod_or_higher()) : ?>
      <th width="30%">Message</th>
      <?php endif ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($this->job_tasks as $job_task) : ?>
      <tr>
        <td><?= $job_task->id ?></td>
        <td><?= $this->link_to($this->h($job_task->task_type), ['action' => "show", 'id' => $job_task->id]) ?></td>
        <td><?= $this->h($job_task->status) ?></td>
        <td><?= $this->h($job_task->pretty_data()) ?: "ERROR" ?></td>
        <?php if (current_user()->is_mod_or_higher()) : ?>
        <td><?= $this->h($job_task->status_message) ?></td>
        <?php endif ?>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>

<div id="paginator">
  <?= $this->will_paginate($this->job_tasks) ?>
</div>
