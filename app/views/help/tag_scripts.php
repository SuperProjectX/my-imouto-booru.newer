<div class="help">
  <h1>Help: Tag Scripts</h1>

  <div class="section">
    <p>Tag scripts allow you to batch together several tag changes. With a single script you can add tags, remove tags, conditionally add tags, conditionally remove tags, or any combination of the above. Simply create one, select it, and click on a post thumbnail to apply the tag script in the background. The best way to illustrate how they work is through examples.</p>
    <p>You can combine commands, but you cannot nest them. For example, <code>[if cat, dog] [if dog, cat]</code> works, but <code>[if cat, [reset]]</code> does not.</p>
  
    <div class="section">
      <h4>Add</h4>
      <ul>
        <li><code>cat dog</code> would add the <code>cat</code> and <code>dog</code> tag.</li>
      </ul>
    </div>
  
    <div class="section">
      <h4>Remove</h4>
      <ul>
        <li><code>-cat -dog</code> would remove the <code>cat</code> and <code>dog</code> tag.</li>
        <li><code>cat -dog</code> would add the <code>cat</code> tag and remove the <code>dog</code> tag.</li>
      </ul>
    </div>
  
    <div class="section">
      <h4>Conditional</h4>
      <ul>
        <li><code>[if cat, dog]</code> would add the <code>dog</code> tag if and only if the post had the <code>cat</code> tag.</li>
        <li><code>[if -cat, dog]</code> would add the <code>dog</code> tag if and only if the post did not have the <code>cat</code> tag.</li>
        <li><code>[if cat, -dog]</code> would remove the <code>dog</code> tag if and only if the post had the <code>cat</code> tag.</li>
        <li><code>[if -cat, -dog]</code> would remove the <code>dog</code> tag if and only if the post did not have the <code>cat</code> tag.</li>
        <li><code>[if cat -animal, animal]</code> would add the <code>animal</code> tag if and only if the post had the <code>cat</code> tag but did not have the <code>animal</code> tag.</li>
      </ul>
    </div>
  
    <div class="section">
      <h4>Reset</h4>
      <ul>
        <li><code>[reset]</code> would remove every tag from the post.</li>
        <li><code>[reset] cat</code> would remove every tag from the post, then add the <code>cat</code> tag.</li>
        <li><code>cat [reset]</code> would add the <code>cat</code> tag, then remove every tag from the post (this is a pointless script).</li>
      </ul>
    </div>

    <div class="section">
      <h4>Rating Changes</h4>
      <ul>
        <li><code>rating:e</code> would change the post's rating to explicit.</li>
        <li><code>[if sex, rating:e]</code> would change the post's rating to explicit if and only if it had the <code>sex</code> tag.</li>
      </ul>
    </div>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>