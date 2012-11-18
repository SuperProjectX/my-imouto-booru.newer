<div>
  <h5><?= $this->t('post_options') ?></h5>
  <ul>
    <li><?= $this->link_to_function($this->t('post_options_edit'), "$('comments').hide(); $('edit').show().scrollTo(); $('post_tags').focus(); Cookie.put('show_defaults_to_edit', 1);") ?></li>
    <!-- <?php //if (!$this->post->is_deleted() && $this->post->image() && $this->post->width && $this->post->width > 700) : ?>
      <li><?php //echo $this->link_to_function($this->t('post_options_resize'), "post->resize_image()") ?></li>
    <?php //endif ?> -->
    <?php if ($this->post->image() && $this->post->can_be_seen_by(current_user())) : ?>
      <?php $file_jpeg = $this->post->get_file_jpeg() ?>
      <?php if ($this->post->use_sample(current_user()) or current_user()->always_resize_images) : ?>
      <li><?php if (!array_key_exists("dakimakura", $this->post->parsed_cached_tags) || current_user()->is_contributor_or_higher());
                    echo $this->link_to($this->t('post_options_view_larger'), $file_jpeg['url'], array(
                      'class' => ($this->post->has_sample() ? "original-file-changed":"original-file-unchanged") . " highres-show",
                      'id' => 'highres-show', 'link_width' => $this->post->width, 'link_height' => $this->post->height))
          ?>
      </li>
      <?php endif ?>
      <li><?php if (array_key_exists("dakimakura", $this->post->parsed_cached_tags) && !current_user()->is_contributor_or_higher()) :
                      $file_sample = $this->post->get_file_sample(current_user());
                      echo $this->link_to(($this->post->has_sample() ? $this->t('post_options_download_larger') : $this->t('post_options_image')) . ' (' . $this->number_to_human_size($file_sample['size']) . ' ' . strtoupper($file_sample['ext']) . ')', $file_sample['url'], array(
                      'class' => $this->post->has_sample() ? "original-file-changed":"original-file-unchanged",
                      'id' => 'highres'));
              else:
                      $this->link_to(($this->post->has_sample() ? $this->t('post_options_download_larger') : $this->t('post_options_image')) . ' (' . $this->number_to_human_size($file_jpeg['size']) . ' ' . strtoupper($file_jpeg['ext']), $file_jpeg['url'], array(
                      'class' => ($this->post->has_sample() ? "original-file-changed":"original-file-unchanged"),
                      'id' => 'highres'));
              endif
          ?>
      </li>
      <?php if ($this->post->has_jpeg()) : ?>
        <?php $file_image = $this->post->get_file_image() ?>
        <?php # If we have a JPEG, the above link was the JPEG.  Link to the PNG here. ?>
        <li><?= $this->link_to($this->t('post_options_download').' '.strtoupper($file_image['ext']).' ('.$this->number_to_human_size($file_image['size']).')', $file_image['url'], array(
                        'class' => 'original-file-unchanged',
                        'id' => 'png'));
                ?>
        </li>
      <?php endif ?>
    <?php endif ?>
    <?php if ($this->post->can_user_delete(current_user())) : ?>
    <li><?= $this->link_to($this->t('post_options_delete'), array('#delete', 'id' => $this->post->id)) ?></li>
    <?php endif ?>
    <?php if ($this->post->is_deleted() && current_user()->is_janitor_or_higher()) : ?>
      <li><?= $this->link_to($this->t('post_options_undelete'), array('#undelete', 'id' => $this->post->id)) ?></li>
    <?php endif ?>
    <?php if (!$this->post->is_flagged() && !$this->post->is_deleted()) : ?>
      <li><?= $this->link_to_function($this->t('post_options_flag'), "Post.flag(".$this->post->id.", function() { window.location.reload(); })", array('level' => 'member')) ?></li>
    <?php endif ?>
    <?php if (!$this->post->is_deleted() && $this->post->image() && !$this->post->is_note_locked()) : ?>
      <li><?= $this->link_to_function($this->t('post_options_tl'), "Note.create(".$this->post->id.")", array('level' => 'member')) ?></li>
    <?php endif ?>
    <li id="add-to-favs"><?= $this->link_to($this->t('post_options_add_fav'), "#") ?></li>
    <li id="remove-from-favs"><?= $this->link_to($this->t('post_options_del_fav'), "#") ?></li>
    <?php if ($this->post->is_pending() && current_user()->is_janitor_or_higher()) : ?>
      <li><?= $this->link_to_function($this->t('post_options_approve'), "if (confirm('".$this->t('post_options_approve_confirm')."')) {Post.approve(".$this->post->id.")") ?></li>
    <?php endif ?>
    <?php if (!$this->post->is_deleted()) : ?>
      <li id="add-to-pool" class="advanced-editing"><a href="#" onclick="new Ajax.Updater('add-to-pool', '/pool/select?post_id=<?= $this->post->id ?>', {asynchronous:true, evalScripts:true, method:'get'}); return false;"><?= $this->t('post_options_add_pool') ?></a></li>
    <?php endif ?>
    <?php if (!$this->post->is_deleted()) : ?>
      <li id="set-avatar"><?= $this->link_to($this->t('post_options_avatar'), array('user#set_avatar', 'id' => $this->post->id)) ?></li>
    <?php endif ?>
    <li><?= $this->link_to($this->t('post_options_history'), array('history#index', 'search' => 'post:'.$this->post->id)) ?></li>
  </ul>
</div>
