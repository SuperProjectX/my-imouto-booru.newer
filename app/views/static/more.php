<div id="static-more">
  <div id="title"><h2><?= $this->link_to($this->image_tag('logo_small.png', ['alt' => CONFIG()->app_name, 'size' => '484x75', 'id' => 'logo']), 'root') ?></h2></div>
  <div>
    <ul>
      <li><h4><?= $this->t('static_t1') ?></h4></li>
      <li><?= $this->link_to($this->t('static1'), 'help#posts') ?></li>
      <li><?= $this->link_to($this->t('static2'), 'post#atom') ?></li>
      <li><?= $this->link_to($this->t('static3'), 'post#index') ?></li>
      <li><?= $this->link_to($this->t('static4'), 'post#browse') ?></li>
      <li><?= $this->link_to($this->t('static5'), 'post#similar') ?></li>
      <li><?= $this->link_to($this->t('static6'), 'post#popular_recent') ?></li>
      <li><?= $this->link_to($this->t('static7'), 'post#popular_by_day') ?></li>
      <li><?= $this->link_to($this->t('static8'), 'post#popular_by_week') ?></li>
      <li><?= $this->link_to($this->t('static9'), 'post#popular_by_month') ?></li>
      <li><?= $this->link_to($this->t('static10'), 'post#random') ?></li>
      <li><?= $this->link_to($this->t('static11'), 'post#deleted_index') ?></li>
      <li><?= $this->link_to($this->t('static12'), 'history#index') ?></li>
      <li><?= $this->link_to($this->t('static13'), 'post#upload') ?></li>
    </ul>
    <ul>
      <li><h4><?= $this->t('static_t2') ?></h4></li>
      <li><?= $this->link_to($this->t('static14'), 'help#bookmarklet') ?></li>
      <li><?= $this->link_to($this->t('static15'), 'http://unbuffered.info/danbooruup') ?></li>
      <li><?= $this->link_to($this->t('static16'), 'help#api') ?></li>
    </ul>
    <?php if (CONFIG()->enable_reporting) : ?>
      <ul>
        <li><h4><?= $this->t('static_t3') ?></h4></li>
        <li><?= $this->link_to($this->t('static17'), 'report#note_updates') ?></li>
        <li><?= $this->link_to($this->t('static18'), 'report#tag_updates') ?></li>
        <li><?= $this->link_to($this->t('static19'), 'report#wiki_updates') ?></li>
        <li><?= $this->link_to($this->t('static20'), 'report#post_uploads') ?></li>
        <li><?= $this->link_to($this->t('static21'), 'report#votes') ?></li>
        <li><?= $this->link_to($this->t('static22'), 'job_task#index') ?></li>
      </ul>
    <?php endif ?>
  </div>
  <div>
    <ul>
      <li><h4><?= $this->t('static_t4') ?></h4></li>
      <li><?= $this->link_to($this->t('static23'), 'help#tags') ?></li>
      <li><?= $this->link_to($this->t('static24'), 'tag_alias#index') ?></li>
      <li><?= $this->link_to($this->t('static25'), 'tag_implication#index') ?></li>
      <li><?= $this->link_to($this->t('static26'), 'tag#edit') ?></li>
      <li><?= $this->link_to($this->t('static27'), 'tag#cloud') ?></li>
      <li><?= $this->link_to($this->t('static28'), 'tag#index') ?></li>
      <li><?= $this->link_to($this->t('static29'), 'tag#mass_edit') ?></li>
    </ul>
    <ul>
      <li><h4><?= $this->t('static_t5') ?></h4></li>
      <li><?= $this->link_to($this->t('static30'), 'help#notes') ?></li>
      <li><?= $this->link_to($this->t('static31'), 'note#history') ?></li>
      <li><?= $this->link_to($this->t('static32'), 'note#index') ?></li>
    </ul>
    <?php if (CONFIG()->enable_artists) : ?>
      <ul>
        <li><h4><?= $this->t('static_t6') ?></h4></li>
        <li><?= $this->link_to($this->t('static33'), 'help#artists') ?></li>
        <li><?= $this->link_to($this->t('static34'), 'artist#index') ?></li>
      </ul>
    <?php endif ?>
    <ul>
      <li><h4><?= $this->t('static_t7') ?></h4></li>
      <li><?= $this->link_to($this->t('static35'), 'help#pools') ?></li>
      <li><?= $this->link_to($this->t('static36'), 'pool#index') ?></li>
    </ul>
  </div>
  <div>
    <ul>
      <li><h4><?= $this->t('static_t8') ?></h4></li>
      <li><?= $this->link_to($this->t('static37'), 'help#comments') ?></li>
      <li><?= $this->link_to($this->t('static38'), 'comment#index') ?></li>
      <li><?= $this->link_to($this->t('static39'), 'comment#moderate') ?></li>
    </ul>
    <ul>
      <li><h4><?= $this->t('static_t9') ?></h4></li>
      <li><?= $this->link_to($this->t('static40'), 'help#forum') ?></li>
      <li><?= $this->link_to($this->t('static41'), 'forum#index') ?></li>
      <li><?= $this->link_to($this->t('static42'), 'inline#index') ?></li>
    </ul>
    <ul>
      <li><h4><?= $this->t('static_t10') ?></h4></li>
      <li><?= $this->link_to($this->t('static43'), 'help#wiki') ?></li>
      <li><?= $this->link_to($this->t('static44'), 'wiki#index') ?></li>
      <li><?= $this->link_to($this->t('static45'), 'wiki#history') ?></li>
    </ul>
    <ul>
      <li><h4><?= $this->t('static_t11') ?></h4></li>
      <li><?= $this->link_to($this->t('static46'), 'http://code.google.com/p/my-imouto-booru/') ?></li>
      <li><?= $this->link_to($this->t('static47'), 'http://trac.donmai.us') ?></li>
    </ul>
  </div>
  <div>
    <ul>
      <li><h4><?= $this->t('static_t12') ?></h4></li>
      <?php if (!current_user()->id) : ?>
        <li><?= $this->link_to($this->t('static48'), 'user#login') ?></li>
        <li><?= $this->link_to($this->t('static49'), 'user#signup') ?></li>
        <li><?= $this->link_to($this->t('static50'), 'user#reset_password') ?></li>
      <?php else: ?>
        <li><?= $this->link_to($this->t('static51'), 'user#home') ?></li>
        <li><?= $this->link_to($this->t('static52'), 'user#logout') ?></li>
        <li><?= $this->link_to($this->t('static53'), array('user#show', 'id' => current_user()->id)) ?></li>
        <li><?= $this->link_to($this->t('static54'), 'user_record#index') ?></li>
        <li><?= $this->link_to($this->t('static55'), 'user#edit') ?></li>
        <li><?= $this->link_to($this->t('static56'), 'user#show_blocked_users') ?></li>
        <li><?= $this->link_to($this->t('static57'), 'user#change_password') ?></li>
        <li><?= $this->link_to($this->t('static58'), 'user#invites') ?></li>
      <?php endif ?>
      <li><?= $this->link_to($this->t('static59'), 'help#users') ?></li>
      <li><?= $this->link_to($this->t('static60'), 'user#index') ?></li>
      <li><?= $this->link_to($this->t('static61'), 'static#terms_of_service') ?></li>
    </ul>
    <?php if (current_user()->is_admin_or_higher()) : ?>
      <ul>
        <li><h4><?= $this->t('static_t13') ?></h4></li>
        <li><?= $this->link_to($this->t('static62'), 'admin#edit_user') ?>
        <li><?= $this->link_to($this->t('static63'), 'admin#reset_password') ?>
      </ul>
    <?php endif ?>
  </div>
</div>
