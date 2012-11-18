<div>
  <?= $this->form_tag(array('action' => 'index'), array('method' => 'get'), function(){ ?>
    <table class="form">
      <tbody>
        <tr>
          <th width="15%">
            <label for="name"><?= $this->t('tag_name') ?></label>
            <p><?= $this->t('tag_wild') ?></p>
          </th>
          <td width="85%"><?= $this->text_field_tag("name", $this->params()->name, array('size' => '40')) ?></td>
        </tr>
        <tr>
          <th><label for="type"><?= $this->t('tag_type') ?></label></th>
          <td><?= $this->select_tag('type', array(array_merge(array('Any' => 'any'), array_unique(CONFIG()->tag_types)), $this->params()->type)) ?></td>
        </tr>
        <tr>
          <th><label for="order"><?= $this->t('tag_order') ?></label></th>
          <td><?= $this->select_tag('order', array(array('Name' => 'name', 'Count' => 'count', 'Date' => 'date'), $this->params()->order)) ?></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td><?= $this->submit_tag($this->t('tag_search')) ?></td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  <?php }) ?>
</div>

<table width="100%" class="highlightable">
  <thead>
    <tr>
      <th width="60px"><?= $this->t('tag_posts') ?></th>
      <th><?= $this->t('tag_name') ?></th>
      <th width="<?= $this->can_delete_tags ? '180' : '200' ?>px"><?= $this->t('tag_type') ?></th>
      <th width="<?= $this->can_delete_tags ? '140' : '120' ?>px" colspan="2"><?= $this->t('tag_action') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($this->tags as $tag) : ?>
      <tr class="<?= $this->cycle('even', 'odd') ?>">
        <td align="right"><?= $tag->post_count ?></td>
        <td class="tag-type-<?= $tag->type_name ?>">
          <?= $this->link_to('?', array('controller' => 'wiki', 'action' => 'show', 'title' => $tag->name)) ?>
          <?= $this->link_to($tag->name, array('controller' => 'post', 'action' => 'index', 'tags' => $tag->name)) ?>
        </td>
        <td><?= $tag->type_name . ($tag->is_ambiguous ? ", ambiguous" : "") ?></td>
        <td width="<?= $this->can_delete_tags ? '80' : '60' ?>px">
            <?= $this->link_to($this->t('tag_edit'), array('action' => 'edit', 'id' => $tag->id)) ?>
            <?php if ($this->can_delete_tags) : ?>
            (<?= $this->link_to('d', array_merge(array('#delete', 'id' => $tag->id), $this->params()->get()), array('title' => 'Delete tag')) ?>)
            <?php endif ?>
        </td>
        <td width="60px"><?= $this->link_to($this->t('tag_history'), array('controller' => 'history', 'search' => 'tag:'.$tag->id)) ?></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>

<div id="paginator">
  <?= $this->will_paginate($this->tags) ?>
</div>

<?= $this->render_partial('footer') ?>
