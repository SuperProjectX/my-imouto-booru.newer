<div style="margin-bottom: 1em;">
  <?= $this->form_tag(['action' => "index"], ['method' => 'get'], function(){ ?>
    <?= $this->text_field_tag("query", $this->params()->query) ?>
    <?= $this->submit_tag($this->t('imp_search')) ?>
    <?= $this->submit_tag($this->t('alias_search')) ?>
  <?php }) ?>
</div>

<?= $this->form_tag(['action' => "update"], function() { ?>
  <table class="highlightable" width="100%">
    <thead>
      <tr>
        <th width="1%"></th>
        <th width="19%"><?= $this->t('imp_pre') ?></th>
        <th width="20%"><?= $this->t('imp_con') ?></th>
        <th width="60%"><?= $this->t('alias_reason') ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <td colspan="4">
          <?php if (current_user()->is_mod_or_higher()) : ?>
            <?= $this->button_to_function("Select pending", "$$('.pending').each(function(x) {x.checked = true})") ?>
            <?= $this->submit_tag($this->t('alias_approve')) ?>
          <?php endif ?>
          <?= $this->button_to_function($this->t('alias_delete'), "$('reason-box').show(); $('reason').focus()") ?>
          <?= $this->button_to_function($this->t('alias_add'), "$('add-box').show().scrollTo(); $('tag_implication_predicate').focus()") ?>

          <div id="reason-box" style="display: none; margin-top: 1em;">
            <strong><?= $this->t('alias_reason2') ?></strong>
            <?= $this->text_field_tag("reason", "", ['size' => 40]) ?>
            <?= $this->submit_tag($this->t('alias_delete')) ?>
          </div>
        </td>
      </tr>
    </tfoot>
    <tbody>
      <?php foreach ($this->implications as $i) : ?>
        <tr class="<?= $this->cycle('even', 'odd') ?> <?= $i->is_pending ? 'pending-tag' : null ?>">
          <td><input type="checkbox" value="1" name="implications[<?= $i->id ?>]" <?= $i->is_pending ? 'class="pending"' : null ?>></td>
          <td><?= $this->link_to($this->h($i->predicate->name), ['controller' => "post", 'action' => "index", 'tags' => $i->predicate->name]) ?> (<?= $i->predicate->post_count ?>)</td>
          <td><?= $this->link_to($this->h($i->consequent->name), ['controller' => "post", 'action' => "index", 'tags' => $i->consequent->name]) ?> (<?= $i->consequent->post_count ?>)</td>
          <td><?= $this->h($i->reason) ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
<?php }) ?>

<div id="add-box" style="display: none;">
  <?= $this->form_tag(['action' => "create"], function() { ?>
    <h4><?= $this->t('imp_text') ?></h4>
    <p><?= $this->t('imp_text2') ?></p>
    <p><?= $this->t('imp_text3') ?></p>
    <?php if (!current_user()->is_anonymous()) : ?>
      <?= $this->hidden_field_tag("tag_implication[creator_id]", current_user()->id) ?>
    <?php endif ?>

    <table>
      <tr>
        <th><label for="tag_implication_predicate"><?= $this->t('imp_pre') ?></label></th>
        <td><?= $this->text_field('tag_implication', 'predicate', ['size' => 40]) ?></td>
      </tr>
      <tr>
        <th><label for="tag_implication_consequent"><?= $this->t('imp_con') ?></label></th>
        <td><?= $this->text_field('tag_implication', 'consequent', ['size' => 40]) ?></td>
      </tr>
      <tr>
        <th><label for="tag_implication_reason"><?= $this->t('alias_reason') ?></label></th>
        <td><?= $this->text_area('tag_implication', 'reason', ['size' => "40x2"]) ?></td>
      </tr>
      <tr>
        <td colspan="2"><?= $this->submit_tag($this->t('alias_submit')) ?></td>
      </tr>
    </table>
  <?php }) ?>
</div>

<div id="paginator">
  <?= $this->will_paginate($this->implications) ?>
</div>

<?= $this->render_partial("tag/footer") ?>
