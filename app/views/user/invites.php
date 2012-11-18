<h4><?= $this->t('user_invites') ?></h4>
<p><?= $this->t('user_invites_text') ?></p>

<div style="margin-bottom: 2em">
  <h6><?= $this->t('user_invites2') ?></h6>
  <?= $this->form_tag(['action' => 'invites'}, ['onsubmit' => "return confirm('".$this->t('user_invites_text2')."' + \$F('user_name') + '?')", function(){ ?>
    <table width="100%">
      <tfoot>
        <tr>
          <td colspan="2"><?= $this->submit_tag($this->t('user_submit')) ?></td>
        </tr>
      </tfoot>
      <tbody>
        <tr>
          <td><label for="member_name"><?= $this->t('user_name') ?></label></td>
          <td>
            <?=$this->text_field("member", "name", ['class' => 'ac-user-name']) ?>
          </td>
        </tr>
        <tr>
          <td><label for="member_level"><?= $this->t('user_level') ?></label></td>
          <td><?= $this->select("member", "level", [["Contributor" => CONFIG()->user_levels["Contributor"], "Privileged" => CONFIG()->user_levels["Privileged"]]]) ?></td>
        </tr>
      </tbody>
    </table>
  <?php }) ?>
</div>

<div>
  <h6><?= $this->t('user_current_invites') ?></h6>
  <p><?= $this->t('user_current_invites_text') ?></p>

  <table>
    <thead>
      <tr>
        <th><?= $this->t('user_user') ?></th>
        <th><?= $this->t('users_posts') ?></th>
        <th><?= $this->t('user_fav') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->invited_users as $user) : ?>
        <tr>
          <td><?= $this->link_to($this->h(user.pretty_name), ['user#show', 'id' => $uuser->id]) ?></td>
          <td><?= $this->link_to(Post::count(['conditions' => 'user_id = '.$user->id]), ['post#index', 'tags' => 'user:'.$user->name]) ?></td>
          <td><?= $this->link_to($user->post_votes()->select(['score' => 3])->size(), ['controller' => '/post', 'action' => 'index', 'tags' => 'vote:3:'.$user->name.' order:vote']) ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>

<?= $this->render_partial("footer") ?>
