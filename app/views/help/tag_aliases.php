<div class="help">
  <h1>Help: Tag Aliases</h1>
  <p>Sometimes, two tags can mean the same thing. For example, <code>pantsu</code> and <code>panties</code> have identical meanings. It makes sense that if you search for one, you should also get the results for the other.</p>
  <p>Danbooru tries to fix this issue by using tag aliases. You can alias one or more tags to one reference tag. For example, if we aliased <code>pantsu</code> to <code>panties</code>, then whenever someone searched for <code>pantsu</code> or tagged a post with <code>pantsu</code>, it would be internally replaced with <code>panties</code>. Tags are normalized before they are saved to the database. This means that the <code>pantsu</code> tag only exists in the aliases table.</p>
  <p>When a tag is aliased to another tag, that means that the two tags are equivalent. You would not generally alias <code>rectangle</code> to <code>square</code>, for example, because while all squares are rectangles, not all rectangles are squares. To model this sort of relationship, you would need to use <?= $this->link_to("implications", "#tag_implications") ?>.</p>
  <p>While anyone can <?= $this->link_to("suggest", "tag_alias#index") ?> an alias, only an administrator can approve it.</p>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>