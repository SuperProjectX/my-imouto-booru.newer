<table width="100%" class="row-highlight">
  <thead>
    <tr>
      <th></th>
      <th width="5%"><?= $this->t('note_post') ?></th>
      <th width="5%"><?= $this->t('note_note') ?></th>
      <th width="60%"><?= $this->t('note_body') ?></th>
      <th width="10%"><?= $this->t('note_edited') ?></th>
      <th width="10%"><?= $this->t('note_date') ?></th>
      <th width="10%"><?= $this->t('note_options') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($this->notes as $note) : ?>
      <tr class="<?= cycle 'even', 'odd' ?>">
        <td style="background: <?= id_to_color(note.post_id) ?>;"></td>
        <td><?= $this->link_to(note.post_id, 'post#show', 'id' => 'note'.post_id ?></td>
        <td><?= $this->link_to("#[note.note_id}.#{note.version]", 'note#history', 'id' => 'note'.note_id ?></td>
        <td><?= $this->h(note.body) ?> <?php unless note.is_active? ?>(deleted)<?php end ?></td>
        <td><?= $this->link_to($this->h(note.author), 'user#show', 'id' => 'note'.user_id ?></td>
        <td><?= note.updated_at.strftime("%D") ?></td>
        <td><?= $this->link_to($this->t('note_revert'), array('note#revert', 'id' => note.note_id, 'version' => note.version), 'method' => 'post', 'confirm' => $this->t('note_revert_confirm') ?></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>

<div id="paginator">
  <?= will_paginate($this->notes) ?>
</div>

<?= $this->render_partial(") ?>
