<div id="mail-show" class="mail">
  <div id="previous-messages" style="display: none;">
  </div>

  <?= $this->render_partial("message", ['message' => $this->dmail]) ?>

  <div style="width: 50em; display: none;" id="response">
    <?= $this->render_partial("compose", ['from_id' => current_user()->id]) ?>
  </div>

  <?php $this->content_for('footer', function(){ ?>
    <li><?= $this->link_to_function("Show conversation", "Dmail.expand(".($this->dmail->parent_id ?: $this->dmail->id).", ".$this->dmail->id.")") ?></li>
    <?php if ($this->dmail->to_id == $this->current_user->id) : ?>
      <li><?= $this->link_to_function("Respond", "Dmail.respond('".$this->dmail->from->name."')") ?></li>
    <?php endif ?>
  <?php }) ?>

  <?= $this->render_partial("footer") ?>
</div>
