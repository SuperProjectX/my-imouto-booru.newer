<?= $this->content_for('html_header', function() { ?>
  <?= $this->render_partial('social_meta') ?>
<?php }) ?>
<div id="post-view">
  <?php if (!$this->post) : ?>
    <h2><?= $this->t('post_empty') ?></h2>
  <?php else: ?>
    <?php if ($this->post->can_be_seen_by(current_user())) : ?>
      <script type="text/javascript">Post.register_resp(<?= json_encode(Post::batch_api_data(array($this->post))) ?>); </script>
    <?php endif ?>

    <?= $this->render_partial('post/show_partials/status_notices', array('pools' => $this->pools)) ?>

    <div class="sidebar">
      <?= $this->render_partial('search') ?>
      <?= $this->render_partial('tags') ?>
      <?= $this->render_partial('post/show_partials/statistics_panel') ?>
      <?= $this->render_partial('post/show_partials/options_panel') ?>
      <?= $this->render_partial('post/show_partials/related_posts_panel') ?>
 <br />
  <?php if (CONFIG()->can_see_ads(current_user())) : ?>
  <?= $this->render_partial('vertical') ?>
  <?php endif ?>
    </div>
    <div class="content" id="right-col">
    <?php if (CONFIG()->can_see_ads(current_user())) : ?>
      <?= $this->render_partial('horizontal') ?>
      <br />
      <br />
      <?php endif ?>
      <?= $this->render_partial('post/show_partials/image') ?>
      <?= $this->render_partial('post/show_partials/image_footer') ?>
      <?= $this->render_partial('post/show_partials/edit') ?>
      <?= $this->render_partial('post/show_partials/comments') ?>
    </div>

    <?= $this->content_for('post_cookie_javascripts', function() { ?> 
      <script type="text/javascript">
        <?php if (false) : //TODO: ($this->post->can_be_seen_by(current_user())) : ?>
          jQuery(function($) {
              Moebooru.addData(<?= json_encode(Post::batch_api_data(array($this->post))) ?>);
              Moe.trigger('vote'update_widget'');
          });
        <?php endif ?>

        RelatedTags.init(Cookie.get('my_tags'), '<?= $this->params()->url ?>')

        if (Cookie.get('resize_image') == '1') {
          Post.resize_image()
        }

        var anchored_to_comment = window.location.hash == "#comments";
        if(window.location.hash.match(/^#c[0-9]+$/))
          anchored_to_comment = true;

        if (Cookie.get('show_defaults_to_edit') == '1' && !anchored_to_comment) {
          $('comments').hide();
          $('edit').show();
        }

        <?php $browser_url = "/post/browse#".$this->post->id ?>
        <?php !empty($this->following_pool_post) && $browser_url .= "/pool:" . $this->following_pool_post->pool_id ?>
        OnKey(66, { AlwaysAllowOpera: true }, function(e) { window.location.href = <?= json_encode($browser_url) ?>; });
      </script>
    <?php }) ?>
  <?php endif ?>
</div>

<?php if (CONFIG()->app_name == "oreno.imouto") : ?>
<?= $this->render_partial('referral') ?>
<?php endif ?>

<script type="text/javascript">
  new TagCompletionBox($("post_tags"));
  if(TagCompletion)
    TagCompletion.observe_tag_changes_on_submit($("edit-form"), $("post_tags"), $("post_old_tags"));

  <?php if (CONFIG()->app_name == "oreno.imouto") : ?>
    referral_banner = new ReferralBanner($("hosting-referral"));
    referral_banner.increment_views_and_check_referral();
  <?php endif ?>
</script>
<?= $this->render_partial('footer') ?>
