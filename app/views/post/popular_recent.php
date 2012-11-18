<div id="post-popular">
  <h3>
    <?php foreach(["1d","1w","1m","1y"] as $period) : ?>
      <?php if ($this->params()->period == $period) : ?>
        <?= ucfirst($this->period_name) ?>
      <?php else: ?>
        <?= $this->link_to($period, ['post#popular_recent', 'period' => $period]) ?>
      <?php endif ?>
    <?php endforeach ?>
  </h3>

  <?= $this->render_partial('posts', ['posts' => $this->posts]) ?>
</div>

<?= $this->content_for('subnavbar', function() { ?>
  <li><?= $this->link_to("Popular", ['post#popular_by_day', 'month' => date('m', $this->start), 'day' => date('d', $this->start), 'year' => date('Y', $this->start)]) ?></li>
  <li><?= $this->link_to("Popular (by week)", ['post#popular_by_week', 'year' => date('Y', $this->start), 'month' => date('m', $this->start), 'day' => date('d', $this->start)]) ?></li>
  <li><?= $this->link_to("Popular (by month)", ['post#popular_by_month', 'year' => date('Y', $this->start), 'month' => date('m', $this->start)]) ?></li>
<?php }) ?>

<?= $this->render_partial('footer') ?>
