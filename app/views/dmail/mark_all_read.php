<h4><?=$this->$this->t('dmail_mark_all_text' ?></h4>
<p><?=$this->t('dmail_mark_all_text2' ?></p>

<?= $this->form_tag('action' => "mark_all_read", function(){ ?>
  <?= $this->submit_tag($this->t('dmail_yes')) ?>
  <?= $this->submit_tag($this->t('dmail_no')) ?>
<?php }) ?>
