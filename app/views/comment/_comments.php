<div class="response-list">
  <?php foreach ($this->comments as $c) : ?>
    <?= $this->render_partial("comment/comment", array('comment' => $c)) ?>
  <?php endforeach ?>
</div>

<div style="clear: both;">
  <?php if ($this->hide) : ?>
    <?= $this->content_tag("h6", $this->link_to_function($this->t('comment_reply'), "Comment.show_reply_form(".$this->post_id.");"), array('id' => 'respond-link-'.$this->post_id)) ?>
  <?php endif ?>
  
  <div id="reply-<?= $this->post_id ?>" style="<?= $this->hide ? "display: none;" : null ?>">
    <?= $this->form_tag('comment#create', array('level' => 'member'), function() { ?>
      <?= $this->hidden_field_tag("comment[post_id]", $this->post_id, array('id' => 'comment_post_id_'.$this->post_id)) ?>
      <?= $this->text_area("comment", "body", array('rows' => '7', 'id' => 'reply-text-'.$this->post_id, 'style' => 'width: 98%; margin-bottom: 2px;')) ?>
      <?= $this->submit_tag($this->t('comment_post')) ?>
      <!-- <?= $this->submit_tag($this->t('comment_bump')) ?> -->
    <?php }) ?>
<!--    <p style="margin-top: 1em; font-style: italic;">[spoiler]Hide spoiler text like this[/spoiler] (<?= $this->link_to($this->t('.more'), 'help#comments') ?>)</p> -->
  </div>
</div>

<script type="text/javascript">
  <?= $this->avatar_init() ?>
  InlineImage.init();
</script>

