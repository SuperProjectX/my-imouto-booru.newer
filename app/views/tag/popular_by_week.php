<div id="tag-list">
  <h1><?= $this->link_to('«', ['controller' => "tag", 'action' => "popular_by_week", 'year' => date('Y', strtotime('-1 week', $this->start)), 'month' => date('m', strtotime('-1 week', $this->start)), 'day' => date('d', strtotime('-1 week', $this->start))]) ?> <?= date("F d, Y", $this->start) ?> - <?= date("F d, Y", $this->end) ?> <?= $this->link_to_if($this->start <= time(), '»', ['controller' => "tag", 'action' => "popular_by_week", 'year' => date('Y', strtotime('+1 week', $this->start)), 'month' => date('m', strtotime('+1 week', $this->start)), 'day' => date('d', strtotime('+1 week', $this->start))]) ?></h1>

  <?= $this->cloud_view($this->tags, 3) ?>
</div>

<?php $this->content_for('footer', function(){ ?>
  <p><?= $this->link_to($this->t('tag_popular_day'), ['action' => "popular_by_day"]) ?> | <?= $this->link_to($this->t('tag_popular_week'), ['action' => "popular_by_week"]) ?> | <?= $this->link_to($this->t('tag_popular_month'), ['action' => "popular_by_month"]) ?></p>
<?php }) ?>

<?= $this->render_partial("footer") ?>
