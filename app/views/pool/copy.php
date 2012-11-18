<h4><?=$this->$this->t('pool_clone' ?></h4>

<?= $this->form_tag('action' => "copy", function(){ ?>
  <?= hidden_field_tag "id", $this->params()->id ?>
  <label><?=$this->$this->t('pool_name' ?></label> <?= $this->text_field_tag("name", $this->new_pool.pretty_name) ?>
  <?= $this->submit_tag($this->t('pool_copy')) ?> <?= $this->button_to_function($this->t('pool_cancel'), "history.back()") ?></td>
<?php }) ?>

<?= $this->render_partial("footer") ?>

<script type="text/javascript">$("name").focus();</script>
