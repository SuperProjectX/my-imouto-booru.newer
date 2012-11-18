<div class="comment avatar-container">
  <div class="author">
    <h6 class="author"><?= $this->link_to($this->h($this->post->author()), ['controller' => "user", 'action' => "show", 'id' => $this->post->creator_id]) ?></h6>
    <span class="date"><?= $this->link_to($this->t(['time.x_ago', 't' => $this->time_ago_in_words($this->post->created_at)]), ['action' => "show", 'id' => $this->post->id]) ?></span>
    <?php if ($this->post->creator->has_avatar()) : ?>
      <div class="forum-avatar-container"> <?= $this->avatar($this->post->creator, $this->post->id) ?> </div>
    <?php endif ?>
  </div>
  <div class="content">
    <?php if ($this->post->is_parent) : ?>
      <h6><?= $this->h($this->post->title) ?></h6>
    <?php else: ?>
      <h6 class="response-title"><?= $this->h($this->post->title) ?></h6>
    <?php endif ?>
    <div class="body">
      <?= $this->format_inlines($this->format_text($this->post->body), $this->post->id) ?>
    </div>
    <?php if (!isset($this->preview)) : ?>
    <div class="$this->post-footer" style="clear: left;">
      <ul class="flat-list pipe-list">
      <?php if (current_user()->has_permission($this->post, 'creator_id')) : ?>
        <li> <?= $this->link_to($this->t('forum_edit'), ['action' => "edit", 'id' => $this->post->id]) ?>
        <li> <?= $this->link_to($this->t('forum_delete'), ["#destroy", 'id' => $this->post->id], ['confirm' => $this->t('forum_delete_confirm'), 'method' => 'post']) ?>
      <?php endif ?>
      <?php if ($this->post->is_parent && current_user()->is_mod_or_higher()) : ?>
        <?php if ($this->post->is_sticky) : ?>
          <li> <?= $this->link_to($this->t('forum_unstick'), ['action' => "unstick", 'id' => $this->post->id], ['method' => 'post']) ?>
        <?php else: ?>
          <li> <?= $this->link_to($this->t('forum_stick'), ['action' => "stick", 'id' => $this->post->id], ['method' => 'post']) ?>
        <?php endif ?>
        <?php if ($this->post->is_locked) : ?>
          <li> <?= $this->link_to($this->t('forum_unlock'), ['action' => "unlock", 'id' => $this->post->id], ['method' => 'post']) ?>
        <?php else: ?>
          <li> <?= $this->link_to($this->t('forum_lock'), ['action' => "lock", 'id' => $this->post->id], ['method' => 'post']) ?>
        <?php endif ?>
      <?php endif ?>
      <li> <?= $this->link_to_function($this->t('forum_quote'), "Forum.quote({$this->post->id})") ?>
      </ul>
    </div>
    <?php endif ?>
  </div>
</div>
