<?php if (!$this->post->is_deleted()) : ?>
  <div<?php if (CONFIG()->dblclick_resize_image) echo ' ondblclick="Post.resize_image(); return false;"' ?>>
    <?php if (!$this->post->can_be_seen_by(current_user())) : ?>
      <p><?= $this->t('post_image_text') ?></p>
    <?php elseif ($this->post->image()) : ?>
      <div id="note-container">
        <?php foreach ($this->post->active_notes() as $note) : ?>
          <div class="note-box" style="width: <?= $note->width ?>px; height: <?= $note->height ?>px; top: <?= $note->y ?>px; left: <?= $note->x ?>px;" id="note-box-<?= $note->id ?>">
            <div class="note-corner" id="note-corner-<?= $note->id ?>"></div>
          </div>
           <div class="note-body" id="note-body-<?= $note->id ?>" title="Click to edit"><?= $this->h($note->formatted_body()) ?></div>
        <?php endforeach ?>
      </div>
      <?php
        $file_sample = $this->post->get_file_sample(current_user()); $jpeg = $this->post->get_file_jpeg(current_user());
        echo $this->image_tag($file_sample['url'], array(
                    'alt'          => $this->post->tags,
                    'id'           => 'image',
                    'class'        => 'image',
                    'width'        => $file_sample['width'],
                    'height'       => $file_sample['height'],
                    'large_width'  => $jpeg['width'],
                    'large_height' => $jpeg['height'])); ?>
    <?php elseif ($this->post->flash()) : ?>
      <object width="<?= $this->post->width ?>" height="<?= $this->post->height ?>">
        <param name="movie" value="<?= $this->post->file_url() ?>">
        <embed src="<?= $this->post->file_url() ?>" width="<?= $this->post->width ?>" height="<?= $this->post->height ?>" allowScriptAccess="never"></embed>
      </object>

      <p><?= $this->link_to($this->t('post_flash_dl'), $this->post->file_url()) ?></p>
    <?php else: ?>
      <h2><a href="<?= $this->post->file_url() ?>"><?= $this->t('post_download') ?></a></h2>
      <p><?= $this->t('post_download_text') ?></p>
    <?php endif ?>
  </div>
  <div style="margin-bottom: 1em;">
    <p id="note-count"></p>
    <script type="text/javascript">
      Note.post_id = <?= $this->post->id ?>

      <?php foreach ($this->post->active_notes() as $note) : ?>
        Note.all.push(new Note(<?= $note->id ?>, false, '<?= $this->h($note->body) ?>'))
      <?php endforeach ?>

      Note.updateNoteCount()
      Note.show()

      new WindowDragElement($("image"));

      $("image").observe("click", function(e) { if(!e.stopped) Note.toggle(); }.bindAsEventListener());
    </script>
  </div>
<?php endif ?>

