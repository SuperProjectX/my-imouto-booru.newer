<?= $this->render_partial("sidebar") ?>

<div class="content" style="float: left; width: 40em;">
  <div id="wiki-view">
  </div>
  <?= $this->form_tag(['action' => "create"], 'level'=>'member', function(){ ?>
    <?= $this->text_field('wiki_page', 'title') ?>
    <?= $this->render_partial("edit_buttons") ?>
  <?php }) ?>
</div>

<div style="float: left; width: 15em;">
  <h4>Reference</h4>
  <pre>
A paragraph.

Followed by another.

h4. A header

* List item 1
* List item 2
* List item 3

Linebreaks are important between lists,
headers, and paragraphs.

A "conventional link"'http'://www.google.com

A [[wiki link]] (underscores are not needed)

An aliased [[real page|wiki link]]

<a href="http://hobix.com/textile/quick.html" target="_blank">Read more</a>.
  </pre>
</div>

<?= $this->render_partial("footer") ?>
