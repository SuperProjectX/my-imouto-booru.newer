<div class="message">
  <?php if ($this->message->to_id == current_user()->id) : ?>
    <h5 class="received"><?= $this->h($this->message->title) ?></h5>
  <?php else: ?>
    <h5 class="sent"><?= $this->h($this->message->title) ?></h5>
  <?php endif ?>

  <div style="margin-bottom: 1em;">
    <em><?= $this->t(['.sent_by_html', 'user' => $this->link_to($this->message->from->name, ['controller' => "user", 'action' => "show", 'id' => $this->message->from_id]), 'x_ago' => $this->t(['time.x_ago', 't' => $this->time_ago_in_words($this->message->created_at)])]) ?></em>
  </div>

  <div style="width: 50em;">
    <?= $this->format_text($this->message->body) ?>
  </div>
</div>
