<div class="help">
  <h1>Help: Notes</h1>

  <div class="section">
    <p>You can annotate images with notes. This is primarily used to translate text. Please do not use a note when a comment would suffice.</p>
    <p>Because this feature makes heavy usage of DHTML and Ajax, it probably won't work on many browsers. Currently it's been tested with Firefox 2, IE6, and IE7.</p>
    <p>If you have an issue with an existing note or have a comment about it, instead of replacing the note, post a comment. Comments are more visible to other users, and chances are someone will respond to your inquiry.</p>
    <p>You can create a new note via the <strong>Add Translation</strong> link in the sidebar. The note will appear in the middle of the image. You can drag this note inside the image. You can resize the note by dragging the little black box on the bottom-right corner of the note.</p>
    <p>When you mouse over the note box, the note body will appear. You can click on the body and another box will appear where you can edit the text. This box will also contain four links:</p>
    <ul>
      <li><strong>Save</strong> This saves the note to the database.</li>
      <li><strong>Cancel</strong> This reverts the note to the last saved copy. The note position, dimensions, and text will all be restored.</li>
      <li><strong>History</strong> This will redirect you to the history of the note. Whenever you save a note the old data isn't destroyed. You can always revert to an older version. You can even undelete a note.</li>
      <li><strong>Remove</strong> This doesn't actually remove the note from the database; it only hides it from view. You can undelete a note by reverting to a previous version.</li>
    </ul>
    <p>All HTML code will be sanitized. You can place small translation notes by surrounding a block of text with <code>&lt;tn&gt;...&lt;/tn&gt;</code> tags.</p>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>