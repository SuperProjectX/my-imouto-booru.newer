<div id="preview" style="display: none; margin: 1em 0; width: 60em;">
</div>

<?= $this->form_tag("#update", function(){ ?>
  <?= $this->hidden_field_tag("id", $this->params()->id) ?>
  <table>
    <tr><td><label for="forum_post_title"><?= $this->t('forum_title') ?></label></td><td><?= $this->text_field('forum_post', 'title', ['size' => 60]) ?></td></tr>
    <tr><td colspan="2"><?= $this->text_area('forum_post', 'body', ['rows' => 20, 'cols' => 80]) ?></td></tr>
    <tr><td colspan="2">
      <?= $this->submit_tag($this->t('forum_post')) ?>
      <input name="preview" onclick="new Ajax.Updater('preview', '<?= $this->url_for('#preview') ?>', {asynchronous:true, evalScripts:true, onSuccess:function(request){$('preview').show()}, parameters:Form.serialize(this.form)});" type="button" value="<?= $this->t('forum_preview') ?>"/>
    </td></tr>
  </table>
<?php }) ?>
