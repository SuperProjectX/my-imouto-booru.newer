<div id="comment-list">
  <?php if ($this->posts->blank()) : ?>
    <h4><?= $this->t('comments_none') ?></h4>
  <?php endif ?>

  <?php foreach ($this->posts as $post) : ?>
    <div class="post">
      <div class="col1">
        <?= $this->link_to($this->image_tag($post->preview_url(), array('title' => $post->tags, 'class' => 'preview javascript-hide', 'id' => 'p'.$post->id, 'width' => $post->preview_dimensions()[0], 'height' => $post->preview_dimensions()[1])), array('post#show', 'id' => $post->id)) ?>&nbsp;
      </div>
      <div class="col2" id="comments-for-p<?= $post->id ?>">
        <div class="header">
          <div>
            <span class="info"><strong><?= $this->t('comment_date') ?></strong> <?= $this->compact_time($post->created_at) ?></span>
            <span class="info"><strong><?= $this->t('comment_user') ?></strong> <?= $this->link_to($this->h($post->author()), array('user#show', 'id' => $post->user->id)) ?></span>
            <span class="info"><strong><?= $this->t('comment_rating') ?></strong> <?= $post->pretty_rating() ?></span>
            <span class="info vote-container"><strong><?= $this->t('comment_score') ?></strong>
              <span id="post-score-<?= $post->id ?>"><?= $post->score ?></span>
              <?= $this->vote_widget(current_user()) ?>
              <?= $this->vote_tooltip_widget() ?>
            </span>

            <?php if ($post->comments->size() > 6) : ?>
              <span class="info"><strong><?= $this->t('.hidden') ?></strong> <?= $this->link_to($post->comments->size() - 6, array('post#show', 'id' => $post->id)) ?></span>
            <?php endif ?>
          </div>
          <div class="tags">
            <strong><?= $this->t('comment_tags') ?></strong>
            <?php foreach (explode(' ', $post->tags) as $name) : ?>
              <span class="tag-type-<?= Tag::type_name($name) ?>">
                <?= $this->link_to(str_replace('_', ' ', $name), array('post#', 'tags' => 'name')) ?>
              </span>
            <?php endforeach ?>
          </div>
        </div>
        <?= $this->render_partial("comments", array('comments' => $post->recent_comments(), 'post_id' => $post->id, 'hide' => 'true')) ?>
      </div>
    </div>
  <?php endforeach ?>

  <div id="paginator">
    <?= $this->will_paginate($this->posts) ?>
  </div>

  <?php if (!empty($this->page_uses_translations)) : ?>
    <?php $this->content_for('above_footer', function(){ ?>
      <?= $this->t(array('.translation_html', 'p' => $this->link_to('Google', 'http://translate.google.com'))) ?>
      <br>
    <?php }) ?>
  <?php endif ?>

  <script type="text/javascript">
    Post.register_resp(<?= json_encode(Post::batch_api_data($this->posts->all())) ?>);
<?php /*
    jQuery(function ($) {
      var scores = <?= json_encode($this->posts->reduce(array(), function($h,$p){ $h[$p->id] = $p->score; return $h; } )) ?>
      for (var id in scores) {
        var vote = new Vote($('#comments-for-p'+id), id);
        vote.updateWidget(Post.votes.get(id), scores[id]);
      }
    });
*/ ?>
    <?php foreach ($this->posts as $post) : ?> 
    var container = $("comments-for-p<?= $post->id ?>").down(".vote-container");
    var widget = new VoteWidget(container);
    widget.set_post_id(<?= $post->id ?>);
    <?php endforeach ?>
    Post.init_blacklisted({replace: true})
  </script>

  <?= $this->render_partial('footer') ?>
</div>
