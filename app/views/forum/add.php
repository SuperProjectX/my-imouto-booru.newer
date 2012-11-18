<?= form_tag('action' => "create") do ?>
  <?= hidden_field_tag "forum_post[parent_id]", params["parent_id"], ['id' => "forum_post_parent_id"] ?>
  <table>
    <tr><td><label for="forum_post_title"><?=$this->$this->t('forum_title' ?></label></td><td><?= $this->text_field('forum_post', 'title', ['size' => 60]) ?></td></tr>
    <tr><td colspan="2"><?= $this->text_area('forum_post', 'body', ['rows' => 20, 'cols' => 80]) ?></td></tr>
    <tr><td colspan="2"><?= $this->submit_tag(t('forum_post')) ?></td></tr>
  </table>
<?php end ?>
