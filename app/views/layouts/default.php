<!DOCTYPE html>
<html class="action-<?= $this->params()->controller ?> action-<?= $this->params()->controller ?>-<?= $this->params()->action ?> hide-advanced-editing">
<head>
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
  <title><?= $this->page_title ?></title>
  <meta name="description" content="<?= CONFIG()->app_name ?>">
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
  <link rel="top" title="<?= CONFIG()->app_name ?>" href="/">
  <?php # The javascript-hide class is used to hide elements (eg. blacklisted posts) from JavaScript. ?>
  <script type="text/javascript">
    var css = ".javascript-hide { display: none !important; }";
    var style = document.createElement("style"); style.type = "text/css";
    if(style.styleSheet) // IE
      style.styleSheet.cssText = css;
    else
      style.appendChild(document.createTextNode(css));
    document.getElementsByTagName("head")[0].appendChild(style);
  </script>

  <?= $this->yield('html_header') ?>
  <?= $this->auto_discovery_link_tag('atom', 'post#atom', array('tags' => $this->params()->tags)) ?>
  <?= $this->stylesheet_link_tag('application') ?>

  <script type="text/javascript" src="/javascripts/application.js"></script>
  <script type="text/javascript" src="/javascripts/legacy.js"></script>
  <!--[if lt IE 8]>
  <script src="/IE8.js" type="text/javascript"></script>
  <![endif]-->
  <?php // iTODO: echo $this->tag('link', array('rel' => 'search', 'type' => 'Mime':'OPENSEARCH', 'href' => 'opensearch_path'('xml'), 'title' => 'CONFIG'()->app_name) ?>
  <?= CONFIG()->custom_html_headers ?>
  <!--[if lt IE 7]>
    <style type="text/css">
      body div#post-view > div#right-col > div > div#note-container > div.note-body {
        overflow: visible;
      }
    </style>
    <script src="<?= $this->request()->protocol() ?>ie7-js.googlecode.com/svn/trunk/lib/IE7.js" type="text/javascript"></script>
  <![endif]-->
  <?php // echo csrf_meta_tag ?>
</head>
<body>
  <?= $this->render_partial('layouts/news') ?>
  <div id="header">
    <div id="title"><h2 id="site-title"><?= $this->image_tag('logo_small.png', array('alt' => CONFIG()->app_name, 'size' => '484x75', 'id' => 'logo')) ?><span><?= $this->tag_header($this->params()->tags) ?></span></h2></div>
    <?= $this->render_partial('layouts/menu') ?>
  </div>
  <?= $this->render_partial('layouts/login') ?>

  <?php if (CONFIG()->server_host == "yande.re") : ?>
    <div style="display: none;">Danbooru-based image board with a specialization in high-quality images.</div>
  <?php endif ?>

  <!--[if lt IE 7]>
  <div style="display: none;" id="old-browser"><?= $this->t('old_browser') ?>
    <?= $this->t('old_browser2') ?>
    <a href="http://www.mozilla.com/firefox/">Firefox</a>,
    <a href="http://www.opera.com/">Opera</a>,
    <a href="http://www.microsoft.com/windows/internet-explorer/download-ie.aspx">Internet Explorer</a>.
    <div style="text-align: right;" id="old-browser-hide">
      <a href="#" onclick='$("old-browser").hide(); Cookie.put("hide-ie-nag", "1");'><?= $this->t('old_browser3') ?></a>
    </div>
  </div>
  <![endif]-->
  <?= $this->render_partial('layouts/notice') ?>

  <div class="blocked" id="block-reason" style="display: none;">
  </div>

  <div id="content">
    <?= $this->yield() ?>
    <?php if ($this->content_for('subnavbar')) : ?>
      <div class="footer">
        <?= $this->yield('above_footer') ?>
        <ul class="flat-list" id="subnavbar">
          <?= $this->yield('subnavbar') ?>
        </ul>
      </div>
    <?php endif ?>
  </div>

  <script type="text/javascript">
    InitTextAreas();
    InitAdvancedEditing();
    Post.InitBrowserLinks();
    if(TagCompletion)
      TagCompletion.init(<?php // echo json_encode(Tag::get_summary_version()) ?>);
  </script>

  <!--[if lt IE 7]>
    <script type="text/javascript">
      if(Cookie.get("hide-ie-nag") != "1")
        $("old-browser").show();
    </script>
  <![endif]-->
  
  <?= $this->yield('post_cookie_javascripts') ?>
  <?php if (CONFIG()->ga_tracking_id) echo $this->render_partial('layouts/ga') ?>
</body>
</html>
