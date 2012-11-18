<div class="page">
  <p><?= $this->t('user_resend') ?></p>
  <?= $this->form_tag('#resend_confirmation', function(){ ?>
    <table class="form">
      <tbody>
        <tr>
          <th>
            <label class="block" for="email"><?= $this->t('user_resend_email') ?></label>
          </th>
          <td>
            <?= $this->text_field_tag("email") ?>
          </td>
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
