<div id="pool-show">
  <h4><?= $this->t('pool_pool2') ?><?= $this->h($this->pool->pretty_name()) ?></h4>
  <?php if ($this->pool->description) : ?>
    <div style="margin-bottom: 2em;"><?= $this->format_text($this->pool->description) ?></div>
  <?php endif ?>
  <div style="margin-top: 1em;">
  <ul id="post-list-posts">
    <?php foreach($this->posts as $post) : ?>
      <?php?><?= $this->print_preview($post, ['onclick' => "return remove_post_confirm(".$post->id.", ".$this->pool->id.")",
                             'user' => current_user(), 'display' => $this->browse_mode == 1? 'large' : 'block', 'hide_directlink' => $this->browse_mode == 1]) ?>
    <?php endforeach ?>
  </ul>
  </div>
</div>
<script type="text/javascript">
  function remove_post_confirm(post_id, pool_id) {
    if (!$("del-mode") || !$("del-mode").checked) {
      return true
    }

    Pool.remove_post(post_id, pool_id)
    return false
  }

  Post.register_resp(<?= json_encode(Post::batch_api_data($this->posts->all())) ?>);
</script>
<?= $this->render_partial("post/hover") ?>

<div id="paginator">
  <?= $this->will_paginate($this->posts, ['class' => "no-browser-link"]) ?>

  <div style="display: none;" id="info"><?= $this->t('pool_delete_mode_text') ?></div>
</div>

<?php $this->content_for('footer', function(){ ?>
  <?php if (CONFIG()->pool_zips) : ?>
    <?php $zip_params = [] ?>
    <?php $has_jpeg = CONFIG()->jpeg_enable && $this->pool->has_jpeg_zip($zip_params) ?>
    <?php if ($has_jpeg) : ?>
      <li><?= $this->link_to_pool_zip($this->t('pool_jpeg'), $this->pool, array_merge($zip_params, ['jpeg' => true])) ?></li>
    <?php endif ?>
    <?php $li_class = $has_jpeg ? "advanced-editing":"" ?>
    <li class="<?= $li_class ?>"><?= $this->link_to_pool_zip($this->t('pool_png'), $this->pool, $zip_params, ['has_jpeg' => $has_jpeg]) ?></li>
  <?php endif ?>
  <li><?= $this->link_to($this->t('pool_index'), ['controller' => "post", 'action' => "index", 'tags' => "pool:".$this->pool->id]) ?> </li>
  <?php if (!current_user()->is_anonymous()) : ?>
  <li><?= $this->link_to_function($this->t('pool_toggle'), "User.set_pool_browse_mode(".(1-$this->browse_mode).");") ?></li>
  <?php endif ?>
  <?php if (current_user()->has_permission($this->pool)) : ?>
    <li><?= $this->link_to($this->t('pool_edit'), ['action' => "update", 'id' => $this->params()->id]) ?></li>
    <li><?= $this->link_to($this->t('pool_delete'), ['action' => "destroy", 'id' => $this->params()->id]) ?></li>
  <?php endif ?>
<?php }) ?>

<?php $this->content_for('footer_final', function(){ ?>
  <br>
  <?php if (current_user()->can_change($this->pool, 'posts')) : ?>
    <li><?= $this->link_to($this->t('pool_show_order'), ['action' => "order", 'id' => $this->params()->id]) ?></li>
    <?php if (current_user()->is_contributor_or_higher) : ?>
      <li><?= $this->link_to($this->t('pool_show_copy'), ['action' => "copy", 'id' => $this->params()->id]) ?></li>
      <li><?= $this->link_to($this->t('pool_show_transfer'), ['action' => "transfer_metadata", 'to' => $this->params()->id]) ?></li>
    <?php endif ?>
  <?php endif ?>
  <li><?= $this->link_to($this->t('pool_show_history'), ['controller' => "history", 'action' => "index", 'search' => "pool:".$this->params()->id]) ?></li>
  <?php if (current_user()->can_change($this->pool, 'posts')) : ?>
  <li class="advanced-editing del-mode">
    <input type="checkbox" id="del-mode" onclick="Element::toggle('info')">
    <label for="del-mode"><?= $this->t('pool_delete_mode') ?></label>
  </li>
  <?php endif ?>
<?php }) ?>

<?= $this->render_partial("footer") ?>
