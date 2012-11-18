<?= $this->render_partial("sidebar") ?>

<div class="content">
  <table width="100%">
    <thead>
      <tr>
        <th width="60%">Page</th>
        <th width="40%">Last edited</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->wiki_pages as $wiki_page) : ?>
        <tr class="<?= cycle 'even', 'odd' ?>">
          <td><?= $this->link_to(h($wiki_page->pretty_title), ['controller' => "wiki", 'action' => "show", 'title' => $wiki_page->title]) ?></td>
          <td><?= $wiki_page->updated_at.strftime("%m/%d %I:%M") ?> by <?= $this->h $wiki_page->author ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>

  <div id="paginator">
    <?= will_paginate($this->wiki_pages) ?>
  </div>
</div>


<?= $this->render_partial("footer") ?>
