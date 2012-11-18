<div class="status-notice" id="pool<?= $this->pool->id ?>">
  <div style="display: inline;">
    <p>
      <?php if ($this->pool_post->prev_post_id) : ?>
        <?= $this->link_to($this->t('post_pool_previous'), array('#show', 'id' => $this->pool_post->prev_post_id, 'pool_id' => $this->pool_post->pool_id)) ?>
      <?php endif ?>
      <?php if ($this->pool_post->next_post_id) : ?>
        <?= $this->link_to($this->t('post_pool_next'), array('#show', 'id' => $this->pool_post->next_post_id, 'pool_id' => $this->pool_post->pool_id)) ?>
      <?php endif ?>
      <?= $this->t('post_pool_text') ?><span id="pool-seq-<?= $this->pool_post->pool_id ?>"><?= $this->h($this->pool_post->pretty_sequence) ?></span>
      <?= $this->t('post_pool_text2') ?><?= $this->link_to($this->h($this->pool->pretty_name()), array('pool#show', 'id' => $this->pool->id)) ?><?= $this->t('post_pool_text3') ?>
      <?php $this->pooled_post_id = $this->post->id ?>

    <?php if (current_user()->can_change($this->pool_post, 'active')) : ?>
      <span class="advanced-editing">
        (<?= $this->link_to_function($this->t('post_pool_remove'), "if(confirm('".$this->t('post_pool_remove_confirm').' '.$this->pool->pretty_name().")) Pool.remove_post(".$this->post->id.", ".$this->pool->id.")");
        ?>, <?= $this->link_to_function($this->t('post_pool_change'), "Pool.change_sequence(".$this->post->id.", ".$this->pool_post->pool_id.", ".json_encode($this->pool_post->sequence).")");
        ?><?php
          if ($this->post->parent_id)
            echo $this->link_to_function($this->t('post_pool_xfer'), "if(confirm('".$this->t('post_pool_remove_confirm')." ".$this->pool->pretty_name().$this->t('post_pool_xfer_confirm')."')) Pool.transfer_post(".$this->post->id.", ".$this->post->parent_id.", ".$this->pool->id.", ".json_encode($this->pool_post->sequence).")");
        ?>)
      </span>
    <?php endif ?>
    </p>
  </div>
</div>
