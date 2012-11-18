<div id="pool-index">
  <div style="margin-bottom: 2em;">
    <?= $this->form_tag(['action' => "index"], ['method' => 'get'], function(){ ?>
      <?php if ($this->params()->order) : ?>
      <?= $this->hidden_field_tag("order", $this->params()->order) ?>
      <?php endif ?>
      <?= $this->text_field_tag("query", $this->params()->query, ['size' => 40]) ?>
      <?= $this->submit_tag($this->t('pool_search')) ?>
    <?php }) ?>
  </div>

  <?= $this->image_tag('blank.gif', ['id' => 'hover-thumb', 'alt' => '', 'style' => 'position: absolute; display: none; border: 2px solid #000; right: 42%;']) ?>

  <table width="100%" class="highlightable">
    <thead>
      <tr>
        <th width="60%"><?=$this->t('pool_name') ?></th>
        <th width="*"><?=$this->t('pool_creator') ?></th>
        <th width="*"><?=$this->t('pool_posts') ?></th>
        <th width="*"><?=$this->t('pool_created') ?></th>
        <th width="*"><?=$this->t('pool_updated') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->pools as $p) : ?>
        <tr class="<?= $this->cycle('even', 'odd') ?>" id="p<?= $p->id ?>">
          <td><?= $this->link_to($this->h($p->pretty_name()), ['action' => "show", 'id' => $p->id]) ?></td>
          <td><?= $this->h($p->user->pretty_name()) ?></td>
          <td><?= $p->post_count ?></td>
          <td><?= $this->t(['time.x_ago', 't' => $this->time_ago_in_words($p->created_at)]) ?></td>
          <td><?= $this->t(['time.x_ago', 't' => $this->time_ago_in_words($p->updated_at)]) ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>

<div id="paginator">
  <?= $this->will_paginate($this->pools) ?>
</div>

<?php $this->content_for('post_cookie_javascripts', function(){ ?>
<script type="text/javascript">
  var thumb = $("hover-thumb");
  <?php foreach ($this->samples as $pool_id => $post) : ?>
    Post.register(<?= $post->to_json() ?>);
    var hover_row = $("p<?= $pool_id ?>");
    var container = hover_row.up("TABLE");
    Post.init_hover_thumb(hover_row, <?= $post->id ?>, thumb, container);
  <?php endforeach ?>
  Post.init_blacklisted({replace: true});

  <?php foreach ($this->samples as $post) : ?>
    if(!Post.is_blacklisted(<?= $post->id ?>))
      Preload.preload('<?= $post->preview_url ?>');
  <?php endforeach ?>
</script>
<?php }) ?>

<?= $this->render_partial("footer") ?>
