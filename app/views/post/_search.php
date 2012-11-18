<div style="margin-bottom: 1em;">
  <h5><?= $this->t('post_search') ?></h5>
  <?= $this->form_tag('post#index', array('method' => 'get'), function(){ ?>
    <div>
      <?= $this->text_field_tag("tags", $this->params()->tags, array('size' => '20')) ?>
      <?= $this->submit_tag($this->t('post_search'), array('style' => 'display: none;')) ?>
    </div>
  <?php }) ?>
</div>
<script type="text/javascript">
  new TagCompletionBox($("tags"));
  if(TagCompletion)
    TagCompletion.observe_tag_changes_on_submit($("tags").up("form"), $("tags"), null);
</script>
