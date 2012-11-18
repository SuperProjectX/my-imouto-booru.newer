<div class="help">
  <h1>Help: Source Code</h1>

  <div class="section">
    <p>You can get the Danbooru source code using Subversion. Run <code>svn co svn://donmai.us/danbooru/trunk</code> for the latest copy.</p>
    <p>All Danbooru code is released under a FreeBSD license.</p>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>