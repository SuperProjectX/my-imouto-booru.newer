<h4><?= $this->t('.title') ?></h4>
<p><?= $this->t(['.confirm', 'name' => $this->artist->name]) ?></p>

<?= $this->form_tag([], ['level' => 'privileged'], function(){ ?>
  <?= $this->submit_tag($this->t('buttons._yes')) ?>
  <?= $this->submit_tag($this->t('buttons._no')) ?>
<?php }) ?>
