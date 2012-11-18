<div id="post-list">
    <div class="sidebar">
      <?= $this->render_partial('search') ?>
      <?php if (CONFIG()->can_see_ads.call(current_user())) : ?>
        <?= CONFIG()->ad_code_index_side ?>
      <?php endif ?>
      <div style="margin-bottom: 1em;" id="mode-box">
        <h5><?= $this->t('post_sim_mode') ?></h5>
        <form onsubmit="return false;" action="">
          <div>
            <select name="mode" id="mode" onchange="PostModeMenu.change()" onkeyup="PostModeMenu.change()" style="width: 13em;">
              <option value="view"><?= $this->t('post_sim_view') ?></option>
              <option value="reparent"><?= $this->t('post_sim_reparent') ?></option>
              <option value="dupe"><?= $this->t('post_sim_dupe') ?></option>
              <option value="edit"><?= $this->t('post_sim_edit') ?></option>
<!--              <option value="rating-s">Rate Safe</option>
              <option value="rating-q">Rate Questionable</option>
              <option value="rating-e">Rate Explicit</option>
              <?php if (current_user()->is_privileged_or_higher?) : ?>
                <option value="lock-rating">Lock Rating</option>
                <option value="lock-note">Lock Notes</option>
              <?php endif ?>-->
              <option value="flag"><?= $this->t('post_sim_flag') ?></option>
              <option value="apply-tag-script"><?= $this->t('post_sim_script') ?></option>
            </select>
          </div>
        </form>
      </div>

      <?= $this->render_partial('tag_script') ?>
      <?= $this->render_partial('blacklists') ?>

      <div>
        <h5><?= $this->t('post_sim_services') ?></h5>
        <ul>
          <li> <?= link_to $this->t('post_sim_services_text'), 'post#similar', 'params' => 'params'.merge({'services' => 'all'}) ?>
          <?php CONFIG()->image_service_list.map do |service, server| ?>
          <li>
            <span class="service-link<?= " service-active" if $this->services.find { |s| s == service }?>">
              <?= image_tag(get_service_icon(service), 'class' => 'service-icon', 'id' => 'list') ?>
              <?= link_to "#{service}", 'post#similar', 'params' => 'params'.merge({'services' => 'service'}) ?>
              <?php if ($this->errors[server]) : ?>
                <?= $this->t('post_sim_down') ?>
                <!-- <?= $this->errors[server][:message] ?> -->
              <?php endif ?>
            </span>
          <?php end ?>
        </ul>
      </div>
      <div>
        <h5><?= $this->t('post_sim_options') ?></h5>
        <ul>
          <li><?= link_to ($this->params()->forcegray ? $this->t('post_sim_color') : $this->t('post_sim_gray')), params.merge({ :forcegray => ($this->params()->forcegray ? 0 : 1) }) ?>
          <?php unless $this->params()->threshold ?>
          <li><?= link_to $this->t('post_sim_more'), params.merge({ 'threshold' => '0' }) ?></li>
          <?php end ?>
          <?php if ($this->params()->url) : ?>
          <li>
          <?= link_to $this->t('post_sim_upload'), 'post#upload',
                  :url => ($this->params()->full_url or $this->params()->url),
                  :tags => $this->params()->tags,
                  :rating => $this->params()->rating,
                  :parent => $this->params()->parent
          ?>
          </li>
          <?php endif ?>
        </ul>
      </div>
    </div>
    <?php if ($this->initial) : ?>
      <div id="duplicate">
        <?= $this->t('post_sim_dupe_text') ?>
        <?= $this->t('post_sim_dupe_text2') ?><?= link_to "duplicate post guidelines", 'wiki#show', 'title' => 'duplicate post_guidelines' ?><?= $this->t('post_sim_dupe_period') ?>
        <ul>
        <li>
          <?= $this->t('post_sim_dupe_text3') ?>
          <?= link_to_function( "reparent", "$('mode').value = 'reparent'; PostModeMenu.change();"); ?>
          <?= $this->t('post_sim_dupe_text4') ?>
        <li>
          <?= $this->t('post_sim_dupe_text5') ?>
          <?= link_to_function( $this->t('post_sim_dupe_text6'), "$('mode').value = 'dupe'; PostModeMenu.change();"); ?>.
        <li>
          <form action=<?= url_for('action' => 'destroy', 'name' => 'destroy') ?> id="destroy" method="post">
            <?= hidden_field_tag "id", $this->params()->id, 'id' => 'destroy_id' ?>
            <?= hidden_field_tag "reason", "duplicate" ?>
            <?= $this->t('post_sim_dupe_text7') ?>
            <?= link_to_function( $this->t('post_sim_dupe_text8'), nil) do |page| page.call "$('destroy').submit" end ?>.
          </form>
        </ul>
        <div id="blacklisted-notice" style="display: none;">
          <?= $this->t('post_sim_dupe_text9') ?><b><?= $this->t('post_sim_dupe_text10') ?></b><?= $this->t('post_sim_dupe_text11') ?>
        </div>
      </div>
    <?php endif ?>
    <div class="content">
      <div id="quick-edit" style="display: none; margin-bottom: 1em;">
        <h4><?= $this->t('post_sim_edit_tags') ?></h4>
        <?= $this->form_tag('action' => 'update', function() { ?>
          <?= hidden_field_tag "id", "" ?>
          <?= hidden_field_tag "post[old_tags]", "" ?>
          <?= text_area_tag "post[tags]", "", 'size' => '60x2', 'id' => 'post_tags' ?>
          <?= submit_tag $this->t('post_sim_update') ?>
          <?= tag(:input, 'type' => 'button', :value => $this->t('post_sim_cancel'), 'class' => 'cancel') ?>
        <?php }) ?>
      </div>

      <?php unless $this->initial ?>
      <?= $this->form_tag({'post#similar'}, 'multipart' => 'true', 'id' => 'similar-form', function(){ ?>
        <input name="forcegray" type="hidden" value="<?= $this->h($this->$this->params()->forcegray) ?>">
        <input name="services" type="hidden" value="<?= $this->h($this->$this->params()->services) ?>">
        <input name="threshold" type="hidden" value="<?= $this->h($this->$this->params()->threshold) ?>">


        <table class="form">
          <tfoot>
            <tr>
              <td colspan="2"><?= submit_tag $this->t('post_search'), 'tabindex' => '3', 'accesskey' => 's' ?></td>
            </tr>
          </tfoot>
          <tbody>
            <tr>
              <th>
                <label for="url"><?= $this->t('post_source') ?></label>
              </th>
              <td>
                <input id="url" name="url" size="50" type="text" tabindex="1" value="<?= $this->h($this->params()->url) ?>">
              </td>
            </tr>
            <tr>
              <th width="20%"><label for="post_file"><?= $this->t('post_file') ?></label></th>
              <td width="80%"><input id="file" name="file" size="50" tabindex="2" type="file"></td>
            </tr>
          </tbody>
        </table>
      <?php end ?>
      <?php end ?>

      <?php if (not $this->posts.nil?) : ?>
      <?= $this->render_partial('posts'), :locals => {:posts => $this->posts} ?>
      <?php endif ?>

      <div id="paginator"></div>

      <?php if ($this->params()->full_url) : ?>
      <img src="<?= $this->params()->full_url ?>"/>
      <?php }) ?>
    </div>
</div>
<?= $this->content_for('post_cookie_javascripts', function() { ?>
<script type="text/javascript">
  <?php unless $this->initial ?>
  $("url").focus();
  <?php endif ?>

  <?php if ($this->params()->id) : ?>
  // for post_mode_menu.js:click
  id=<?= $this->params()->id ?>;
  <?php endif ?>

  post_quick_edit = new PostQuickEdit($("quick-edit"));

  PostModeMenu.init()

  var form = $("similar-form");
  if(form && SimilarWithThumbnailing)
    new SimilarWithThumbnailing(form);
</script>
<?php }) ?>

<?= $this->render_partial('footer') ?>
