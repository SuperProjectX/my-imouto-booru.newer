<div class="help">
  <h1>Help: Ratings</h1>

  <div class="section">
    <p>All posts on Danbooru are one of three ratings: Safe, Questionable, and Explicit. Questionable is the default if you don't specify one. <strong>Please note that this system is not foolproof</strong>: from time to time explicit images will be tagged safe, and vice versa. Therefore you should not depend on ratings unless you can tolerate the occasional exception.</p>

    <div class="section">
      <h4>Explicit</h4>
      <p>Any image where the vagina or penis are exposed and easily visible. This includes depictions of sex, masturbation, or any sort of penetration.</p>
    </div>
  
    <div class="section">
      <h4>Safe</h4>
      <p>Safe posts are images that you would not feel guilty looking at openly in public. Pictures of nudes, exposed nipples or pubic hair, cameltoe, or any sort of sexually suggestive pose are NOT safe and belong in questionable. Swimsuits and lingerie are borderline cases; some are safe, some are questionable.</p>
    </div>
  
    <div class="section">
      <h4>Questionable</h4>
      <p>Basically anything that isn't safe or explicit. This is the great middle area, and since it includes unrated posts, you shouldn't really expect anything one way or the other when browsing questionable posts.</p>
    </div>

    <div class="section">
      <h4>Search</h4>
      <p>You can filter search results by querying for <code>rating:s</code>, <code>rating:q</code>, or <code>rating:e</code> for safe, questionable, and explicit posts, respectively. You can also combine them with other tags and they work as expected.</p>
      <p>If you want to remove a rating from your search results, use <code>-rating:s</code>, <code>-rating:q</code>, and <code>-rating:e</code>.</p>
    </div>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>