<h4><?= $this->t('post_deleted_posts') ?></h4>

<table width="100%" class="highlightable">
  <thead>
    <tr>
<!--      <th width="5%">Resolved</th> -->
      <th width="5%"><?= $this->t('post_deleted_post') ?></th>
      <th width="10%"><?= $this->t('post_deleted_user') ?></th>
      <th width="45%"><?= $this->t('post_deleted_tags') ?></th>
      <th width="35%"><?= $this->t('post_deleted_reason') ?></th>
      <?php if (current_user()->is_mod_or_higher()) : ?>
      <th width="1*"><?= $this->t('post_deleted_by') ?></th>
      <?php endif ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($this->posts as $post) : ?>
      <tr class="<?= $this->cycle('even', 'odd') ?>">
<!--        <td><?= $post->flag_detail->is_resolved() ?></td> -->
        <td><?= $this->link_to($post->id, ['action' => 'show', 'id' => $post->id]) ?></td>
        <td><?= $this->link_to($this->h($post->author), ['user#show', 'id' => $post->user_id]) ?></td>
        <td><?= $this->h($post->cached_tags) ?></td>
        <td><?= $this->h($post->flag_detail->reason) ?></td>
        <?php if (current_user()->is_mod_or_higher()) : ?>
        <td><?= $this->link_to($this->h($post->flag_detail->author), ['user#show', 'id' => $post->flag_detail->user_id]) ?></td>
        <?php endif ?>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>

<div id="paginator">
  <?= $this->will_paginate($this->posts) ?>
</div>

<?= $this->render_partial('footer') ?>
