<div class="help">
  <h1>Help: Image Sampling</h1>

  <div class="section">
    <p>While high resolution images are nice for archival purposes, beyond a certain resolution they become impractical to view and time consuming to download.</p>
    <p>Danbooru will automatically resize any image larger than <?= CONFIG()->sample_width ?>x<?= CONFIG()->sample_height ?> to a more manageable size, in addition to the thumbnail. It will also store the original, unresized image.</p>
    <?php if(!CONFIG()->force_image_samples): ?>
      <p>You can toggle this behavior by changing the Show Image Samples setting in your <?= $this->link_to("user settings", "user#edit") ?>.</p>
    <?php endif ?>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>
