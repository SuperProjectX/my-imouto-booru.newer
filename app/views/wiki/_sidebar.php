<div class="sidebar">
  <div style="margin-bottom: 1em;">
    <h6>Search</h6>
    <?= $this->form_tag(['action' => "index"], ['method' => "get"], function(){ ?>
      <?= $this->text_field_tag("query", $this->params()->query, ['size' => 20, 'id' => "search-box"]) ?>
    <?php }) ?>
  </div>

  <?= $this->render_partial("recently_revised") ?>
</div>
