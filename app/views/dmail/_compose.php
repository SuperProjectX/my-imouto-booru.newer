<?= $this->form_tag("#create", function(){ ?>
  <?= $this->hidden_field_tag("dmail[parent_id]", ($this->dmail->parent_id ?: $this->dmail->id), ['id' => "dmail_parent_id"]) ?>

  <table width="100%">
    <tfoot>
      <tr>
        <td></td>
        <td><?= $this->submit_tag($this->t('dmail_send')) ?> <?= $this->submit_tag($this->t('dmail_preview'), ['id' => 'dmail-preview', 'name' => 'preview', 'type' => 'button']) ?></td>
      </tr>
      <tr>
        <td></td>
        <td><div style="width: 400px" id="dmail-preview-area"></div></td>
      </tr>
    </tfoot>
    <tbody>
      <tr>
        <th><label for="dmail_to_name"><?= $this->t('dmail_to') ?></label></th>
        <td><input class="ac-user-name ui-autocomplete-input" id="dmail_to_name" name="dmail[to_name]" size="30" type="text" value="<?= $this->params()->to ?>" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true"/></td>
      </tr>
      <tr>
        <th><label for="dmail_title"><?= $this->t('dmail_title') ?></label></th>
        <td><?= $this->text_field('dmail', 'title') ?></td>
      </tr>
      <tr>
        <th><label for="dmail_body"><?=$this->t('dmail_body') ?></label></th>
        <td><?= $this->text_area('dmail', 'body', ['size' => "50x25", 'class' => "default"]) ?></td>
      </tr>
    </tbody>
  </table>
<?php }) ?>
<script type="text/javascript">
  $('dmail-preview').observe('click', function(event) {
    $('dmail-preview-area').innerHTML = '<em><?= $this->t('dmail_preview_js_text') ?></em>';
    new Ajax.Updater('dmail-preview-area', '<?= addslashes($this->url_for(['dmail#preview'])) ?>', { parameters: { body: $F('dmail_body') } });
    event.stop(); //Stop the browser from actually going to href.
  });
</script>
