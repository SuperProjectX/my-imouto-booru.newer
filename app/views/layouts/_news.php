<?php if (!CONFIG()->show_news_bar) return; ?>
<div id="news-ticker" style="display: none">
  <ul>
    <li>We're testing new MyImouto. Please report errors <?= $this->link_to('here', 'http://code.google.com/p/my-imouto-booru/issues/list') ?>.</li>
  </ul>

  <a href="#" id="close-news-ticker-link"><?= $this->t('news_ticker_close') ?></a>
</div>
<script type="text/javascript">
  if (Cookie.get('hide-news-ticker') != '1') {
    $('news-ticker').show();
    $('close-news-ticker-link').observe('click', function(e) {
      $('news-ticker').hide();
      Cookie.put('hide-news-ticker', '1', 7);
      return false;
    })
  }
</script>
