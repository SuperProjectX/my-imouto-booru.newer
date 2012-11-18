<div id="forum">
  <div id="search" style="margin-bottom: 1em;">
    <?= $this->form_tag(['action' => "search"], ['method' => 'get'], function(){ ?>
      <?= $this->text_field_tag("query", $this->params()->query, ['size' => 40]) ?>
      <?= $this->submit_tag($this->t('forum_search')) ?>
    <?php }) ?>
  </div>

  <table class="nowrap highlightable" width="100%">
    <thead>
      <tr>
        <th><?=$this->t('forum_title') ?></th>
        <th><?=$this->t('forum_created_by') ?></th>
        <th><?=$this->t('forum_updated_by') ?></th>
        <th><?=$this->t('forum_updated') ?></th>
        <th><?=$this->t('forum_responses') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->forum_posts as $fp) : ?>
        <tr class="<?= $this->cycle('even', 'odd') ?>">
          <td class="wrap full">
            <?php if (!current_user()->is_anonymous() && $fp->updated_at > current_user()->last_forum_topic_read_at) : ?>
              <span class="forum-topic unread-topic"><?php if ($fp->is_sticky) : ?><?=$this->t('forum_sticky') ?><?php endif ?><?= $this->link_to($this->h($fp->title), ['action' => "show", 'id' => $fp->id]) ?></span>
            <?php else: ?>
              <span class="forum-topic"><?php if ($fp->is_sticky) : ?><?=$this->t('forum_sticky') ?><?php endif ?><?= $this->link_to($this->h($fp->title), ['action' => "show", 'id' => $fp->id]) ?></span>
            <?php endif ?>

            <?php if ($fp->response_count > 30) : ?>
              <?= $this->link_to($this->t('forum_last'), ['action' => "show", 'id' => $fp->id, 'page' => ceil($fp->response_count / 30.0)], ['class' => "last-page"]) ?>
            <?php endif ?>

            <?php if ($fp->is_locked) : ?>
              <span class="locked-topic"><?= $this->t('.is_locked') ?></span>
            <?php endif ?>
          </td>
          <td><?= $this->h($fp->author()) ?></td>
          <td><?= $this->h($fp->last_updater()) ?></td>
          <td><?= $this->t(['time.x_ago', 't' => $this->time_ago_in_words($fp->updated_at)]) ?></td>
          <td><?= $fp->response_count ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>

  <div id="paginator">
    <?= $this->will_paginate($this->forum_posts) ?>
  </div>

  <?php $this->content_for('subnavbar', function(){ ?>
    <li><?= $this->link_to("New topic", ["#blank"]) ?></li>
    <?php if (!current_user()->is_anonymous()) : ?>
      <li><?= $this->link_to_function($this->t('forum_mark'), "Forum::mark_all_read()") ?></li>
    <?php endif ?>
    <li><?= $this->link_to($this->t('forum_help'), ['controller' => "help", 'action' => "forum"]) ?></li>
  <?php }) ?>

  <div id="preview" style="display: none; margin: 1em 0;">
  </div>

  <div id="reply" style="display: none;">
    <?= $this->form_tag(['action' => "create"], ['level' => 'member'], function() { ?>
      <?= $this->hidden_field("forum_post", "parent_id", ['value' => $this->params()->parent_id]) ?>
      <table>
        <tr>
          <td><label for="forum_post_title"><?=$this->t('forum_title') ?></label></td>
          <td><?= $this->text_field('forum_post', 'title', ['size' => 60]) ?></td>
        </tr>
        <tr>
          <td colspan="2"><?= $this->text_area('forum_post', 'body', ['rows' => 20, 'cols' => 80]) ?></td>
        </tr>
        <tr>
          <td colspan="2">
            <?= $this->submit_tag($this->t('forum_post')) ?>
            <input name="preview" onclick="new Ajax.Updater('preview', '<?= $this->url_for('#preview') ?>', {asynchronous:true, evalScripts:true, onSuccess:function(request){$('preview').show()}, parameters:Form.serialize(this.form)});" type="button" value="<?= $this->t('forum_preview') ?>"/>
          </td>
        </tr>
      </table>
    <?php }) ?>
  </div>
</div>
