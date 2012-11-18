<?php if (empty($this->user->tag_subscriptions)) : ?>
  None
<?php else: ?>
  <?= $this->tag_subscription_listing($user) ?>
<?php endif ?>
  
<?php if (current_user()->id == $this->user->id) : ?>
  (<a href="/tag_subscription">edit</a>)
<?php endif ?>
