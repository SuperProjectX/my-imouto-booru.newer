<?php #iTODO: give support to "form_for" ? ?>
<div id="user-edit">
  <?= $this->form_tag([ 'action' => 'update' ], function() { ?>
    <?= $this->hidden_field('render', 'view', ['value' => 'change_password']) ?>
    <?= $this->render_partial('shared/error_messages', ['object' => current_user()]) ?>
    <table>
      <tbody>
        <tr>
          <th><label for="user_current_password">Current password</label></th>
          <td><?= $this->password_field('user', 'current_password') ?></td>
        </tr>
        <tr>
          <th><label for="user_password"><?= $this->t('.new_password') ?></label></th>
          <td><?= $this->password_field('user', 'password') ?></td>
        </tr>
        <tr>
          <th><label for="user_password">Password confirmation</label></th>
          <td><?= $this->password_field('user', 'password_confirmation') ?></td>
        </tr>
        <tr>
          <td><?= $this->submit_tag($this->t('buttons.save')) ?> <?= $this->submit_tag($this->t('buttons.cancel')) ?></td>
        </tr>
      </tbody>
    </table>
  <?php }) ?>
</div>

<?= $this->render_partial("footer") ?>
