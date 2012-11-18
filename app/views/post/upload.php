<div id="post-add">
  <div id="static_notice" style="display: none;"></div>

  <?php if ($this->deleted_posts > 0) : ?>
    <div id="posts-deleted-notice" class="has-deleted-posts" style="margin-bottom: 1em;">
      <?= $this->deleted_posts == 1? $this->t('post_upload_text'):$this->t('post_upload_text2') ?><?= $this->t('post_upload_text3') ?><?= $this->deleted_posts == 1? $this->t('post_upload_text4'):$this->t('post_upload_text5') ?>
      <a href="<?= $this->url_for(array('action' => 'deleted_index', 'user_id' => current_user()->id)) ?>"><?= $this->t('post_upload_text6') ?></a>.
      (<?= $this->link_to_function($this->t('post_upload_text7'), 'Post.acknowledge_new_deleted_posts();') ?>)
    </div>
  <?php endif ?>

  <?php if (!current_user()->is_privileged_or_higher()) : ?>
    <div style="margin-bottom: 2em;">
      <h4><?= $this->t('post_upload_text8') ?></h4>
      <p><?= $this->t('post_upload_text9') ?></p>
      <ul>
        <li><?= $this->t('post_upload_text10') ?><?= $this->link_to("furry", array('wiki#show', 'title' => 'furry')) ?>, <?= $this->link_to("yaoi", array('wiki#show', 'title' => 'yaoi')) ?>, <?= $this->link_to("guro", array('wiki#show', 'title' => 'guro')) ?>, <?= $this->link_to("toon", array('wiki#show', 'title' => 'toon')) ?>,<?= $this->t('post_upload_text11') ?><?= $this->link_to("poorly drawn", array('wiki#show', 'title' => 'poorly_drawn')) ?><?= $this->t('post_upload_text12') ?></li>
        <li><?= $this->t('post_upload_text13') ?><?= $this->link_to("compression artifacts", array('wiki#show', 'title' => 'compression_artifacts')) ?></li>
        <li><?= $this->t('post_upload_text13') ?><?= $this->link_to("obnoxious watermarks", array('wiki#show', 'title' => 'watermark')) ?></li>
        <li><?= $this->link_to($this->t('post_upload_text14'), 'help#post_relationships') ?></li>
        <li><?= $this->t('post_upload_text15') ?><?= $this->link_to("tagging guidelines", 'help#tags') ?></li>
      </ul>
      <p><?= $this->t('post_upload_text16') ?><?= CONFIG()->member_post_limit - Post::count(array('conditions' => array("user_id = ? AND created_at > ?", current_user()->id, strtotime('-1 day')))) == 1 ? "post" : "posts" ?><?= $this->t('post_upload_text17') ?></p>
    </div>
  <?php endif ?>

  <?= $this->form_tag('post#create', array('level' => 'member', 'multipart' => true, 'id' => 'edit-form'), function(){ ?>
    <div id="posts">
      <?php if ($this->params()->url) : ?>
        <?= $this->tag('img', array('src' => $this->params()->url, 'alt' => $this->params()->url, 'title' => 'Preview', 'id' => 'image')) ?>
        <p id="scale"></p>
        <script type="text/javascript">
        document.observe("dom:loaded", function() {
          if ($("image").height > 400) {
            var width = $("image").width
            var height = $("image").height
            var ratio = 400.0 / height
            $("image").width = width * ratio
            $("image").height = height * ratio
            $("scale").innerHTML = "Scaled " + parseInt(100 * ratio) + "%"
          }
        })
        </script>
      <?php endif ?>

      <table class="form">
        <tfoot>
          <tr>
            <td></td>
            <td>
              <?= $this->submit_tag($this->t('post_upload'), array('tabindex' => '8', 'accesskey' => 's', 'class' => 'submit', 'style' => 'margin: 0;')) ?>
              <?= $this->submit_tag($this->t('post_cancel'), array('tabindex' => '8', 'accesskey' => 's', 'class' => 'cancel', 'style' => 'display: none; vertical-align: bottom; margin: 0;')) ?>
              <div id="progress" class="upload-progress-bar" style="display: none;">
                <div class="upload-progress-bar-fill"></div>
              </div>
              <span style="display: none;" id="post-exists"><?= $this->t('post_upload_text18') ?><a href="#" id="post-exists-link"></a></span>
              <span style="display: none;" id="post-upload-error"></span>
            </td>
          </tr>
        </tfoot>
        <tbody>
          <tr>
            <th width="15%"><label for="post_file"><?= $this->t('post_upload_file') ?></label></th>
            <td width="85%"><?= $this->file_field("post", "file", array('size' => '50', 'tabindex' => '1')) ?><span class="similar-results" style="display: none;"></span></td>
          </tr>
          <tr>
            <th>
              <label for="post_source"><?= $this->t('post_upload_source') ?></label>
              <?php if (!current_user()->is_privileged_or_higher()) : ?>
                <p><?= $this->t('post_upload_source_text') ?></p>
              <?php endif ?>
            </th>
            <td>
              <?= $this->text_field("post", "source", array('value' => $this->params()->url, 'size' => '50', 'tabindex' => '2')) ?>
              <?php if (CONFIG()->enable_artists) : ?>
                <?= $this->link_to_function($this->t('post_upload_artist'), "RelatedTags.find_artist(\$F('post_source'))") ?>
              <?php endif ?>
            </td>
          </tr>
          <tr>
            <th>
              <label for="post_tags"><?= $this->t('post_upload_tags') ?></label>
              <?php if (!current_user()->is_privileged_or_higher()) : ?>
                <p><?= $this->t('post_upload_tags_text') ?>(<?= $this->link_to($this->t('post_upload_tags_help'), array('help#tags'), array('target' => '_blank')) ?>)</p>
              <?php endif ?>
            </th>
            <td>
              <?= $this->text_area("post", "tags", array('value' => $this->params()->tags, 'size' => '60x2', 'tabindex' => '3')) ?>
              <?= $this->link_to_function($this->t('post_edit_related_tags'), "RelatedTags.find('post_tags')") ?> |
              <?= $this->link_to_function($this->t('post_edit_related_artists'), "RelatedTags.find('post_tags', 'artist')") ?> |
              <?= $this->link_to_function($this->t('post_edit_related_characters'), "RelatedTags.find('post_tags', 'char')") ?> |
              <?= $this->link_to_function($this->t('post_edit_related_copyrights'), "RelatedTags.find('post_tags', 'copyright')") ?> |
              <?= $this->link_to_function($this->t('post_upload_related_circles'), "RelatedTags.find('post_tags', 'circle')") ?>
            </td>
          </tr>
          <?php if (CONFIG()->enable_parent_posts) : ?>
            <tr>
              <th><label for="post_parent_id"><?= $this->t('post_upload_parent') ?></label></th>
              <td><?= $this->text_field("post", "parent_id", array('value' => $this->params()->parent, 'size' => '5', 'tabindex' => '4')) ?></td>
            </tr>
          <?php endif ?>
          <tr>
            <th>
              <label for="post_rating_questionable"><?= $this->t('post_upload_rating') ?></label>
              <?php if (!current_user()->is_privileged_or_higher()) : ?>
                <p><?= $this->t('post_upload_rating_text') ?>(<?= $this->link_to($this->t('post_upload_tags_help'), array('help#ratings'), array('target' => '_blank')) ?>)</p>
              <?php endif ?>
            </th>
            <td>
              <input id="post_rating_explicit" name="post[rating]" type="radio" value="e" <?php if (($this->params()->rating ?: $this->default_rating) == "e") : ?>checked="checked"<?php endif ?> tabindex="5">
              <label for="post_rating_explicit"><?= $this->t('post_upload_e') ?></label>

              <input id="post_rating_questionable" name="post[rating]" type="radio" value="q" <?php if (($this->params()->rating ?: $this->default_rating) == "q") : ?>checked="checked"<?php endif ?> tabindex="6">
              <label for="post_rating_questionable"><?= $this->t('post_upload_q') ?></label>

              <input id="post_rating_safe" name="post[rating]" type="radio" value="s" <?php if (($this->params()->rating ?: $this->default_rating) == "s") : ?>checked="checked"<?php endif ?> tabindex="7">
              <label for="post_rating_safe"><?= $this->t('post_upload_s') ?></label>
            </td>
          </tr>
          <?php if (current_user()->is_contributor_or_higher()) : ?>
            <tr>
              <th><label for="anonymous"><?= $this->t('.anonymous') ?></label></th>
              <td><?= $this->check_box_tag('anonymous', '1') ?></td>
            </tr>
          <?php endif ?>
        </tbody>
      </table>

      <div id="related"><em><?= $this->t('post_upload_related') ?></em></div>
    </div>
  <?php }) ?>

</div>

<script type="text/javascript">
  Post.observe_text_area("post_tags")
  if (Cookie.get("upload-disclaimer") == "1") {
    $("upload-disclaimer").hide()
  }

  /* Set up PostUploadForm in dom:loaded, to make sure the login handler can attach to
   * the form first. */
  document.observe("dom:loaded", function() {
    var form = $("edit-form");
    form.down("#post_file").on("change", function(e) { form.down("#post_tags").focus(); });

    if(form)
    {
      new PostUploadForm(form, $("progress"));
      new UploadSimilarSearch(form.down("#post_file"), form.down(".similar-results"));
    }
  }.bindAsEventListener());
</script>

<?= $this->content_for('post_cookie_javascripts', function() { ?>
  <script type="text/javascript">
    RelatedTags.init(Cookie.unescape(Cookie.get('my_tags')), '<?= $this->params()->ref ?: $this->params()->url ?>')
  </script>
<?php }) ?>

<?= $this->render_partial("footer") ?>
