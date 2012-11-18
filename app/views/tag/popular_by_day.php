<div id="tag-list">
  <h1><?= $this->link_to('«', ['controller' => "tag", 'action' => "popular_by_day", 'year' => date('Y', strtotime('-1 day', $this->day)), 'month' => date('m', strtotime('-1 day', $this->day)), 'day' => date('d', strtotime('-1 day', $this->day))]) ?> <?= date("F d, Y", $this->day) ?> <?= $this->link_to_if($this->day <= time(), '»', ['tag#popular_by_day', 'year' => date('Y', strtotime('+1 day', $this->day)), 'month' => date('m', strtotime('+1 day', $this->day)), 'day' => date('d', strtotime('+1 day', $this->day))]) ?></h1>
  <?= $this->cloud_view($this->tags, 1.5) ?>
</div>

<?php $this->content_for('footer', function(){ ?>
  <li><?= $this->link_to($this->t('static7'), ['action' => "popular_by_day"]) ?></li>
  <li><?= $this->link_to($this->t('static8'), ['action' => "popular_by_week"]) ?></li>
  <li><?= $this->link_to($this->t('static9'), ['action' => "popular_by_month"]) ?></li>
<?php }) ?>

<?= $this->render_partial("footer") ?>
