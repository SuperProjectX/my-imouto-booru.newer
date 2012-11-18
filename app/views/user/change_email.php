<?php # iTODO: form_for support ?>
<div id="user-edit">
  <?= $this->form_tag([ 'action' => 'update' ], function() { ?>
    <?= $this->hidden_field('render', 'view', ['value' => 'change_email']) ?>
    <?php# Just so the current email carries over on error -?>
    <?= $this->hidden_field('user', 'current_email') ?>
    <?= $this->render_partial('shared/error_messages', ['object' => $this->user]) ?>
    <table>
      <tbody>
        <tr>
          <th><?= $this->t('.current_email') ?></th>
          <td><?= current_user()->current_email ?></td>
        </tr>
        <tr>
          <th><label for="user_email"><?= $this->t('.new_email') ?></label></th>
          <td><?= $this->text_field('user', 'email') ?></td>
        </tr>
        <tr>
          <th><label for="user_current_password"><?= $this->t('.current_password') ?></label></th>
          <td><?= $this->password_field('user', 'current_password') ?></td>
        </tr>
        <tr>
          <td><?= $this->submit_tag($this->t('buttons.save')) ?> <?= $this->submit_tag($this->t('buttons.cancel')) ?></td>
        </tr>
      </tbody>
    </table>
  <?php }) ?>
</div>

<?= $this->render_partial("footer") ?>
