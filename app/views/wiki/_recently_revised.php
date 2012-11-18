<div style="margin-bottom: 1em;">
  <h6>Recent Changes (<?= $this->link_to("all", ['action' => "index", 'order' => "date"]) ?>)</h6>
  <ul>
    <?php foreach (WikiPage::find_all(['limit' => 25, 'order' => "updated_at desc"]) as $page) : ?>
      <li><?= $this->link_to($this->h($page->pretty_title()), ['controller' => "wiki", 'action' => "show", 'title' => $page->title]) ?></li>
    <?php endforeach ?>
  </ul>
</div>
