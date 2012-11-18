<div id="edit" style="display: none;">
  <?= $this->form_tag(array('post#update', 'id' => $this->post->id), array('id' => 'edit-form', 'level' => 'member'), function() { ?>
    <?= $this->hidden_field_tag("post[old_tags]", $this->post->tags) ?>
    <table class="form">
      <tfoot>
        <tr>
          <td colspan="2"><?= $this->submit_tag($this->t('post_edit_save'), array('tabindex' => '11', 'accesskey' => 's')) ?></td>
        </tr>
      </tfoot>
      <tbody>
        <tr>
          <th width="15%">
            <label class="block" for="post_rating_questionable"><?= $this->t('post_edit_rating') ?></label>
            <?php if (!current_user()->is_privileged_or_higher()) : ?>
              <p><?= $this->t('post_edit_rating_text') ?><?= $this->link_to($this->t('post_edit_help'), 'help#ratings', array('target' => '_blank')) ?><?= $this->t('post_edit_rating_close') ?></p>
            <?php endif ?>
          </th>
          <td width="85%">
            <?php if ($this->post->is_rating_locked()) : ?>
              <?= $this->t('post_edit_rating_locked') ?>
            <?php else: ?>
              <?= $this->radio_button_tag("post[rating]", "e", $this->post->rating == "e", array('id' => 'post_rating_explicit', 'tabindex' => '1')) ?>
              <label for="post_rating_explicit"><?= $this->t('post_edit_e') ?></label>
              <?= $this->radio_button_tag("post[rating]", "q", $this->post->rating == "q", array('id' => 'post_rating_questionable', 'tabindex' => '2')) ?>
              <label for="post_rating_questionable"><?= $this->t('post_edit_q') ?></label>
              <?= $this->radio_button_tag("post[rating]", "s", $this->post->rating == "s", array('id' => 'post_rating_safe', 'tabindex' => '3')) ?>
              <label for="post_rating_safe"><?= $this->t('post_edit_s') ?></label>
            <?php endif ?>
          </td>
        </tr>
        <?php if (CONFIG()->enable_parent_posts) : ?>
          <tr>
            <th><label><?= $this->t('post_edit_parent') ?></label></th>
            <td><?= $this->text_field("post", "parent_id", array('size' => '10', 'tabindex' => '4')) ?></td>
          </tr>
        <?php endif ?>
          <tr>
            <th><label class="block" for="post_is_shown_in_index"><?= $this->t('post_edit_shown') ?></label></th>
            <td><?= $this->check_box("post", "is_shown_in_index", array('tabindex' => '7')) ?></td>
          </tr>
        <?php if (current_user()->is_privileged_or_higher()) : ?>
          <tr>
            <th><label class="block" for="post_is_note_locked"><?= $this->t('post_edit_note_locked') ?></label></th>
            <td><?= $this->check_box("post", "is_note_locked", array('tabindex' => '7')) ?></td>
          </tr>
          <tr>
            <th><label class="block" for="post_is_rating_locked"><?= $this->t('post_edit_rating_locked') ?></label></th>
            <td><?= $this->check_box("post", "is_rating_locked", array('tabindex' => '8')) ?></td>
          </tr>
        <?php endif ?>
        <tr>
          <th><label class="block" for="post_source"><?= $this->t('post_edit_source') ?></label></th>
          <td><?= $this->text_field("post", "source", array('size' => '40', 'tabindex' => '9')) ?></td>
        </tr>
          <tr>
            <th>
              <label class="block" for="post_tags"><?= $this->t('post_edit_tags') ?></label>
              <?php if (!current_user()->is_privileged_or_higher()) : ?>
                <p><?= $this->t('post_edit_tags_text') ?><?= $this->link_to($this->t('post_edit_help'), array('help#tags'), array('target' => '_blank')) ?><?= $this->t('post_edit_tags_close') ?></p>
              <?php endif ?>
            </th>
            <td>
              <?= $this->text_area("post", "tags", array('disabled' => !$this->post->can_be_seen_by(current_user()), 'size' => '50x4', 'tabindex' => '10')) ?>
            <?php if ($this->post->can_be_seen_by(current_user())) : ?>
              <?= $this->link_to_function($this->t('post_edit_related_tags'), "RelatedTags.find('post_tags')") ?> |
              <?= $this->link_to_function($this->t('post_edit_related_artists'), "RelatedTags.find('post_tags', 'artist')") ?> |
              <?= $this->link_to_function($this->t('post_edit_related_characters'), "RelatedTags.find('post_tags', 'char')") ?> |
              <?= $this->link_to_function($this->t('post_edit_related_copyrights'), "RelatedTags.find('post_tags', 'copyright')") ?>
              <?php if (CONFIG()->enable_artists) : ?>
              | <?= $this->link_to_function($this->t('post_edit_find_artists'), "RelatedTags.find_artist(\$F('post_source'))") ?>
              <?php endif ?>
            <?php endif ?>
            </td>
          </tr>
      </tbody>
    </table>
    <div>
      <h5><?= $this->t('post_edit_related_tags2') ?></h5>
      <div style="margin-bottom: 1em;" id="related"><em><?= $this->t('post_edit_related_none') ?></em></div>
    </div>
  <?php }) ?>
</div>
