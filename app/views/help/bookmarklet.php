<div class="help">
  <h1>Help: Bookmarklet</h1>

  <div class="section">
    <p>Bookmark the following link: <a href="javascript:location.href='http://<?= CONFIG()->server_host ?>/post/upload?url='+encodeURIComponent(location.href)">Post to <?= CONFIG()->app_name ?></a>.</p>
  
    <div class="section">
      <h4>How to Use</h4>
      <ul>
        <li>Click on the bookmarklet.</li>
        <li>All images that can be uploaded to <?= CONFIG()->app_name ?> will get a thick dashed blue border.</li>
        <li>Click on an image to upload it to <?= CONFIG()->app_name ?>.</li>
        <li>You'll be redirected to the upload page where you can fill out the tags, the title, and set the rating.</li>
      </ul>
    </div>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>