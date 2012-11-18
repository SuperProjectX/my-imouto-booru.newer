<?php // why call a partial? $this->render_partial("select_form") ?>
<?= $this->form_tag("#add_post", function() { ?>
  <?= $this->hidden_field_tag("post_id", $this->params()->post_id) ?>
  <?= $this->select_tag("pool_id", [$this->options, $this->last_pool_id]) ?>
  <?= $this->button_to_function($this->t('pool_add2'), "Pool.add_post({$this->params()->post_id}, \$F('pool_id'))", ['level' => 'member']) ?>
<?php }) ?>

