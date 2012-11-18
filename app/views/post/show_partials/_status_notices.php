<?php if ($this->post->is_flagged()) : ?>
  <div class="status-notice">
    <?= $this->t('notice') ?><?= $this->h($this->post->flag_detail->author()) ?>. Reason: <?= $this->h($this->post->flag_detail->reason) ?>
    <?php if (current_user()->is_mod_or_higher() or ($this->post->flag_detail && $this->post->flag_detail->user_id == current_user()->id)) : ?>
    (<?= $this->link_to_function($this->t('notice2'), 'Post.unflag('.$this->post->id.', function() { window.location.reload(); })') ?>)
    <?php endif ?>
    <?php if (current_user()->is_janitor_or_higher()) : ?>
      (<?= $this->link_to_function($this->t('notice3'), 'Post.prompt_to_delete('.$this->post->id.');') ?></li>)
    <?php endif ?>
  </div>
<?php elseif ($this->post->is_pending()) : ?>
  <div class="status-notice" id="pending-notice">
    <?= $this->t('notice4') ?>
    <?php if ($this->post->flag_detail) : ?>
      <?= $this->t('notice5') ?><?= $this->h($this->post->flag_detail->reason()) ?>
    <?php endif ?>
    <?php if (current_user()->is_janitor_or_higher()) : ?>
      (<?= $this->link_to_function($this->t('notice6'), "if (confirm('".$this->t('notice_text')."')) {Post.approve(".$this->post->id."))") ?></li>)
      (<?= $this->link_to_function($this->t('notice7'), "Post.prompt_to_delete(".$this->post->id.");") ?></li>)
    <?php endif ?>
  </div>
<?php elseif ($this->post->is_deleted()) : ?>
  <div class="status-notice">
    <?= $this->t('notice8') ?>
    <?php if ($this->post->flag_detail) : ?>
      <?php if (current_user()->is_mod_or_higher()) : ?>
        <?= $this->t('notice9') ?><?= $this->link_to($this->h($this->post->flag_detail->author), array('user#show', 'id' => $this->post->flag_detail->user_id)) ?>
      <?php endif ?>

      <?= $this->t('notice10') ?><?= $this->h($this->post->flag_detail->reason) ?>. MD5: <?= $this->post->md5 ?>
    <?php endif ?>
  </div>
<?php endif ?>

<?php if ($this->post->is_held) : ?>
  <div class="status-notice" id="held-notice">
    <?= $this->t('notice11') ?>
    <?php if (current_user()->has_permission($this->post)) : ?>
      (<?= $this->link_to_function($this->t('notice12'), 'Post.activate_post('.$this->post->id.');') ?>)
    <?php endif ?>
  </div>
<?php endif ?>

<?php if (!$this->post->is_deleted() && $this->post->use_sample(current_user()) && $this->post->can_be_seen_by(current_user()) && !isset($this->post->parsed_cached_tags['dakimakura'])) : ?>
  <div class="status-notice" style="display: none;" id="resized_notice">
    <?= $this->t('notice13') ?><?= $this->link_to_function($this->t('notice14'), 'Post.highres()') ?><?= $this->t('notice15') ?>
    <!--
    <?php if (!current_user()->is_anonymous() || !CONFIG()->force_image_samples) : ?>
      <?= $this->link_to_function($this->t('notice16'), 'User.disable_samples()') ?>.
    <?php endif ?>
    -->
    <?= $this->link_to_function($this->t('notice17'), "$('resized_notice').hide(); Cookie.put('hide_resized_notice', '1')") ?>.
    <script type="text/javascript">
      if (Cookie.get("hide_resized_notice") != "1") {
        $("resized_notice").show()
      }
    </script>
  </div>
  <div class="status-notice" style="display: none;" id="samples_disabled">
    <?= $this->t('notice18') ?>
  </div>
<?php endif ?>

<?php if (CONFIG()->enable_parent_posts) : ?>
  <?php if ($this->post->parent_id) : ?>
    <div class="status-notice">
      <?= $this->t('notice19') ?><?= $this->link_to($this->t('notice20'), array('#show', 'id' => $this->post->parent_id)) ?><?php
      ?><span class="advanced-editing"> (<?= $this->link_to_function($this->t('notice21'), 'Post.reparent_post('.$this->post->id.', '.$this->post->parent_id.', '.($this->post->get_parent()->parent_id ? "true":"false").')') ?>)</span>.
    </div>
  <?php endif ?>

  <?php if ($this->post->has_children) : ?>
    <?php $children = $this->post->children; $s = $this; ?>
    <div class="status-notice">
      <?= $this->t('notice22') ?><?= $this->link_to(($children->size() == 1? $this->t('notice23'):$this->t('notice24')), array('#index', 'tags' => 'parent:'.$this->post->id)) ?> (post #<?=
        implode(', ', array_map(function($child){return $this->link_to($child->id, array('#show', 'id' => $child->id));}, $children->all())) ?>).
    </div>
  <?php endif ?>
<?php endif ?>

<?php foreach ($this->pools as $pool) : ?>
  <?= $this->render_partial("post/show_partials/pool", array('pool' => $pool, 'pool_post' => PoolPost::find_first(array('conditions' => array("pool_id = ? AND post_id = ?", $pool->id, $this->post->id))))) ?>
<?php endforeach ?>
