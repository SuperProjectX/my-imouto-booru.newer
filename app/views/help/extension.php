<div class="help">
  <h1>Help: Firefox Extension</h1>

  <div class="section">
    <p>There is a <a href="http://unbuffered.info/danbooruup">Firefox extension available</a> to upload files from sites that have some sort of referrer or cookie access restriction. It is an alternative to the <a href="/help/bookmarklet">bookmarklet</a>. The extension provides autocomplete for tags when adding a post or using the site.</p>
    <p>Note that you need Firefox 2.0.x for the version 0.2.7 and above, which is also now compatible with the <a href="http://lolifox.com">lolifox</a>, but 0.2.6 is still available for Firefox 1.5.x users. On upgrading from Firefox 1.5 to 2.0 you should be automatically prompted to update to the latest compatible version.</p>
    <p>As of version 0.2.8 the autocomplete function extends to the input fields on Danbooru itself.</p>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>