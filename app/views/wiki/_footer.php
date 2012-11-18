<?php $this->content_for('subnavbar', function(){ ?>
  <li><?= $this->link_to($this->t('.list'), ['action' => "index"]) ?></li>
  <li><?= $this->link_to($this->t('.new'), ['action' => "add"]) ?></li>
  <?= $this->yield('footer') ?>
  <li><?= $this->link_to($this->t('.help'), ['controller' => "help", 'action' => "wiki"]) ?></li>
<?php }) ?>
