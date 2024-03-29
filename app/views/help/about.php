<div class="help">
  <h1>Help: About</h1>

  <div class="section">
    <p>Danbooru is a web application that allows you to upload, share, and tag images. Much of it is inspired by both <a href="http://moeboard.net">Moeboard</a> and <a href="http://flickr.com">Flickr</a>. It was specifically designed to be of maximum utility to seasoned imageboard hunters. Some of these features include:</p>
    <ul>
      <li>Posts never expire</li>
      <li>Tag and comment on posts</li>
      <li><a href="/help/tags">Search for tags</a> via intersection, union, negation, or pattern</li>
      <li>Integrated <a href="/help/wiki">wiki</a></li>
      <li>Annotate images with <a href="/help/notes">notes</a></li>
      <li>Input a URL and Danbooru automatically downloads the file</li>
      <li>Duplicate post detection (via MD5 hashes)</li>
      <li>REST-based <a href="/help/api">API</a></li>
      <li>Atom and RSS feeds for posts</li>
      <li><a href="/help/bookmarklet">Bookmarklet</a> and <a href="/help/extension">Firefox extension</a></li>
    </ul>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>