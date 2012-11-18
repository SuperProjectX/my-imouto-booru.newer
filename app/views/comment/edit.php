<div id="comment-edit">
  <h4><?= $this->t('.title') ?></h4>

  <?= $this->form_tag("#update", function(){ ?>
    <?= $this->hidden_field_tag("id", $this->params()->id) ?>
    <?= $this->text_area("comment", "body", array('rows' => 10, 'cols' => 60)) ?><br>
    <?= $this->submit_tag($this->t('.save')) ?>
  <?php }) ?>
</div>
