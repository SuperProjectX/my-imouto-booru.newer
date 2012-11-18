<div id="post-list">
  <?php
    if ($this->tag_suggestions) :
      $total = count($this->tag_suggestions);
      $count = 0;
  ?>
    <div class="status-notice">
      <?= $this->t('post_meant') ?><?= implode(', ', array_map(function($x)use($total, &$count){
          $count++;
          $or = $count == $total && $total > 1 ? 'or ' : '';
          return $or.$this->tag_link($x);
        }, $this->tag_suggestions)) ?> 
    </div>
  <?php
      unset($total, $count);
    endif
  ?>

  <div class="sidebar">
    <?= $this->render_partial('search') ?>
    <?php if (current_user()->is_privileged_or_higher()) : ?>
    <div style="margin-bottom: 1em;" id="mode-box" class="advanced-editing">
      <h5><?= $this->t('post_mode') ?></h5>
      <form onsubmit="return false;" action="">
        <div>
          <select name="mode" id="mode" onchange="PostModeMenu.change()" onkeyup="PostModeMenu.change()" style="width: 13em;">
            <option value="view"><?= $this->t('post_view') ?></option>
            <option value="edit"><?= $this->t('post_edit') ?></option>
<!--            <option value="rating-s">Rate safe</option>
            <option value="rating-q">Rate questionable</option>
            <option value="rating-e">Rate explicit</option>
            <?php if (current_user()->is_privileged_or_higher()) : ?>
              <option value="lock-rating">Lock rating</option>
              <option value="lock-note">Lock notes</option>
            <?php endif ?> -->
            <?php if (current_user()->is_mod_or_higher()) : ?>
              <option value="approve"><?= $this->t('post_approve') ?></option>
            <?php endif ?>
            <option value="flag"><?= $this->t('post_flag') ?></option>
            <option value="apply-tag-script"><?= $this->t('post_script') ?></option>
            <option value="reparent-quick"><?= $this->t('post_reparent') ?></option>
            <?php if ($this->searching_pool) : ?>
              <option value="remove-from-pool"><?= $this->t('post_pool_remove') ?></option>
            <?php endif ?>
          </select>
        </div>
      </form>
    </div>

    <?= $this->render_partial('tag_script') ?>
    <?php endif ?>

    <?php if ($this->searching_pool) : ?>
      <?= $this->t('post_viewing') ?><?= link_to($this->h($this->searching_pool->pretty_name()), array('pool#show', 'id' => $this->searching_pool->id)) ?>.
    <?php endif ?>

    <?php if ($this->showing_holds_only) : ?>
      <?php if (!$this->posts->blank()) : ?>
        <div style="margin-bottom: .5em;">
          <?= $this->link_to_function($this->t('post_activate'), "Post.activate_all_posts()") ?>
        </div>
      <?php endif ?>
    <?php else: ?>
      <div id="held-posts" style="display: none; margin-bottom: .5em;"><?= $this->t('post_held_text') ?><span id="held-posts-count"></span> (<a href="#"><?= $this->t('post_held_view') ?></a>).</div>
    <?php endif ?>

    <?= $this->render_partial('blacklists') ?>
    <?= $this->render_partial('tags', array('include_tag_hover_highlight' => 'true')) ?>

    <br />

    <?php if (CONFIG()->can_see_ads(current_user())) : ?>
    <?= $this->render_partial('vertical') ?>
    <?php endif ?>
  </div>
  <div class="content">
    <?php if (!empty($this->ambiguous_tags)) : ?>
      <div class="status-notice">
        <?= $this->t('post_amb') ?><?php array_map(function($x){echo link_to(h($x), 'wiki#show', array('title' => $x));}, $ambiguous_tags) ?>
      </div>
    <?php endif ?>
     <?php if (CONFIG()->can_see_ads(current_user())) : ?>
      <?= CONFIG()->ad_code_index_bottom ?>
    <?php endif ?>

    <div id="quick-edit" style="display: none;" class="top-corner-float">
      <?= $this->form_tag('#update', function(){ ?>
        <?= $this->text_area_tag("post[tags]", "", array('size' => '60x2', 'id' => 'post_tags')) ?>
        <?= $this->submit_tag($this->t('post_update')) ?>
        <?= $this->tag('input', array('type' => 'button', 'value' => $this->t('post_cancel'), 'class' => 'cancel')) ?>
      <h4 style="float: right;"><?= $this->t('post_edit_tags') ?></h4>
      <?php }) ?>
    </div>

    <?= $this->render_partial("hover") ?>
    <?= $this->render_partial('posts', array('posts' => $this->posts)) ?>

    <div id="paginator">
      <?= $this->will_paginate($this->posts) ?>
    </div>
  </div>
</div>

<?= $this->content_for('post_cookie_javascripts', function() { ?>
<script type="text/javascript">
  post_quick_edit = new PostQuickEdit($("quick-edit"));

  PostModeMenu.init(<?= $this->searching_pool && $this->searching_pool->id ?>)
  <?php foreach ($this->preload as $post) : ?>
  Preload.preload('<?= $post->preview_url() ?>');
  <?php endforeach ?>

  var held_posts = Cookie.get("held_post_count");
  if(held_posts && held_posts > 0)
  {
    var e = $("held-posts");
    if(e)
    {
      var a = e.down("A");
      var cnt = e.down("#held-posts-count");
      cnt.update("" + held_posts + " " + (held_posts == 1? "post":"posts"));
      a.href = "/post/index?tags=holds%3Aonly%20user%3A" + Cookie.get("login") + "%20limit%3A100"
      e.show();
    }
  }
  Post.cache_sample_urls();
  new TagCompletionBox($("post_tags"));
  if($("tag-script"))
    new TagCompletionBox($("tag-script"));
</script>
<?php }) ?>

<?= $this->content_for('html_header', function() { ?>
  <?= $this->auto_discovery_link_tag_with_id('rss', array('post#piclens', 'tags' => $this->params()->tags, 'page' => $this->params()->page), array('id' => 'pl')) ?> 
  <?= $this->navigation_links($this->posts) ?> 
<?php }) ?>

<?= $this->render_partial('footer') ?>

<?php if ($this->content_for('subnavbar')) : ?>
  <!-- Align the links to the content, not the window. -->
  <div style="clear: both;">
    <div class="sidebar">&nbsp;</div>
    <div class="footer" style="clear: none;">
      <ul class="flat-list" id="subnavbar">
        <?= $this->yield('subnavbar') ?>
      </ul>
    </div>
  </div>
  <?php $this->clear_content_for('subnavbar') ?>
<?php endif ?>

