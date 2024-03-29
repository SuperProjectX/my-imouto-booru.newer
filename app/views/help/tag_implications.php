<div class="help">
  <h1>Help: Tag Implications</h1>
  <p>Suppose you tag a post with <code>miniskirt</code>. Miniskirts are simply a type of skirt, so ideally, you would like people who search for <code>skirt</code> to see your <code>miniskirt</code> post. You could tag your post with both <code>skirt</code> and <code>miniskirt</code>, but this starts to get tedious after awhile.</p>
  <p>Tag implications can be used to describe is-a relationships. A miniskirt is a type of skirt. When a <code>miniskirt &rarr; skirt</code> implication is created, then whenever someone tags a post with <code>miniskirt</code>, Danbooru will also tag it with <code>skirt</code>. The tag is normalized before it is saved to the database.</p>
  <p>Tag implications have a <em>predicate</em> and a <em>consequent</em>. The predicate is what is matched against. In the previous example, it would be <code>miniskirt</code>. The consequent is the tag that is added. In the example, it would be <code>skirt</code>.</p>
  <p>You can have multiple implications for the same predicate. Danbooru will just add all the matching consequent tags. For example, if we created a <code>miniskirt &rarr; female_clothes</code> implication, then anytime someone tagged a post with <code>miniskirt</code> it would be expanded to <code>miniskirt skirt female_clothes</code>.</p>
  <p>Implications can also be chained together. Instead of <code>miniskirt &rarr; female_clothes</code> we could create a <code>skirt &rarr; female_clothes</code> implication. The end result would be the same.</p>
  <p>This implication process occurs AFTER the alias process.<p>
  <p>It's easy to go overboard with implications. It's important not to create implications for frivolous things; for example, we could theoretically implicate everything to an <code>object</code> tag, but this is pointless and only adds bloat to the database. For cases where the predicate and the consequent are synonymous, <a href="/help/tag_aliases">aliases</a> are a much better idea as they have lower overhead.</p>
  <p>While you can <a href="/tag_implication/index">suggest new implications</a>, only an administrator can approve them.</p>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>