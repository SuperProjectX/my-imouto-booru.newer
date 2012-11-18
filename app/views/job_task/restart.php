<h4>Restart Job #<?= $this->job_task.id ?></h4>

<?= $this->form_tag('action' => "restart", function(){ ?>
  <?= $this->submit_tag("Yes") ?>
  <?= $this->button_to_function("No", "location.back()") ?>
<?php }) ?>
