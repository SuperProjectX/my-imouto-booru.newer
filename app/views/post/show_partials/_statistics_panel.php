<div id="stats" class="vote-container">
  <h5><?= $this->t('post_stats') ?></h5>
  <ul>
    <li><?= $this->t('post_stats_id') ?><?= $this->post->id ?></li>
    <li><?= $this->t('post_stats_posted') ?><?= $this->link_to($this->t(array('time.x_ago', 't' => $this->time_ago_in_words($this->post->created_at))), array('#index', 'tags' => 'date:' . substr($this->post->created_at, 0, 10)), array('title' => substr(date('r', strtotime($this->post->created_at)), 0, -6))) ?> <?= $this->t(array('.uploaded_by_user_html', 'u' => $this->link_to_if($this->post->user_id, $this->post->author(), array('user#show', 'id' => $this->post->user_id)))) ?></li>
    <?php if (current_user()->is_admin() && $this->post->approver) : ?>
      <li><?= $this->t('post_stats_approver') ?><?= $this->post->approver->name ?></li>
    <?php endif ?>
    <?php if ($this->post->image()) : ?>
      <li><?= $this->t('post_stats_size') ?><?= $this->post->width ?>x<?= $this->post->height ?></li>
    <?php endif ?>
    <?php if ($this->post->source) : ?>
      <?php if (strpos($this->post->source, 'http') === 0) : ?>
        <li><?= $this->t('post_stats_source') ?><?= $this->link_to(substr($this->post->source, 8, 20) . "...", $this->post->normalized_source(), array('rel' => 'nofollow', 'target' => '_blank')) ?></li>
      <?php else: ?>
        <li><?= $this->t('post_stats_source') ?><?= $this->post->source ?></li>
      <?php endif ?>
    <?php endif ?>
    <li><?= $this->t('post_stats_rating') ?><?= $this->post->pretty_rating() ?> <?= $this->vote_tooltip_widget() ?></li>

    <li>
      <?= $this->t('post_stats_score') ?><span id="post-score-<?= $this->post->id ?>"><?= $this->post->score ?></span>
      <?= $this->vote_widget(current_user()) ?>
    </li>

    <li><?= $this->t('post_stats_fav') ?><span id="favorited-by"><?= $this->favorite_list($this->post) ?></span> <span id="favorited-by-more"></span></li>
  </ul>
</div>

<?= $this->content_for('post_cookie_javascripts', function() { ?>
<script type="text/javascript">
  var widget = new VoteWidget($("stats"));
  widget.set_post_id(<?= $this->post->id ?>);
  widget.init_hotkeys();

  Post.init_add_to_favs(<?= $this->post->id ?>, $("add-to-favs"), $("remove-from-favs"));
</script>
<?php }) ?>

