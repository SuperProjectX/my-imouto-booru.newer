<div>
  <h4>
    <?= $this->link_to_function($this->t('post_image_edit'), "$('comments').hide(); $('edit').show(); $('post_tags').focus(); Cookie.put('show_defaults_to_edit', 1);") ?> |
    <?= $this->link_to_function($this->t('post_image_respond'), "$('edit').hide(); $('comments').show(); Cookie.put('show_defaults_to_edit', 0);") ?>
  </h4>
</div>
