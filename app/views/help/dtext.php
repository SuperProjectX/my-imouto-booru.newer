<div class="help">
  <h1>Help: DText</h1>
  <div class="section">
    <p>DText is the name for Danbooru's custom text formatting language. It's a mishmash of several markdown languages including Textile, MediaWiki, and BBCode.</p>
  </div>

  <div class="section">
    <h4>Inline</h4>
    <dl>
      <dt>http://danbooru.donmai.us</dt>
      <dd>URLs are automatically linked.</dd>
      
      <dt>[b]strong text[/b]</dt>
      <dd>Makes text bold.</dd>
      
      <dt>[i]emphasized text[/i]</dt>
      <dd>Makes text italicized.</dd>
      
      <dt>[[wiki page]]</dt>
      <dd>Links to the wiki.</dd>
      
      <dt>{{touhou monochrome}}</dt>
      <dd>Links to a post search.</dd>
      
      <dt>post #1234</dt>
      <dd>Links to post #1234.</dd>
      
      <dt>forum #1234</dt>
      <dd>Links to forum #1234.</dd>
      
      <dt>comment #1234</dt>
      <dd>Links to comment #1234.</dd>
      
      <dt>pool #1234</dt>
      <dd>Links to pool #1234.</dd>
      
      <dt>[spoiler]Some spoiler text[/spoiler]</dt>
      <dd>Marks a section of text as spoilers.</dd>
    </dl>
  </div>
  
  <div class="section">
    <h4>Block</h4>
    <pre>
      A paragraph.
      
      Another paragraph
      that continues on multiple lines.
      
      
      h1. An Important Header
      
      h2. A Less Important Header
      
      h6. The Smallest Header
      
      
      [quote]
      bob said:
      
      When you are quoting someone.
      [/quote]
    </pre>
  </div>
  
  <div class="section">
    <h4>Lists</h4>
    <pre>
      * Item 1
      * Item 2
      ** Item 2.a
      ** Item 2.b
      * Item 3
    </pre>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>