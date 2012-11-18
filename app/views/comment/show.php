<?= $this->render_partial("comment/comments", array('comments' => array($this->comment), 'post_id' => $this->comment->post_id, 'hide' => false)) ?>

<div style="clear: both;">
  <p><?= $this->link_to($this->t('.return'), array("post#show", 'id' => $this->comment->post_id)) ?></p>
</div>
