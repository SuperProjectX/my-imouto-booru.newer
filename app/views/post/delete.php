<h4><?= $this->t('post_delete') ?></h4>

<?php if (CONFIG()->can_see_post(current_user(), $this->post)) : ?>
  <?= $this->image_tag($this->post->preview_url()) ?>
<?php endif ?>

<?= $this->form_tag(array('action' => 'destroy'), function(){ ?>
  <?= $this->hidden_field_tag("id", $this->params()->id) ?>
  <label><?= $this->t('post_deleted_reason') ?></label> <?= $this->text_field_tag("reason", CONFIG()->default_post_delete_reason) ?>
  <?php if ($this->post->is_deleted()) : ?>
  <?= $this->hidden_field_tag("destroy", "1") ?>
  <?php elseif (CONFIG()->allow_destroy_completely) : ?>
  <input type="hidden" name="destroy" value="0" />
  <label for="post_destroy">Destroy completely</label> <input id="post_destroy" type="checkbox" name="destroy" value="1"<?php if (CONFIG()->default_to_destroy_completely) : ?> checked="checked"<?php endif ?> /><br />
  <?php endif ?>
  <?= $this->submit_tag($this->post->is_deleted() ? $this->t('post_delete_perma'):$this->t('post_delete_delete')) ?> <?= $this->submit_tag($this->t('post_delete_cancel')) ?>
<?php }) ?>

<div class="deleting-post">
<?php if (!$this->post->is_deleted()) : ?>
    <br>
    <p>
    <?php if ($this->post_parent) : ?>
      <?= $this->t('post_delete_parent_text') ?><p>
    <?php if (CONFIG()->can_see_post(current_user(), $this->post_parent)) : ?>
      <ul id="post-list-posts"> <?= $this->print_preview($this->post_parent, array('hide_directlink' => 'true')) ?> </ul>
    <?php else: ?>
      <?= $this->t('post_delete_access') ?>
    <?php endif ?>

    <?php else: ?>
      <?= $this->t('post_delete_no_parent_text') ?><p>
    <?php endif ?>
<?php else: ?>
  <?= $this->t('post_delete_perma_text') ?>
<?php endif ?>
</div>

<?= $this->render_partial("footer") ?>

<script type="text/javascript">$("reason").focus();</script>
