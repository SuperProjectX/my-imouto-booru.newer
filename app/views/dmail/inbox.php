<h4><?= $this->t('dmail_inbox') ?></h4>

<?php if ($this->dmails->blank()) : ?>
  <p><?=$this->t('dmail_empty') ?></p>
<?php else: ?>
  <div class="mail">
    <table width="100%" class="highlightable">
      <thead>
        <tr>
          <th width="15%"><?=$this->t('dmail_from') ?></th>
          <th width="15%"><?=$this->t('dmail_to') ?></th>
          <th width="55%"><?=$this->t('dmail_title') ?></th>
          <th width="15%"><?=$this->t('dmail_when') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->dmails as $dmail) : ?>
          <tr class="<?= $this->cycle('even', 'odd') ?>" id="row-<?= $dmail->id ?>">
            <td><?= $this->h($dmail->from->name) ?></td>
            <td><?= $this->h($dmail->to->name) ?></td>
            <td>
              <?php if ($dmail->from_id == current_user()->id) : ?>
                <?= $this->link_to($this->h($dmail->title), ['action' => "show", 'id' => $dmail->id], ['class' => "sent"]) ?>
              <?php else: ?>
                <?php if ($dmail->has_seen) : ?>
                  <?= $this->link_to($this->h($dmail->title), ['action' => "show", 'id' => $dmail->id], ['class' => "received"]) ?>
                <?php else: ?>
                  <strong><?= $this->link_to($this->h($dmail->title), ['action' => "show", 'id' => $dmail->id], ['class' => "received"]) ?></strong>
                <?php endif ?>
              <?php endif ?>
            </td>
            <td><?= $this->t(['time.x_ago', 't' => $this->time_ago_in_words($dmail->created_at)]) ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
<?php endif ?>

<div id="paginator" style="margin-bottom: 1em;">
  <?= $this->will_paginate($this->dmails) ?>
</div>

<?= $this->render_partial("footer") ?>
