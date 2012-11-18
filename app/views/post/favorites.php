<h3><?= $this->t('post_favorited_by') ?></h3>
<ul>
<?php $this->users.each do |u| ?>
  <li><?= link_to $this->h(u.pretty_name), 'post#index', 'tags' => 'vote:3:#{u.name} order:vote' ?></li>
<?php end ?>
</ul>

<?= $this->render_partial('footer') ?>
