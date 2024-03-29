<div class="help">
  <h1>Help: Pools</h1>

  <div class="section">
    <p>Pools are groups of posts with a common theme. They are similar to <?= $this->link_to("favorites", "#favorites") ?> with three important differences: public pools allow anyone to add or remove from them, you can create multiple pools, and posts in a pool can be ordered. This makes pools ideal for subjective tags, or for posts that are part of a series (as is the case in manga).</p>
    <p>The interface for adding and removing pools resembles the interface for favorites. You can click on <strong>Add to Pool</strong> from the post's page. You'll be redirected to a page where you can select the pool.</p>
    <p>If you're importing several posts into a pool, this process can become tedious. You can instead click on the Import link at the bottom of the pool's page. This allows you to execute a post search using any <?= $this->link_to("tag combination", "#cheatsheet") ?> you would normally use. Remove any posts that are irrelevant to the pool, then finish the import process.</p>
    <p>Pools can be private or public. A private pool means you are the only person who can add or remove from it. In contrast, public pools can be updated by anyone, even anonymous users.</p>
    <p>To remove a post from a pool, go to the pool's page and select the Delete Mode checkbox. Then click on the posts you want to delete. This works similarly to how posts are deleted from favorites.</p>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>