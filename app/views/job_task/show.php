<h4>Job #<?= $this->job_task->id ?></h4>

<ul>
  <li><strong>Type</strong>: <?= $this->job_task.task_type ?></li>
  <li><strong>Status</strong>: <?= $this->job_task.status ?></li>
  <li><strong>Data</strong>: <?= $this->job_task.pretty_data rescue "ERROR" ?></li>
  <li><strong>Message</strong>: <?= $this->job_task.status_message ?></li>
</ul>

<?php $this->content_for('subnavbar', function(){ ?>
  <li><?= $this->link_to("List", ['action' => "index"]) ?></li>
  <?php if ($this->job_task.status == "error" []) : ?>
    <li><?= $this->link_to("Restart", ['action' => "restart", 'id' => $this->job_task.id]) ?></li>
  <?php endif ?>
<?php }) ?>
