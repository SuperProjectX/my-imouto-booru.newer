<div id="artist-create">
  <p><?= $this->t('artist_create') ?></p>

  <?= $this->form_tag(['action' => "create"], ['level' => 'member'], function(){ ?>
    <table class="form">
      <tr>
        <th><label for="artist_name"><?= $this->t('artist_name') ?></label></th>
        <td><?= $this->text_field('artist', 'name', ['size' => 80]) ?></td>
      </tr>
      <?php if ($this->params()->alias_id) : ?>
        <tr>
          <th><label for="artist_alias_name"><?= $this->t('artist_alias_for') ?></label></th>
          <td><?= $this->text_field('artist', 'alias_name', ['size' => 80]) ?></td>
        </tr>
      <?php endif ?>
      <tr>
        <th><label for="artist_alias_names"><?= $this->t('artist_aliases') ?></label</th>
        <td><?= $this->text_field('artist', 'alias_names', ['size' => 80, 'value' => $this->params()->jp_name]) ?></td>
      </tr>
      <tr>
        <th><label for="artist_member_names"><?= $this->t('artist_members') ?></label></th>
        <td><?= $this->text_field('artist', 'member_names', ['size' => 80]) ?></td>
      </tr>
      <tr>
        <th><label for="artist_urls"><?= $this->t('artist_urls') ?></label></th>
        <td><?= $this->text_area('artist', 'urls', ['size' => "80x6", 'class' => "no-block"]) ?></td>
      </tr>
      <tr>
        <th><label for="artist_notes"><?= $this->t('artist_notes') ?></label></th>
        <td><?= $this->text_area('artist', 'notes', ['size' => "80x6", 'class' => "no-block"]) ?></td>
      </tr>
      <tr>
        <td colspan="2"><?= $this->submit_tag($this->t('buttons.save')) ?> <?= $this->button_to_function($this->t('buttons.cancel'), "history.back()") ?></td>
      </tr>
    </table>
  <?php }) ?>
</div>

<?= $this->render_partial("footer") ?>
