<div class="help">
  <h1>Help: Favorites</h1>

  <div class="section">
    <p>You can save individual posts to a personal list of favorites. You need an <a href="/help/users">account</a> in order to use this feature, and you must have Javascript enabled.</p>
    <p>To add a post to your favorites, simply click on the <strong>Add to Favorites</strong> link. Alternatively, you can use the Add to Favorites <a href="/help/posts#mode-menu">mode</a> from the <a href="/post/index">main listing</a>.</p>
    <p>You can view your favorites by clicking on <strong>My Favorites</strong> from the <a href="/post/index">main listing</a>, or going to <a href="/user/home">My Account</a>, then <strong>My Favorites</strong>.</p>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>
