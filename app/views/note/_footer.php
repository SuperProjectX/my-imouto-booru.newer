<?php $this->content_for('subnavbar', function() { ?>
  <li><?= $this->link_to($this->t('note_list'), '#index') ?></li>
  <li><?= $this->link_to($this->t('note_search'), '#search') ?></li>
  <li><?= $this->link_to($this->t('note_history'), '#history') ?></li>
  <li><?= $this->link_to($this->t('note_requests'), ['post#index', 'tags' => 'translation_request']) ?></li>
  <li><?= $this->link_to($this->t('note_help'), 'help#notes') ?></li>
<?php }) ?>
