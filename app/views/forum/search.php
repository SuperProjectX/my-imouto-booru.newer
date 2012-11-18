<div id="forum">
  <div style="margin-bottom: 1em;">
    <?= $this->form_tag(['action' => "search"], ['method' => 'get'], function(){ ?>
      <?= $this->text_field_tag("query", $this->params()->query, ['size' => 40]) ?>
      <?= $this->submit_tag($this->t('forum_search'))?>
    <?php }) ?>
  </div>

  <table class="highlightable">
    <thead>
      <tr>
        <th width="20%"><?=$this->t('forum_topic') ?></th>
        <th width="50%"><?=$this->t('forum_message') ?></th>
        <th width="10%"><?=$this->t('forum_author') ?></th>
        <th width="20%"><?=$this->t('forum_last_updated') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->forum_posts as $fp) : ?>
        <tr class="<?= $this->cycle('even', 'odd') ?>">
          <td><?= $this->link_to($this->h($fp->root()->title), ['action' => "show", 'id' => $fp->root_id]) ?></td>
          <td><?= $this->link_to($this->h(substr($fp->body, 0, 70)) . "...", ['action' => "show", 'id' => $fp->id]) ?></td>
          <td><?= $this->h($fp->author()) ?></td>
          <td><?= $this->t(['.last_updated_by', 't_ago' => $this->t(['time.x_ago', 't' => $this->time_ago_in_words($fp->updated_at)]), 'u' => $fp->last_updater()]) ?></td>
        <tr>
      <?php endforeach ?>
    </tbody>
  </table>

  <div id="paginator">
    <?= $this->will_paginate($this->forum_posts) ?>
  </div>
</div>
