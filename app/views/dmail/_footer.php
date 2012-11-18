<?php $this->content_for('subnavbar', function(){ ?>
  <?= $this->yield('footer') ?>
  <li><?= $this->link_to($this->t('dmail_footer_inbox'), ['action' => "inbox"]) ?></li>
  <li><?= $this->link_to($this->t('dmail_footer_compose'), ['action' => "compose"]) ?></li>
  <li><?= $this->link_to($this->t('dmail_mark'), ['action' => "mark_all_read"], ['method' => 'post']) ?></li>
<?php }) ?>
