<div id="post-view">
  <div class="sidebar">
    <div>
      <h5><?= $this->t('post_related_posts') ?></h5>
      <ul>
        <li><?= $this->link_to($this->t('post_previous'), array('post#show', 'id' => $this->params()->id - 1)) ?></li>
        <li><?= $this->link_to($this->t('post_next'), array('post#show', 'id' => $this->params()->id + 1)) ?></li>
        <li><?= $this->link_to($this->t('post_random'), 'post#random') ?></li>
      </ul>
    </div>
  </div>
  <div>
    <p><?= $this->t('post_404') ?></p>
  </div>
</div>
