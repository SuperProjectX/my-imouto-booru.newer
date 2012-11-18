<div id="post-popular">
  <h3><?= $this->link_to('«', ['post#popular_by_week', 'year' => date('Y', strtotime('-1 week', $this->start)), 'month' => date('m', strtotime('-1 week', $this->start)), 'day' => date('d', strtotime('-1 week', $this->start))]) ?> <?= date("F d, Y", $this->start) ?> - <?= date("F d, Y", $this->end) ?> <?= $this->link_to_if($this->start <= time(), '»', ['post#popular_by_week', 'year' => date('Y', strtotime('+1 week', $this->start)), 'month' => date('m', strtotime('+1 week', $this->start)), 'day' => date('d', strtotime('+1 week', $this->start))]) ?></h3>

  <?= $this->render_partial('posts', ['posts' => $this->posts]) ?>
</div>

<?= $this->content_for('subnavbar', function() { ?>
  <li><?= $this->link_to("Popular", ['post#popular_by_day', 'month' => date('m', $this->start), 'day' => date('d', $this->start), 'year' => date('Y', $this->start)]) ?></li>
  <li><?= $this->link_to("Popular (by week)", ['post#popular_by_week', 'year' => date('Y', $this->start), 'month' => date('m', $this->start), 'day' => date('d', $this->start)]) ?></li>
  <li><?= $this->link_to("Popular (by month)", ['post#popular_by_month', 'year' => date('Y', $this->start), 'month' => date('m', $this->start)]) ?></li>
<?php }) ?>

<?= $this->render_partial('footer') ?>
