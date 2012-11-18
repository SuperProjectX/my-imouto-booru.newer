<?php if ($this->forum_post->is_locked) : ?>
  <div class="status-notice">
    <p><?= $this->t('forum_locked') ?></p>
  </div>
<?php endif ?>

<div id="forum" class="response-list">
  <?php if ($this->page_number <= 1) : ?>
    <?= $this->render_partial("post", ['post' => $this->forum_post]) ?>
  <?php endif ?>

  <?php foreach ($this->children as $c) : ?>
    <?= $this->render_partial("post", ['post' => $c]) ?>
  <?php endforeach ?>
</div>

<?php if (!$this->forum_post->is_locked) : ?>
  <div style="$clear: both;">

    <div id="preview" class="response-list" style="display: none; margin: 1em 0;">
    </div>

    <div id="reply" style="display: none; $clear: both;">
      <?= $this->form_tag(['action' => "create"], ['level' => 'member'], function(){ ?>
        <?= $this->hidden_field("forum_post", "title", ['value' => ""]) ?>
        <?= $this->hidden_field("forum_post", "parent_id", ['value' => $this->forum_post->root_id()]) ?>
        <?= $this->text_area('forum_post', 'body', ['rows' => 20, 'cols' => 80, 'value' => ""]) ?>
        <?= $this->submit_tag($this->t('forum_post')) ?>
        <input name="preview" onclick="new Ajax.Updater('preview', '/forum/preview', {asynchronous:true, evalScripts:true, onSuccess:function(request){$('preview').show()}, parameters:Form.serialize(this.form)});" type="button" value="Preview"/>
      <?php }) ?>
    </div>
  </div>
<?php endif ?>

<div id="paginator">
  <?= $this->will_paginate($this->children) ?>
</div>

<script type="text/javascript">
  <?= $this->avatar_init() ?>
  InlineImage.init();
</script>

<?php $this->content_for('subnavbar', function() { ?>
  <?php if (!$this->forum_post->is_locked) : ?>
    <li><?= $this->link_to_function($this->t('forum_reply'), "Element.toggle('reply')") ?></li>
  <?php endif ?>
  <li><?= $this->link_to($this->t('forum_list'), ['action' => "index"]) ?></li>
  <li><?= $this->link_to($this->t('forum_new'), ['action' => "blank"]) ?></li>
  <?php if (!$this->forum_post->is_parent()) : ?>
    <li><?= $this->link_to($this->t('forum_parent'), ['action' => "show", 'id' => $this->forum_post->parent_id]) ?></li>
  <?php endif ?>
  <li><?= $this->link_to($this->t('forum_help'), ['controller' => "help", 'action' => "forum"]) ?></li>
<?php }) ?>
