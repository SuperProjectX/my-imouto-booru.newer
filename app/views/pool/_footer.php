<?php $this->content_for('subnavbar', function(){ ?>
  <li><?= $this->link_to($this->t('pool_list'), ['action' => "index"]) ?></li>
  <li><?= $this->link_to($this->t('pool_new'), ['action' => "create"]) ?></li>
  <?= $this->yield('footer') ?>
  <li><?= $this->link_to($this->t('pool_help'), ['controller' => "help", 'action' => "pools"]) ?></li>
  <?= $this->yield('footer_final') ?>
<?php }) ?>
