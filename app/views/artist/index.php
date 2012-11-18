<div id="artist-index">
  <div id="search-form" style="margin-bottom: 1em;">
    <?= $this->form_tag(['action' => 'index'], ['method' => 'get'], function(){ ?>
      <?= $this->text_field_tag('name', $this->params()->name, ['size' => 40]) ?> <?= $this->submit_tag($this->t('artist_index_search')) ?>
      <br />
      <?= $this->select_tag('order', [[$this->t('artist_index_name') => 'name', $this->t('artist_index_date') => 'date'], ($this->params()->order ?: '')]) ?>
    <?php }) ?>
  </div>

  <?php if (!$this->artists->blank()) : ?>
    <table class="highlightable" width="100%">
      <thead>
        <tr>
          <th width="5%"></th>
          <th width="35%"><?= $this->t('artist_index_name') ?></th>
          <th width="50%"><?= $this->t('artist_index_updated_by') ?></th>
          <th width="10%"><?= $this->t('artist_index_last_modified') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->artists as $artist) : ?>
          <tr class=<?= $this->cycle('even', 'odd') ?> id="artist-<?= $artist->id ?>">
            <td>
              <?= $this->link_to_if(!$artist->alias_id, 'P', ['controller' => 'post', 'action' => 'index', 'tags' => $artist->name], ['title' => $this->t('artist_posts')]) ?>
              <?= $this->link_to('E', ['action' => 'update', 'id' => $artist->id], ['title' => $this->t('artist_show_edit')]) ?>
              <?= $this->link_to('D', ['action' => 'destroy', 'id' => $artist->id], ['title' => $this->t('artist_delete')]) ?>
            </td>
            <td>
              <?= $this->link_to($artist->name, ['action' => 'show', 'id' => $artist->id]) ?>
              <?php if ($artist->alias_id) : ?>
                &rarr; <?= $this->link_to($artist->alias_name, ['action' => 'show', 'id' => $artist->alias_id], ['title' => $this->t('.is_alias')]) ?>
              <?php endif ?>
              <?php if ($artist->group_id) : ?>
                [<?= $this->link_to($artist->group_name, ['action' => 'show', 'id' => $artist->group_id], ['title' => $this->t('.is_group')]) ?>]
              <?php endif ?>
            </td>
            <?php if ($artist->updater_id) : ?>
              <td><?= User::find_name_by_id($artist->updater_id) ?></td>
            <?php else: ?>
              <td></td>
            <?php endif ?>
            <td><?= date('M d Y, H:i', strtotime($artist->updated_at)) ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  <?php endif ?>

  <div id="paginator">
    <?= $this->will_paginate($this->artists) ?>
  </div>

  <?= $this->render_partial("footer") ?>
</div>
