<div id="note-list">
  <?= $this->render_partial("post/posts", ['posts' => $this->posts]) ?>

  <div id="paginator">
    <?= $this->will_paginate($this->posts) ?>
  </div>

  <?= $this->render_partial("footer") ?>
</div>
