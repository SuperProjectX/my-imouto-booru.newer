<?= $this->content_for('subnavbar', function() { ?>
  <li><?= $this->link_to($this->t('post_list'), 'post#index') ?></li>
  <li><?= $this->link_to($this->t('post_browse'), array('post#browse', 'anchor' => '/')) ?></li>
  <li><?= $this->link_to($this->t('post_upload'), 'post#upload') ?></li>
  <!-- <li id="my-subscriptions-container"><?php //echo $this->link_to($this->t('post_subs'), "/", 'id' => 'my-subscriptions') ?></li> -->
  <li><?= $this->link_to($this->t('post_random'), array('post#', 'tags' => 'order:random')) ?></li>
  <li><?= $this->link_to($this->t('post_popular'), 'post#popular_recent') ?></li>
  <li><?= $this->link_to($this->t('post_image'), 'post#similar') ?></li>
  <li><?= $this->link_to($this->t('post_history'), 'history#index') ?></li>
  <?php if (current_user()->is_contributor_or_higher()) : ?>
    <li><?= $this->link_to($this->t('post_batch'), 'batch#') ?></li>
  <?php endif ?>
  <?php if (current_user()->is_janitor_or_higher()) : ?>
    <li><?= $this->link_to($this->t('post_mod'), 'post#moderate', array('id' => 'moderate')) ?></li>
  <?php endif ?>
  <?= $this->yield('footer') ?>
  <li><?= $this->link_to($this->t('post_help'), 'help#posts') ?></li>
<?php }) ?>
