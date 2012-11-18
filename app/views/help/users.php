<div class="help">
  <h1>Help: Accounts</h1>

  <div class="section">
    <p>There are three types of accounts: basic, privileged, and blocked.</p>
    <p>See the <?= $this->link_to("signup", "user#signup") ?> page for more details.</p>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>