<div class="help">
  <h1>Help: Mass Tag Edit</h1>
  <p><em>Note: this function is only available to moderators.</em></p>
  <p>Mass tag edit allows you to make sweeping changes to posts. It allows you to add tags, remove tags, or change tags to potentially thousands of posts at once. It is an extremely powerful feature that should be used with great caution.</p>
  <p>There are two text fields and two buttons. The first text field is where you enter your tag query. The tag parser is identical to the one used for the main listing so any tag query that works there will work here. This includes all the meta-tags like source, id, user, and date. The second text field is where you enter the tags you want to tag the matching posts with.</p>
  <p>Click on the Preview button to see what posts will be affected. This is based solely on the first text field. When you click on Save, this is what happens: Danbooru finds all the posts that match the query you entered in the first text field. Then, for each post, it removes any tag from the first text field, and adds all the tags from the second text field.</p>
  <p>Here is a table explaining some of the things that you can do:</p>
  <table>
    <tr>
      <th>Tag Query</th>
      <th>Add Tags</th>
      <th>Effect</th>
    </tr>
    <tr>
      <td>apple</td>
      <td>banana</td>
      <td>Change every instance of the <code>apple</code> tag to <code>banana</code>.</td>
    </tr>
    <tr>
      <td>apple</td>
      <td></td>
      <td>Delete every instance of the <code>apple</code> tag.</td>
    </tr>
    <tr>
      <td>apple orange</td>
      <td>apple</td>
      <td>Find every post that has both the <code>apple</code> tag and the <code>orange</code> tag and delete the <code>orange</code> tag.</td>
    </tr>
    <tr>
      <td>source:orchard</td>
      <td>apple</td>
      <td>Find every post with <code>orchard</code> as the source and add the <code>apple</code> tag.</td>
    </tr>
    <tr>
      <td>id:10..20 -apple</td>
      <td>apple</td>
      <td>Find posts with id numbers between 10 and 20 that don't have the <code>apple</code> tag, and tag them with <code>apple</code>.</td>
    </tr>
  </table>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>