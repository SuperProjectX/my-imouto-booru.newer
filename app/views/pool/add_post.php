<h3><?=$this->$this->t('pool_add' ?></h3>

<?= link_to(image_tag($this->post.preview_url), ['controller' => "post", 'action' => "show", 'id' => $this->post.id] ?>

<p><?=$this->$this->t('pool_add_text' ?></p>

<?= $this->form_tag('action' => "add_post", function(){ ?>
  <?= hidden_field_tag("post_id", $this->post.id) ?>

  <table>
    <tbody>
      <tr>
        <th width="15%"><label for="pool_name"><?=$this->$this->t('pool_pool' ?></label></th>
        <td width="85%">
          <select name="pool_id">
            <?= options_from_collection_for_select $this->pools, 'id', 'pretty_name' ?>
          </select>
      </tr>
      <tr>
        <th><label for="pool_sequence"><?=$this->t('pool_order' ?></label></th>
        <td><?= $this->text_field('pool', 'sequence', ['size' => 5, 'value' => ""]) ?></td>
      </tr>
    </tbody>
  </table>

  <?= $this->submit_tag(t('pool_add')) ?> <?= $this->button_to_function($this->t('pool_cancel'), "history.back()") ?>
<?php }) ?>

<?= $this->render_partial("footer") ?>
