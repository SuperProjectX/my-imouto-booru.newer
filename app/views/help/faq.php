<div class="help">
  <h1>Help: Frequently Asked Questions</h1>

  <div class="section">
    <h4>How can I get a contributor account?</h4>
    <p>A moderator or janitor has to invite you.</p>
    
    <h4>How do I delete a tag?</h4>
    <p>If you are asking how to delete a tag that has no posts associated with it, you don't have to. A nightly batch is run that cleans up any unused tag.</p>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>
