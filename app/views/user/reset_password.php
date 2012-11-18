<div class="page">
  <h4><?= $this->t('user_reset') ?></h4>
  <p><?= $this->t('user_reset_text') ?></p>

  <?= $this->form_tag(['action' => 'reset_password'], function(){ ?>
    <table class="form">
      <tbody>
        <tr>
          <th>
            <label class="block" for="user_name"><?= $this->t('user_reset_name') ?></label>
          </th>
          <td>
            <?=$this->text_field("user", "name") ?>
          </td>
        </tr>
        <tr>
          <th><label class="block" for="user_email"><?= $this->t('user_reset_email') ?></label></th>
          <td><?=$this->text_field("user", "email") ?></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2">
            <?= $this->submit_tag($this->t('user_resend_submit')) ?>
          </td>
        </tr>
      </tfoot>
    </table>
  <?php }) ?>
</div>

<?= $this->render_partial("footer") ?>
