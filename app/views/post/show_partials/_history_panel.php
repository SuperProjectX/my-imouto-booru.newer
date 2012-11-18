<div>
  <h5><?= $this->t('post_history') ?></h5>
  <ul>
    <li><?= link_to $this->t('post_history_tags'), 'history#index', 'search' => 'post:#array($this->post.id)' ?></li>
    <li><?= link_to $this->t('post_history_notes'), 'note#history', 'post_id' => $this->post.id ?></li>
  </ul>
</div>
