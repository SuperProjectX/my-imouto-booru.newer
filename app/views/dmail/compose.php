<h4><?=$this->$this->t('dmail_compose' ?></h4>

<?= $this->render_partial("compose", ['from_id' => current_user()->id]) ?>

<?= $this->render_partial("footer") ?>
