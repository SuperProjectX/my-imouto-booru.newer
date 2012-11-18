<div id="user-index">
  <?php if (current_user()->is_anonymous) : ?>
    <h2><?= $this->t('.not_logged_in') ?></h2>

    <ul class="link-page">
      <li><?= $this->link_to($this->t('.login'), ['action' => 'login']) ?></li>
      <?php if (CONFIG()->enable_signups) : ?>
        <li><?= $this->link_to($this->t('.signup'), ['action' => 'signup' ])?></li>
      <?php else: ?>
        <li><?= $this->t('.no_signup') ?></li>
      <?php endif ?>
      <li><?= $this->link_to($this->t('.reset_password'), ['action' => 'reset_password']) ?></li>
    </ul>
  <?php else: ?>
    <h2><?= $this->t(['.greet_user', 'u' => current_user()->name]) ?></h2>
    <p><?= $this->t('.action_info') ?></p>

    <div class="section">
      <ul class="link-page">
        <li><?= $this->link_to($this->t('.logout'), ['action' => 'logout']) ?></li>
        <li><?= $this->link_to($this->t('.my_profile'), ['action' => 'show', 'id' => current_user()->id]) ?></li>
        <li><?= $this->link_to($this->t('.my_mail'), ['controller' => 'dmail', 'action' => 'inbox']) ?></li>
        <li><?= $this->link_to($this->t('.my_favorites'), ['controller' => 'post', 'action' => 'index', 'tags' => 'vote:3:'.current_user()->name.' order:vote']) ?></li>
        <li><?= $this->link_to($this->t('.settings'), ['action' => 'edit']) ?></li>
        <li><?= $this->link_to($this->t('.change_password'), ['action' => 'change_password']) ?></li>
      </ul>
    </div>

    <?php if (current_user()->is_janitor_or_higher()) : ?>
      <div>
        <h4><?= $this->t('.moderator_tools') ?></h4>
        <ul class="link-page">
          <li><?= $this->link_to($this->t('.invites'), ['action' => 'invites']) ?></li>
          <?php if (current_user()->is_mod_or_higher()) : ?>
            <li><?= $this->link_to($this->t('.blocked_users'), ['action' => 'show_blocked_users']) ?></li>
          <?php endif ?>
        </ul>
      </div>
    <?php endif ?>
  <?php endif ?>
</div>

<?= $this->render_partial('footer') ?>
