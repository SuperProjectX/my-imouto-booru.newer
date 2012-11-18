<div id="edit-tag-script" style="display: none;" class="top-corner-float">
  <h5><?= $this->t('post_tag_script') ?></h5>
  <form onsubmit="return false;" action="">
    <?= $this->text_field_tag("tag-script", "", array('size' => '40', 'id' => 'tag-script')) ?>
  </form>
  <div style="margin-top: 0.25em;">
    <?= $this->link_to_function($this->t('post_tag_script_text'), 'PostModeMenu.apply_tag_script_to_all_posts()') ?>
  </div>
</div>
<?= $this->content_for('post_cookie_javascripts', function() { ?>
  <script type="text/javascript">
    TagScript.init($("tag-script"));
  </script>
<?php }) ?>
