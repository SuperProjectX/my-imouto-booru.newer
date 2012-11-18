<?= $this->content_for('post_cookie_javascripts', function() { ?>
<script type="text/javascript">
  var def = [];

  var account_menu = [];
  <?php if (current_user()->is_anonymous()) : ?> 
    account_menu.push(<?= $this->make_sub_item($this->t('layout_login'), "user#login", array('level' => 'member', 'class_names' => array("login-button"))) ?>);
    account_menu.push(<?= $this->make_sub_item($this->t('layout_reset'), 'user#reset_password') ?>);
  <?php else: ?> 
    account_menu.push(<?= $this->make_sub_item($this->t('layout_logout'), array('user#logout', 'from' => $this->request()->fullpath())) ?>);

    if(Cookie.get("user_id"))
    {
      var profile_item = <?= $this->make_sub_item($this->t('layout_profile'), array('user#show', 'id' => current_user()->id)) ?>;
      account_menu.push(profile_item);
    }

    account_menu.push(<?= $this->make_sub_item($this->t('layout_mail'), 'dmail#inbox') ?>);

    if(Cookie.get("login"))
    {
      var favorites_item = <?= $this->make_sub_item($this->t('layout_fav'), array('post#index', 'tags' => "order:vote vote:3:")) ?>;
      if(User.get_use_browser())
        favorites_item.dest = "/post/browse#/order:vote vote:3:" + Cookie.get("login");
      else
        favorites_item.dest += Cookie.get("login")
      account_menu.push(favorites_item);
    }

    account_menu.push(<?= $this->make_sub_item($this->t('layout_settings'), 'user#edit') ?>);
    account_menu.push(<?= $this->make_sub_item($this->t('layout_change_pwd'), 'user#change_password') ?>);
  <?php endif ?> 

  def.push(<?= $this->make_main_item($this->t('layout_account'), 'user#home', array('name' => "my_account", 'level' => 'member')) ?>);
  def[def.length-1].sub = account_menu;

  var posts_menu = [];
  posts_menu.push(<?= $this->make_sub_item($this->t('layout_view_posts'), 'post#index') ?>);
  posts_menu.push(<?= $this->make_sub_item($this->t('layout_search_posts'), 'post#index') ?>);
  posts_menu[posts_menu.length-1].func = ShowPostSearch;
  posts_menu.push(<?= $this->make_sub_item($this->t('layout_upload'), 'post#upload') ?>);
  posts_menu.push(<?= $this->make_sub_item($this->t('layout_random'), array('post#', 'tags' => "order:random")) ?>);
  posts_menu.push(<?= $this->make_sub_item($this->t('layout_popular'), 'post#popular_recent') ?>);
  posts_menu.push(<?= $this->make_sub_item($this->t('layout_img_search'), 'post#similar') ?>);
  posts_menu.push(<?= $this->make_sub_item($this->t('layout_history'), 'history#index') ?>);
  <?php if (current_user()->is_contributor_or_higher()) : ?> 
    posts_menu.push(<?= $this->make_sub_item($this->t('layout_batch'), 'batch#') ?>);
  <?php endif ?> 
  <?php if (current_user()->is_janitor_or_higher()) : ?> 
    posts_menu.push(<?= $this->make_sub_item($this->t('layout_moderate'), 'post#moderate') ?>);

    var posts_flagged = Cookie.get("posts_flagged");
    if (posts_flagged && parseInt(posts_flagged) > "0") {
      posts_menu[posts_menu.length-1].label += " (" + posts_flagged + ")";
      posts_menu[posts_menu.length-1].class_names = ["bolded"];
    }
  <?php endif ?> 
  <?php if (current_user()->is_admin()) : ?> 
    posts_menu.push(<?= $this->make_sub_item($this->t('layout_post_import'), 'post#import') ?>);
  <?php endif ?> 
  def.push(<?= $this->make_main_item($this->t('layout_posts'), 'post#index', array('name' => "posts")) ?>);
  def[def.length-1].sub = posts_menu;

  var comments_menu = [];
  comments_menu.push(<?= $this->make_sub_item($this->t('layout_view_comments'), 'comment#index') ?>);
  comments_menu.push(<?= $this->make_sub_item($this->t('layout_search_comments'), 'comment#search') ?>);
  comments_menu[comments_menu.length-1].func = ShowCommentSearch;
  <?php if (current_user()->is_janitor_or_higher()) : ?>
  comments_menu.push(<?= $this->make_sub_item($this->t('layout_moderate'), 'comment#moderate') ?>);
  <?php endif ?>
  def.push(<?= $this->make_main_item($this->t('layout_comments'), 'comment#index', array('html_id' => "comments-link", 'name' => "comments")) ?>);
  if (Cookie.get("comments_updated") == "1") {
    def[def.length-1].class_names = ["bolded"];
  }
  def[def.length-1].sub = comments_menu;

  var notes_menu = [];
  notes_menu.push(<?= $this->make_sub_item($this->t('layout_view_notes'), 'note#index') ?>);
  notes_menu.push(<?= $this->make_sub_item($this->t('layout_search_notes'), 'note#search') ?>);
  notes_menu[notes_menu.length-1].func = ShowNoteSearch;
  notes_menu.push(<?= $this->make_sub_item($this->t('layout_requests'), array('post#index', 'tags' => "translation_request")) ?>);
  def.push(<?= $this->make_main_item($this->t('layout_notes'), 'note#index', array('name' => "notes")) ?>);
  def[def.length-1].sub = notes_menu;

  <?php if (CONFIG()->enable_artists) : ?> 
  var artists_menu = [];
  artists_menu.push(<?= $this->make_sub_item($this->t('layout_view_artists'), 'artist#index') ?>);
  artists_menu.push(<?= $this->make_sub_item($this->t('layout_search_artists'), 'artist#index') ?>);
  artists_menu[artists_menu.length-1].func = ShowArtistSearch;
  artists_menu.push(<?= $this->make_sub_item($this->t('layout_create'), 'artist#create') ?>);
  def.push(<?= $this->make_main_item($this->t('layout_artists'), 'artist#index', array('name' => 'artists')) ?>);
  def[def.length-1].sub = artists_menu;
  <?php endif ?> 

  var tags_menu = [];
  tags_menu.push(<?= $this->make_sub_item($this->t('layout_view_tags'), 'tag#index') ?>);
  tags_menu.push(<?= $this->make_sub_item($this->t('layout_search_tags'), 'tag#index') ?>);
  tags_menu[tags_menu.length-1].func = ShowTagSearch;
  tags_menu.push(<?= $this->make_sub_item($this->t('layout_popular'), 'tag#popular_by_day') ?>);
  tags_menu.push(<?= $this->make_sub_item($this->t('layout_aliases'), 'tag_alias#index') ?>);
  tags_menu.push(<?= $this->make_sub_item($this->t('layout_imp'), 'tag_implication#index') ?>);
  <?php if (current_user()->is_mod_or_higher()) : ?>
    tags_menu.push(<?= $this->make_sub_item($this->t('layout_mass_edit'), 'tag#mass_edit') ?>);
  <?php endif ?>
  tags_menu.push(<?= $this->make_sub_item($this->t('layout_edit'), 'tag#edit') ?>);
  <?php if (current_user()->is_mod_or_higher() && CONFIG()->enable_tag_fix_count) : ?>
    tags_menu.push(<?= $this->make_sub_item($this->t('layout_fix_count'), 'tag#fix_count') ?>);
  <?php endif ?>
  def.push(<?= $this->make_main_item($this->t('layout_tags'), array('tag#index', 'order' => "date"), array('name' => 'tags')) ?>);
  def[def.length-1].sub = tags_menu;

  var pools_menu = [];
  pools_menu.push(<?= $this->make_sub_item($this->t('layout_view_pools'), 'pool#index') ?>);
  pools_menu.push(<?= $this->make_sub_item($this->t('layout_search_pools'), 'pool#index') ?>);
  pools_menu[pools_menu.length-1].func = ShowPoolSearch;
  pools_menu.push(<?= $this->make_sub_item($this->t('layout_create_pool'), 'pool#create') ?>);
  def.push(<?= $this->make_main_item($this->t('layout_pools'), 'pool#index', array('name' => 'pools')) ?>);
  def[def.length-1].sub = pools_menu;

  var wiki_menu = [];
  wiki_menu.push(<?= $this->make_sub_item($this->t('layout_wiki_index'), 'wiki#index') ?>);
  wiki_menu.push(<?= $this->make_sub_item($this->t('layout_search_wiki'), 'wiki#index') ?>);
  wiki_menu[wiki_menu.length-1].func = ShowWikiSearch;
  wiki_menu.push(<?= $this->make_sub_item($this->t('layout_create_wiki'), 'wiki#add') ?>);
  def.push(<?= $this->make_main_item($this->t('layout_wiki'), array('wiki#show', 'title' => "help:home"), array('name' => 'wiki')) ?>);
  def[def.length-1].sub = wiki_menu;

  var forum_menu = [];
  forum_menu.push(<?= $this->make_sub_item($this->t('layout_forum_topics'), 'forum#index') ?>);
  forum_menu.push(<?= $this->make_sub_item($this->t('layout_search_forum'), 'forum#index') ?>);
  forum_menu[forum_menu.length-1].func = ShowForumSearch;
  forum_menu.push(<?= $this->make_sub_item($this->t('layout_new_topic'), 'forum#blank') ?>);
  def.push(<?= $this->make_main_item($this->t('layout_forum'), 'forum#index', array('name' => "forum", 'html_id' => "forum-link")) ?>);
  if (Cookie.get("forum_updated") == "1") {
    def[def.length-1].class_names = ["bolded"];
  }
  def[def.length-1].sub = forum_menu;

  var help_menu = [];
  <?php $current_controller = Rails::application()->dispatcher()->parameters()->controller ?>
  <?php if (in_array($current_controller, array('post', 'comment', 'note', 'artist', 'tag', 'wiki', 'pool', 'forum'))) : ?>
    help_menu.push(<?= $this->make_sub_item(ucfirst($current_controller) . ' ' . $this->t('layout_help'), 'help#'.$this->get_help_action_for_controller($current_controller)) ?>);
  <?php endif ?>
  help_menu.push(<?= $this->make_sub_item($this->t('layout_site_help'), 'help#') ?>);
  def.push(<?= $this->make_main_item($this->t('layout_help'), 'help#', array('name' => 'help')) ?>);
  def[def.length-1].sub = help_menu;

  def.push(<?= $this->make_main_item($this->t('layout_more'), 'static#more', array('name' => 'more')) ?>);
</script>
<?php }) ?>

