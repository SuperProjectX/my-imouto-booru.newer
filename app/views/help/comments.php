<div class="help">
  <h1>Help: Comments</h1>

  <div class="section">
    <p>All comments are formatted using <?= $this->link_to("DText", "#dtext") ?>.</p>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>