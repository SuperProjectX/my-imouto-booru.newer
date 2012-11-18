<div class="help">
  <h1>Help: Post Relationships</h1>

  <p>Every post can have a parent. Any post that has a parent will not show up in the <?= $this->link_to("main listing", "post#index") ?>. However, the post will appear again if a user does any kind of tag search. This makes it useful for things like duplicates.</p>
  <p>Please do not use parent/children for pages of a manga or doujinshi. It's better to use a <?= $this->link_to("pool", "#pools") ?> for these.</p>
  <p>To use this field, simply enter the id number of the parent post when you upload or edit a post. To search for all the children of a parent post, you can do a tag search for <code>post:nnnn</code>, where <code>nnnn</code> is the id number of the parent post.</p>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>