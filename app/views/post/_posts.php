<div>
  <?php if ($this->posts->blank()) : ?>
    <p><?= $this->t('post_empty') ?></p>
  <?php else: ?>
    <ul id="post-list-posts">
    <?php $this->posts->each(function($post){ ?>
      <?= $this->print_preview($post, array('similarity' => /*isset($this->similar) && isset($this->similar['similarity']['$post']) ? $this->similar['similarity']['$post'] : */ null, 'blacklisting' => 'true', 'disable_jpeg_direct_links' => isset($this->similar))) ?>
    <?php })?>
    </ul>

    <?php # Make sure this is done early, as lots of other scripts depend on this registration. ?>
    <?= $this->content_for('post_cookie_javascripts', function() { ?>
    <script type="text/javascript">
      <?php if ($this->posts) : ?> 
        Post.register_tags(<?= json_encode(Tag::batch_get_tag_types_for_posts($this->posts)) ?>);
      <?php endif ?> 
      <?php $this->posts->each(function($post){ ?>
        <?php if (!$post instanceof ExternalPost) : ?> 
          Post.register(<?= $post->to_json() ?>)
        <?php endif ?> 
      <?php }) ?> 
    </script>
    <?php }, true) ?> 
  <?php endif ?>

<?= $this->content_for('post_cookie_javascripts', function() { ?>
<script type="text/javascript">
  var blacklist_options = {};
  <?php if (isset($this->search_id)) : ?>
    blacklist_options.exclude = <?= json_encode($this->search_id) ?>;
  <?php endif ?>
  Post.init_blacklisted(blacklist_options)

  Post.init_post_list();
</script>
<?php }) ?>
</div>
