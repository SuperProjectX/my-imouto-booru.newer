<div style="display: none;" class="post-hover-overlay" id="index-hover-overlay">
  <a href="#"><?= $this->image_tag('blank.gif', array('alt' => '')) ?></a>
</div>

<div style="display: none;" class="post-hover" id="index-hover-info">
  <div id="hover-top-line">
    <div style="float: right; margin-left: 0em;">
      <span id="hover-dimensions"></span>,
      <span id="hover-file-size"></span>
    </div>
    <div style="padding-right: 1em">
      <?= $this->t('post_hover_post') ?><span id="hover-post-id"></span>
    </div>
  </div>

  <div style="padding-bottom: 0.5em">
    <div style="float: right; margin-left: 0em;">
      <span id="hover-author"></span>
    </div>
    <div style="padding-right: 1em">
      <?= $this->t('post_hover_score') ?><span id="hover-score"></span>
      <?= $this->t('post_hover_rating') ?><span id="hover-rating"></span>
      <span id="hover-is-parent"><?= $this->t('post_hover_parent') ?></span>
      <span id="hover-is-child"><?= $this->t('post_hover_child') ?></span>
      <span id="hover-is-pending"><?= $this->t('post_hover_pending') ?></span>
      <span id="hover-not-shown"><?= $this->t('post_hover_hidden') ?></span>
    </div>
    <div>
      <span id="hover-is-flagged"><span class="flagged-text"><?= $this->t('post_hover_flagged') ?></span><?= $this->t('post_hover_by') ?><span id="hover-flagged-by"></span>: <span id="hover-flagged-reason"> <?= $this->t('post_gar') ?></span></span>
    </div>
  </div>
  <div>
    <span id="hover-tags">
      <?php
      $tags = array();
      foreach (array_unique(CONFIG()->tag_types) as $tag)
        $tags[] = Tag::type_name_from_value($tag);
      usort($tags, function($a, $b) {return ($a_order = Tag::tag_list_order($a)) > ($b_order = Tag::tag_list_order($b)) ?  $a : $b;});
      foreach ($tags as $name) :
      // CONFIG()->tag_types.values.uniq.map { |value| Tag.type_name_from_value(value) }.sort { |a,b|
        // Tag.tag_list_order(a) <=> Tag.tag_list_order(b)
      // }.each do |name|
      ?>
        <span class="tag-type-<?= $name ?>"><a id="hover-tag-<?= $name ?>"></a></span>
      <?php endforeach ?>
    </span>
  </div>
</div>

<?= $this->content_for('post_cookie_javascripts', function() { ?>
<script type="text/javascript">Post.hover_info_init();</script>
<?php }) ?>

