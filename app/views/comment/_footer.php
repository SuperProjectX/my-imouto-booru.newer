<?= $this->content_for('subnavbar', function() { ?>
  <li><?= $this->link_to($this->t('comment_list'), '#index') ?></li>
  <li><?= $this->link_to($this->t('comment_search'), '#search') ?></li>
  <?php if (current_user()->is_janitor_or_higher()) : ?>
    <li><?= $this->link_to($this->t('comment_moderate'), '#moderate') ?></li>
  <?php endif ?>
  <li><?= $this->link_to($this->t('comment_help'), 'help#comments') ?></li>
<?php }) ?>
