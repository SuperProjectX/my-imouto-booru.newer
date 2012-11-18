<?= form_tag('action' => "update") do ?>
  <?= hidden_field_tag "title", $this->params()->title ?>
  <label for="wiki_page_title">Title</label> <?= $this->text_field('wiki_page', 'title') ?><br>
  <?= $this->submit_tag("Save") ?> <?= $this->button_to_function("Cancel", "history.back()") ?>
<?php end ?>

<?= $this->render_partial("footer") ?>
