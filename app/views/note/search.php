<h4><?= $this->t('note_search') ?></h4>

<?= $this->form_tag(['action' => 'search'], ['method' => 'get'], function(){ ?>
  <?= $this->text_field_tag("query", $this->params()->query,  ['size' => '40']) ?> <?= $this->submit_tag($this->t('note_search')) ?>
<?php }) ?>

<?php if ($this->notes) : ?>
  <div style="margin-top: 2em;">
    <?php foreach ($this->notes as $note) : ?>
      <div style="float: left; clear: both; margin-bottom: 2em;">
        <div style="float: left; width: 200px;">
          <?= $this->link_to($this->image_tag($note->post->preview_url, ['width' => $note->post->preview_dimensions()[0], 'height' => $note->post->preview_dimensions()[1]]), ['post#show', 'id' => $note->post_id]) ?>
        </div>
        <div style="float: left;">
          <?= $this->h($note->formatted_body()) //sanitize ?>
        </div>
      </div>
    <?php endforeach ?>
  </div>

  <div id="paginator">
    <?= $this->will_paginate($this->notes) ?>
  </div>
<?php endif ?>

<?= $this->render_partial("footer") ?>
