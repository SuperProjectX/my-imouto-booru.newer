<?= $this->content_for('subnavbar', function() { ?>
  <li><?= $this->link_to($this->t('tag_list'), 'tag#index') ?></li>
  <li><?= $this->link_to($this->t('tag_popular'), 'tag#popular_by_day') ?></li>
  <li><?= $this->link_to($this->t('tag_aliases'), 'tag_alias#index') ?></li>
  <li><?= $this->link_to($this->t('tag_imp'), 'tag_implication#index') ?></li>
  <?php if (current_user()->is_mod_or_higher()) : ?>
    <li><?= $this->link_to($this->t('tag_mass'), 'tag#mass_edit') ?></li>
  <?php endif ?>
  <li><?= $this->link_to($this->t('tag_edit'), 'tag#edit') ?></li>
  <?= $this->yield('footer') ?>
  <li><?= $this->link_to($this->t('tag_help'), 'help#tags') ?></li>
<?php }) ?>
