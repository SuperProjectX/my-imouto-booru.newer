<div id="comments" style="margin-top: 1em; max-width: 800px; width: 100%;">
  <?= $this->render_partial("comment/comments", array('comments' => $this->post->comments, 'post_id' => $this->post->id, 'hide' => false)) ?>
</div>

<?php if (isset($this->page_uses_translations)) : ?>
  <?= $this->content_for('above_footer', function() { ?>
    <?= $this->t('post_comment_google') ?><a href="http://translate.google.com">Google</a>.
    <br />
  <?php }) ?>
<?php endif ?>

