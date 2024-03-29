<div class="help">
  <h1>Help: Getting Started</h1>
  <p>If you are already familiar with Danbooru, you may want to consult the <a href="/help/cheatsheet">cheat sheet</a> for a quick overview of the site.</p>
  <p>The core of Danbooru is represented by <a href="/help/posts">posts</a> and <a href="/help/tags">tags</a>. Posts are the content, and tags are how you find the posts.</p>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>