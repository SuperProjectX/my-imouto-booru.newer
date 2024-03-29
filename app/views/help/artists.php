<div class="help">
  <h1>Help: Artists</h1>

  <div class="section">
    <h4>What are artists?</h4>
    <p>Artists in Danbooru represent the people who created a piece of art. Originally tags were used to describe artists (and they still are), but in many ways tags are insufficient. You can't tie a URL to a tag for example. You can fake a hierarchy using tag implications, but in most this cases this is excessive and leads to an explosion of redundant tags. For these reasons, artists were elevated to first class status in Danbooru.</p>
  </div>

  <div class="section">
    <h4>How do artists differ from tags?</h4>
    <p>For starters, artists can have URLs associated with them. These come in handy when you're uploading a post from the artist's site and want to auto-identify it; Danbooru will query the artist database for the URL and automatically figure out who it is. It isn't foolproof but as the database gets more artists, the more reliable it becomes.</p>
    <p>You can also organize artists more. Doujin circles can be represented as artists and can have group members. Artists can also have aliases and notes for extraneous details.</p>
  </div>

  <div class="section">
    <h4>How do I search for artists?</h4>
    <p>Start at the <?= $this->link_to("index", "artist#index") ?>. In addition to browsing through the entire artist list, you can also search for artists.</p>
    <p>By default, if you just enter a name in the search box Danbooru will return any artist that has your query in their name. This is probably the behavior you want in most cases.</p>
    <p>Suppose you know the artist's homepage, but can't figure out their name. Simply search for the URL (beginning with http) and Danbooru will return any associated artists.</p>
  </div>

  <div class="section">
    <h4>How do I create an artist?</h4>
    <p>First off, <?= $this->link_to("go here", "artist#create") ?>.</p>
    <p>You'll see five fields. <strong>Name</strong> is self-explanatory. <strong>Jap Name/Aliases</strong> is for any aliases the artist has. For example, you would place the artist's name in kanji or kana in this field. If you have more than one alias to enter, you can separate them with commas. <strong>Notes</strong> are for any extra tidbits of information you want to mention (this field is actually saved to the artist's matching wiki page on Danbooru).</p>
    <p>The URLs field is a list of URLs associated with the artist, like their home page, their blog, and any servers that store the artist's images. You can separate multiple artists with newlines or spaces.</p>
  </div>

  <div class="section">
    <h4>How do I update an artist?</h4>
    <p>The interface for updating an artist is nearly identical to the interface for creating artists, except for one additional field: members. <strong>Members</strong> is for artists who are a member of this circle. If there are more than one, you can separate them with commas.</p>
  </div>

  <div class="section">
    <h4>What are aliases?</h4>
    <p>Artists often have more than one name. In particular, they can have a Japanese name and a romanized name. Ideally, users should be able to search for either and get the same artist.</p>
    <p>Danbooru allows you to alias artists to one reference artist, typically one that you can search posts on.</p>
  </div>

  <div class="section">
    <h4>Are artists in any way tied to posts or tags?</h4>
    <p>No. If you create an artist, a corresponding tag is not automatically created. If you create an artist-typed tag, a corresponding artist is not automatically created. If you create an artist but no corresponding tag, searching for posts by that artist won't return any results.</p>
    <p>You can think of the artist database as separate from the tags/posts database.</p>
    <p>This is an intentional design decision. By keeping the two separated, users have far more freedom when it comes to creating aliases, groups, and edits.</p>
  </div>

  <div class="section">
    <h4>When I search for a URL, I get a bunch of unrelated results. What's going on?</h4>
    <p>Short answer: this is just a side-effect of the way Danbooru searches URLs. Multiple results typically mean Danbooru couldn't find the artist.</p>
    <p>Long answer: when you're searching for a URL, typically it's a URL to an image on the artist's site. If this is a new image, querying this will obviously return no results.</p>
    <p>So what Danbooru does is progressively chop off directories from the URL. http://site.com/a/b/c.jpg becomes http://site.com/a/b becomes http://site.com/a becomes http://site.com. It keeps doing this until a match is found. Danbooru does this more than once because there are cases where the URL is nested by date, like in http://site.com/2007/06/05/image.jpg. Usually this algorithm works very well, provided the artist has an entry in the database.</p>
    <p>If he doesn't, then the algorithm is probably going to cut the URL down to just the domain, i.e. http://geocities.co.jp. When this happens, you'll sometimes get every artist hosted on that domain.</p>
    <p>Why not just dump all the results if you get more than one? Well, there are a few cases when multiple artists validly map to the same domain. Usually the domain is just being used to host files or something.</p>
  </div>

  <div class="section">
    <h4>Is there an API?</h4>
    <p>Yes. The artist controller uses the same interface as the rest of Danbooru. See the <?= $this->link_to("API documentation", "help#api") ?> for details.</p>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>
