<div>
  <h5><?= $this->t('post_related') ?></h5>
  <ul>
    <li><?= $this->link_to_if($this->post->previous_id(), $this->t('post_related_previous'), array('post#show', 'id' => $this->post->previous_id)) ?></li>
    <li><?= $this->link_to_if($this->post->next_id(), $this->t('post_related_next'), array('post#show', 'id' => $this->post->next_id)) ?></li>
    <?php if ($this->post->parent_id) : ?>
      <li><?= $this->link_to($this->t('post_related_parent'), array('post#show', 'id' => $this->post->parent_id)) ?></li>
    <?php endif ?>
    <li><?= $this->link_to($this->t('post_related_random'), 'post#random') ?></li>
    <?php if (current_user()->is_member_or_higher()) : ?>
    <?php if (!$this->post->is_deleted() || $this->post->image()) : ?>
      <li><a id="find-dupes"><?= $this->t('post_related_dupe') ?></a><?php #= link_to "Find dupes", 'post#similar', 'id' => $this->post->id, 'services' => 'local' ?></li>
      <li><a id="find-similar"><?= $this->t('post_related_sim') ?></a><?php #= link_to "Find similar", 'post#similar', 'id' => $this->post->id, 'services' => 'all' ?></li>
      <script type="text/javascript">
        $("find-dupes").href = '<?= $this->url_for(array('post#similar', 'id' => $this->post->id, 'services'=>'local')) ?>';
        $("find-similar").href = '<?= $this->url_for(array('post#similar', 'id' => $this->post->id, 'services'=>'all')) ?>';
      </script>
    <?php endif ?>
    <?php endif ?>
  </ul>
</div>
