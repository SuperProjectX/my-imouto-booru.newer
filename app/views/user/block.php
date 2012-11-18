<?= $this->form_tag(['action' => 'block'], function(){ ?>
  <?= $this->hidden_field("ban", "user_id") ?>
  <table class="form">
    <tfoot>
      <tr>
        <td></td>
        <td><?= $this->submit_tag($this->t('user_submit')) ?></td>
      </tr>
    </tfoot>
    <tbody>
      <tr>
        <th><label for="ban_reason"><?= $this->t('user_reason') ?></label></th>
        <td><?= $this->text_area("ban", "reason", 'size' => '40x5' ?></td>
      </tr>
      <tr>
        <th>
          <label for="ban_duration"><?= $this->t('user_duration') ?></label>
        </th>
        <td><?=$this->text_field("ban", "duration", ['size' => '10']) ?></td>
      </tr>
    </tbody>
  </table>
<?php }) ?>

<?= $this->render_partial("footer") ?>
