<div class="help">
  <h1>Help: Voting</h1>

  <div class="section">
    <p>You can vote on posts. When you click on the vote up or vote down link, your browser queries Danbooru in the background and records your vote. You can change your vote only if you are logged in.</p>
    <p>In order to vote, you must have Javascript enabled. You DO NOT need an account to vote on posts.</p>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>