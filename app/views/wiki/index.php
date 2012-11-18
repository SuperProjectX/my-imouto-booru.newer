<div style="margin-bottom: 1em;">
  <?= $this->render_partial("sidebar") ?>

  <table width="70%" class="highlightable">
    <tr>
      <th width="70%">Title</th>
      <th width="30%">Last edited</th>
    </tr>
    <?php foreach($this->wiki_pages as $wiki_page) : ?>
      <tr class="<?= $this->cycle('even', 'odd') ?>">
        <td><?= $this->link_to($this->h($wiki_page->pretty_title()), ['controller' => "wiki", 'action' => "show", 'title' => $wiki_page->title, 'nordirect' => 1]) ?></td>
        <td><?= date("m/d h:i", strtotime($wiki_page->updated_at)) ?> by <?= $this->h($wiki_page->author()) ?></td>
      </tr>
    <?php endforeach ?>
  </table>

  <div id="paginator">
    <?= $this->will_paginate($this->wiki_pages) ?>
  </div>
</div>

<?= $this->render_partial("footer") ?>
