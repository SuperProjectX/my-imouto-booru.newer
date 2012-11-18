<?= $this->form_tag([], ['onsubmit' => "return confirm('{$this->t('tag_js')}')"], function(){ ?>
  <?= $this->text_field_tag("start", $this->params()->source, ['size' => 60]) ?>
  <?= $this->text_field_tag("result", $this->params()->name, ['size' => 60]) ?>
  <?= $this->button_to_function($this->t('tag_js_preview'), "$('preview').innerHTML = '{$this->t('tag_js_preview_txt')}'; new Ajax.Updater('preview', '/tag/edit_preview', {method: 'get', parameters: 'tags=' + \$F('start')})") ?><?= $this->submit_tag($this->t('tag_save')) ?>
<?php }) ?>

<?= $this->render_partial("footer") ?>

<div id="preview">
</div>
