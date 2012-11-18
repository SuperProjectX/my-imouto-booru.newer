<div id="blacklisted-sidebar" style="display: none;">
  <h5>
    <?= $this->link_to_function($this->t('post_hidden_posts'), "$('blacklisted-list-box').toggle()", array('class' => 'no-focus-outline')) ?>
    <span id="blacklist-count" class="post-count"></span>
  </h5>
  <div id="blacklisted-list-box" style="display: none; margin-bottom: 1em;">
    <ul id="blacklisted-list" style="margin-bottom: 0em;">
      <li>
    </ul>

    <?= $this->form_tag("#", array('id' => 'blacklisted-tag-add', 'level' => 'member'), function(){ ?>
      <div>
        » <?= $this->text_field_tag("add-blacklist", "", array('size' => '20')) ?>
        <?= $this->link_to_function($this->t('post_blacklist_add'), "Post.blacklist_add_commit();", array('class' => 'text-button', 'style' => 'padding: 0px 4px', 'level' => 'blocked')) ?>
        <?= $this->submit_tag($this->t('post_blacklist_add'), array('style' => 'display: none;')) ?>
      </div>
      <?= $this->t('post_blacklist_text') ?>
    <?php }) ?>
  </div>

</div>

<?= $this->content_for('post_cookie_javascripts', function() { ?>
<script type="text/javascript">
  document.observe("dom:loaded", function() {
    $("blacklisted-tag-add").observe("submit", function(e) {
      if(e.stopped) return;
      e.stop();
      Post.blacklist_add_commit();
    });
  });
</script>
<?php }) ?>

