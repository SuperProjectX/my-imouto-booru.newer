<div style="margin-bottom: 1em;">
  <?= $this->form_tag([], ['method' => 'get'], function(){ ?>
    <?= $this->text_field_tag("query", $this->params()->query) ?>
    <?= $this->submit_tag($this->t('alias_search')) ?>
    <?= $this->submit_tag($this->t('imp_search')) ?>
  <?php }) ?>
</div>

<div id="aliases">
  <?= $this->form_tag(['action' => "update"], function() { ?>
    <table width="100%" class="highlightable">
      <thead>
        <tr>
          <th width="1%"></th>
          <th width="19%"><?= $this->t('alias_alias') ?></th>
          <th width="20%"><?= $this->t('alias_to') ?></th>
          <th width="60%"><?= $this->t('alias_reason') ?></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <td colspan="4">
            <?php if (current_user()->is_mod_or_higher()) : ?>
              <?= $this->button_to_function($this->t('alias_pending'), "$$('.pending').each(function(x) {x.checked = true})") ?>
              <?= $this->submit_tag($this->t('alias_approve')) ?>
            <?php endif ?>
            <?= $this->button_to_function($this->t('alias_delete'), "$('reason-box').show(); $('reason').focus()") ?>
            <?= $this->button_to_function($this->t('alias_add'), "$('add-box').show().scrollTo(); $('tag_alias_name').focus()") ?>

            <div id="reason-box" style="display: none; margin-top: 1em;">
              <strong><?= $this->t('alias_reason2') ?></strong>
              <?= $this->text_field_tag("reason", "", ['size' => 40]) ?>
              <?= $this->submit_tag($this->t('alias_delete')) ?>
            </div>
          </td>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($this->aliases as $a) : ?>
          <tr class="<?= $this->cycle('even', 'odd') ?> <?= $a->is_pending ? 'pending-tag' : null ?>">
            <td><input type="checkbox" name="aliases[<?= $a->id ?>]" value="1" <?= $a->is_pending ? 'class="pending"' : null ?>></td>
            <td><?= $this->link_to($this->h($a->name), ['controller' => "post", 'action' => "index", 'tags' => $a->name]) ?> (<?= ($tag = Tag::find_by_name($a->name)) ? $tag->post_count : 0 ?>)</td>
            <td><?= $this->link_to($this->h($a->alias_name()), ['controller' => "post", 'action' => "index", 'tags' => $a->alias_name()]) ?> (<?= ($tag = Tag::find($a->alias_id)) ? $tag->post_count : 0 ?>)</td>
            <td><?= $this->h($a->reason) ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  <?php }) ?>
</div>

<div id="add-box" style="display: none;">
  <?= $this->form_tag(['action' => "create"], function() { ?>
    <h4><?= $this->t('alias_text') ?></h4>
    <p><?= $this->t('alias_text2') ?></p>

    <?php if (!current_user()->is_anonymous()) : ?>
      <?= $this->hidden_field_tag("tag_alias[creator_id]", current_user()->id) ?>
    <?php endif ?>

    <table>
      <tr>
        <th><label for="tag_alias_name"><?= $this->t('alias_name') ?></label></th>
        <td><?= $this->text_field('tag_alias', 'name', ['size' => 40]) ?></td>
      </tr>
      <tr>
        <th><label for="tag_alias_alias"><?= $this->t('alias_alias_to') ?></label></th>
        <td><?= $this->text_field('tag_alias', 'alias', ['size' => 40]) ?></td>
      </tr>
      <tr>
        <th><label for="tag_alias_reason"><?= $this->t('alias_reason') ?></label></th>
        <td><?= $this->text_area('tag_alias', 'reason', ['size' => "40x2"]) ?></td>
      </tr>
      <tr>
        <td colspan="2"><?= $this->submit_tag($this->t('alias_submit')) ?></td>
      </tr>
    </table>
  <?php }) ?>
</div>

<div id="paginator">
  <?= $this->will_paginate($this->aliases) ?>
</div>

<?= $this->render_partial("/tag/footer") ?>
