<h3><?=$this->t('pool_delete') ?></h3>

<?= $this->form_tag([], function(){ ?>
  <p><?=$this->t('pool_confirm') ?>"<?= $this->h($this->pool->pretty_name()) ?>"<?=$this->t('pool_question_mark') ?></p>
  <?= $this->submit_tag($this->t('pool_yes')) ?> <?= $this->button_to_function($this->t('pool_no'), "history.back()") ?>
<?php }) ?>

<?= $this->render_partial("footer") ?>
