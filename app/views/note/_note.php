<div class="note-box" style="width: <?= $note->width ?>px; height: <?= $note->height ?>px; top: <?= $note->y ?>px; left: <?= $note->x ?>px;" id="note-box-<?= $note->id ?>">
  <div class="note-corner" id="note-corner-<?= $note->id ?>"></div>
</div>

<div class="note-body" id="note-body-<?= $note->id ?>" title="<?= $this->t('note_click') ?>">
  <?= $this->h($note->body) ?>
</div>