<div id="main-menu">
  <?php
    foreach ($this->top_menu_items() as $item) {
      $class_names = array();
      $class_names[] = "menu";
      $class_names[] = "top-item-".(isset($item['name']) ? $item['name'] : '');
      $class_names = array_merge($class_names, $item['class_names']);
      $onclick = "";
      if (isset($item['login']))
        $onclick = "onclick='if(!User.run_login_onclick(event)) return false;'";
      ?>
      <?php /* This div must not contain any whitespace nodes.  If it does, FF will return offsetLeft
         incorrectly, causing problems if the menu wraps. */ ?>
      <div <?php if (!empty($item['html_id'])) echo "id='".$item['html_id']."' " ?> class='<?= implode(' ', $class_names) ?>'><a href="<?= $item['dest'] ?>" <?= $onclick ?>><?= $item['label'] ?></a></div>
  <?php } ?>
  <a id="has-mail-notice" class="has-mail" style="display: none;" href="<?= $this->url_for('dmail#inbox') ?>">
    <span><?= $this->t('layout_new_mail') ?></span>
  </a>
  <span id='cn' style="display: none;">
  </span>
</div>

<?= $this->content_for('post_cookie_javascripts', function() { ?>
<script type="text/javascript">
  var main_menu = new MainMenu($("main-menu"), def);
  main_menu.add_forum_posts_to_submenu();
  main_menu.init();
  $('cn').show();
</script>
<?php }) ?>
