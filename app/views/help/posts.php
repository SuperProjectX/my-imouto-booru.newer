<div class="help">
  <h1>Help: Posts</h1>
  <p>A post represents a single file that's been uploaded. Each post can have several <a href="/help/tags">tags</a>, <a href="/help/comments">comments</a>, and <a href="/help/notes">notes</a>. If you have an <a href="/help/users">account</a>, you can also add a post to your <a href="/help/favorites">favorites</a>.</p>
  
  <div class="section">
    <h4>Search</h4>
    <p>Searching for posts is straightforward. Simply enter the tags you want to search for, separated by spaces. For example, searching for <code>original panties</code> will return every post that has both the original tag <strong>AND</strong> the panties tag.</p>
    <p>That's not all. Danbooru offers several meta-tags that let you further refine your search, allowing you to query on things like width, height, score, uploader, date, and more. Consult the <a href="/help/cheatsheet">cheat sheet</a> for a complete list of what you can do.</p>
  </div>
  
  <div class="section">
    <h4>Tag List</h4>
    <p>In both the listing page and the show page you'll notice a list of tag links with characters next to them. Here's an explanation of what the links are:</p>
    <dl>
      <dt>?</dt>
      <dd>This links to the wiki page for the tag. If the tag is an <a href="/help/artists">artist</a> type, then you'll be redirected to the artist page.</dd>
      
      <dt>+</dt>
      <dd>This adds the tag to the current search.</dd>

      <dt>&ndash;</dt>
      <dd>This adds the negated tag to the current search.</dd>
            
      <dt>950</dt>
      <dd>The number next to the tag represents how many posts there are. This isn't always the total number of posts for that tag. If you're searching for a combination of tags, this will be the number of posts that have the tag AND the current tag query. If you're not searching for anything, this will be the number of posts found within the last twenty-four hours.</dd>
      
      <dt>Color</dt>
      <dd>Some tag links may be colored green, purple, or red. Green means the tag represents a character. Purple means the tag represents a copyright (things like anime, manag, games, or novels). Red means the tag represents an artist.</dd>
    </dl>
    <p>When you're not searching for a tag, by default the tag list will show the most popular tags within the last three days. When you are searching for tags, the tag list will show related tags, sorted by relevancy.</p>
  </div>
  
  <div class="section">
    <a name="mode-menu"></a>
    <h4>Mode Menu</h4>
    <p>In the main listing page, you'll notice a menu labeled "Mode" in the sidebar. This menu lets you make several changes without ever leaving the listing page. Simply select an option and whenever you click on a thumbnail, the action will be performed in the background.</p>
    
    <dl>
      <dt>View Posts</dt>
      <dd>This is the default mode. Whenever you click on a thumbnail, you'll go to that post.</dd>

      <dt>Edit Posts</dt>
      <dd>Whenever you click on a thumbnail, you'll get a JavaScript prompt. Here you can easily change the post's tags, and the site will update the post for you in the background.</dd>

      <dt>Add to Favorites</dt>
      <dd>Whenever you click on a thumbnail, that post will be added to your list of favorites.</dd>

      <dt>Vote Up</dt>
      <dd>Whenever you click on a thumbnail, that post will be voted up.</dd>

      <dt>Vote Down</dt>
      <dd>Whenever you click on a thumbnail, that post will be voted down.</dd>

      <dt>Rate Safe</dt>
      <dd>Whenever you click on a thumbnail, that post will be rated safe.</dd>

      <dt>Rate Questionable</dt>
      <dd>Whenever you click on a thumbnail, that post will be rated questionable.</dd>

      <dt>Rate Explicit</dt>
      <dd>Whenever you click on a thumbnail, that post will be rated explicit.</dd>

      <dt>Flag Post</dt>
      <dd>Whenever you click on a thumbnail, that post will be flagged for deletion.</dd>
      
      <dt>Lock Rating</dt>
      <dd>Whenever you click on a thumbnail, that post will be rating locked (no one will be able to change the rating).</dd>
      
      <dt>Lock Notes</dt>
      <dd>Whenever you click on a thumbnail, that post will be note locked (no one will be able to edit notes for that post).</dd>

      <dt>Edit Tag Script</dt>
      <dd>Go <a href="/help/tag_scripts">here</a> for details on tag scripts.</dd>
      
      <dt>Apply Tag Script</dt>
      <dd>Whenever you click on a thumbnail, the current tag script will be applied to the post.</dd>
    </dl>
  </div>

  <div class="section">
    <h4>Borders</h4>
    <p>In the listing page, you will notice that some thumbnails have a border. The meaning of this border depends on the color.</p>

    <dl>
      <dt>Red</dt>
      <dd>The post was flagged for deletion.</dd>

      <dt>Blue</dt>
      <dd>The post is pending moderator approval.</dd>

      <dt>Green</dt>
      <dd>The post has child posts.</dd>
      
      <td>Yellow</td>
      <dd>The post has a parent.</dd>
    </dl>
  </div>
</div>

<?php $this->content_for("subnavbar", function() { ?>
  <li><?= $this->link_to("Help", "#index") ?></li>
<?php }) ?>