<h3><?=$this->t('pool_create') ?></h3>

<?= $this->form_tag(['action' => "create"], ['level' => 'member'], function(){ ?>
  <table class="form">
    <tbody>
      <tr>
        <th><label for="pool_name"><?=$this->t('pool_name') ?></label></th>
        <td><?= $this->text_field('pool', 'name') ?></td>
      </tr>
      <tr>
        <th><label for="pool_is_public"><?=$this->t('pool_public') ?></label></th>
        <td><?= $this->check_box("pool", "is_public") ?></td>
      </tr>
      <tr>
        <th><label for="pool_description"><?=$this->t('pool_description') ?></label></th>
        <td><?= $this->text_area('pool', 'description', ['size' => "40x10"]) ?></td>
      </tr>
      <tr>
        <td colspan="2"><?= $this->submit_tag($this->t('pool_save')) ?> <?= $this->button_to_function($this->t('pool_cancel'), "history.back()") ?></td>
      </tr>
    </tbody>
  </table>
<?php }) ?>

<?= $this->render_partial("footer") ?>
