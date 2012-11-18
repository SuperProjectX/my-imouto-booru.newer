<h2><?= $this->t('forum_new_text') ?></h2>

<div style="margin: 1em 0;">
  <div id="preview" class="response-list" style="display: none;">
  </div>

  <div id="reply" style="clear: both;">
    <?= $this->form_tag(['action' => "create"], function(){ ?>
      <?= $this->hidden_field("forum_post", "parent_id", ['value' => $this->params()->parent_id]) ?>
      <table>
        <tr>
          <td><label for="forum_post_title"><?= $this->t('forum_title') ?></label></td>
          <td><?= $this->text_field('forum_post', 'title', ['size' => 60]) ?></td>
        </tr>
        <tr>
          <td colspan="2"><?= $this->text_area('forum_post', 'body', ['rows' => 20, 'cols' => 80]) ?></td>
        </tr>
        <tr>
          <td colspan="2"><?= $this->submit_tag($this->t('forum_post')) ?><input name="preview" onclick="new Ajax.Updater('preview', '<?= $this->url_for('#preview') ?>', {asynchronous:true, evalScripts:true, onSuccess:function(request){$('preview').show()}, parameters:Form.serialize(this.form)});" type="button" value="<?= $this->t('forum_preview') ?>"/></td>
        </tr>
      </table>
    <?php }) ?>
  </div>

</div>

<?php $this->content_for('subnavbar', function(){ ?>
  <li><?= $this->link_to($this->t('forum_list'), ['action' => "index"]) ?></li>
  <li><?= $this->link_to($this->t('forum_help'), ['controller' => "help", 'action' => "forum"]) ?></li>
<?php }) ?>

<script type="text/javascript">
  $("forum_post_title").focus();
</script>
