<div id="static-index">
  <h1 id="static-index-header"><?= $this->link_to(CONFIG()->app_name, "/") ?></h1>
  <div style="margin-bottom: 1em;" id="links">
    <?= $this->link_to($this->t('static_posts'), 'post#index', array('title' => $this->t('static_posts_text'))) ?>
    <?= $this->link_to($this->t('static_comments'), 'comment#index', array('title' => $this->t('static_comments_text'))) ?>
    <?= $this->link_to($this->t('static_tags'), 'tag#index', array('title' => $this->t('static_tags_text'))) ?>
    <?= $this->link_to($this->t('static_wiki'), 'wiki#', array('title' => $this->t('static_wiki_text'))) ?>
    <?= $this->link_to('»', 'static#more', array('title' => $this->t('static_more'))) ?>
  </div>
  <div style="margin-bottom: 2em;">
    <?= $this->form_tag('post#index', array('method' => "get"), function() { ?>
      <div>
        <?= $this->text_field_tag("tags", "", array('size' => 30)) ?><br />
        <?= $this->submit_tag($this->t('static_search')) ?>
      </div>
    <?php }) ?>
  </div>
  <?= $this->numbers_to_imoutos($this->post_count) ?>
  <div style="font-size: 80%; margin-bottom: 2em;">
    <p>
      <?php if (current_user()->is_member_or_higher()) : ?>
        <?= $this->mail_to(CONFIG()->admin_contact, "Contact", array('encode' => "javascript")) ?> &ndash;
      <?php endif ?>
      <?= $this->t('static_serve') ?><?= number_format($this->post_count, 0) ?><?= $this->t('static_posts2') ?>&ndash; <?= str_replace('Moebooru', 'MyImouto', $this->t('static_running')) ?><?= CONFIG()->version ?>
      <br />
      <?= $this->t('static_translation') ?>
    </p>
  </div>
</div>
