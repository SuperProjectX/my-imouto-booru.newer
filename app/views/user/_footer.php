<?php if ($this->content_for('footer')) : ?>
  <?= $this->content_for('subnavbar', function() { ?>
    <?php $this->yield('footer') ?>
  <?php }) ?>
<?php endif ?>
