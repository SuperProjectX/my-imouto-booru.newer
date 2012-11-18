<?php $this->content_for('subnavbar', function(){ ?>
  <li><?= $this->link_to($this->t('artist_footer_list'), ['action' => 'index']) ?></li>
  <li><?= $this->link_to($this->t('artist_footer_add'), ['action' => 'create']) ?></li>
  <li><?= $this->link_to($this->t('artist_footer_help'), ['controller' => '/help', 'action' => 'artists']) ?></li>
<?php }) ?>
