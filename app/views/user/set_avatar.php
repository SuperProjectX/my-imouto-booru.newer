<!--[if lt IE 9]>
<div style="margin-bottom: 1em;"> Setting an avatar with Internet Explorer will not work.</div>
<![endif]-->

<div id="set-avatar" class="page">
  <div class="avatar-crop">
    <?= $this->image_tag($this->post->sample_url(), ['id' => 'image', 'width' => $this->post->get_sample_width(), 'height' => $this->post->get_sample_height()]) ?>
  </div>

  <?= $this->form_tag([], ['level' => 'member'], function(){ ?>
    <?= $this->hidden_field_tag("post_id", $this->params()->id) ?>
    <?php if ($this->params()->user_id) : ?>
      <?=$this->hidden_field_tag("user_id", $this->params()->user_id) ?>
    <?php endif ?>
    <?=$this->hidden_field_tag("left", 0) ?>
    <?=$this->hidden_field_tag("right", 0) ?>
    <?=$this->hidden_field_tag("top", 0) ?>
    <?=$this->hidden_field_tag("bottom", 0) ?>

    <div width="200px">
      <div id="crop-preview-box">
        <div class="crop-preview-frame" style="width: <?= CONFIG()->avatar_max_width + 10 ?>px; height: <?= CONFIG()->avatar_max_height + 10 ?>px;">
          <div class="crop-preview-frame-inner">
            <div id="crop-preview"></div>
          </div>
        </div>
        <div class="avatar-submit">
          <?= $this->submit_tag($this->t('user_set_avatar')) ?>
        </div>
      </div>
    </div>
  <?php }) ?>

  <script type="text/javascript" charset="utf-8">
  function PositionPreview()
  {
    var box = $("crop-preview-box");
    var image = $("image");
    var image_right_outside = image.viewportOffset().left + image.getWidth();
    var image_right_inside = image_right_outside - box.getWidth();
    var viewport_align = document.viewport.getWidth();
    image_right_outside += 5;
    image_right_inside -= 5;

    /* If the image is narrow enough for us to fit the preview to the right
     * without falling off the screen, put it there. */
    box.style.left = image_right_outside + "px";
    box.style.right = "";
    if(box.viewportOffset().left + box.getWidth() < document.viewport.getWidth() - 20)
      return;

    /* It doesn't fit there.  Either attach the preview to the right edge of the
     * viewport, or to the right edge of the image (if that's always on-screen). */
    box.style.left = "";
    box.style.right = "5px";
    if(box.viewportOffset().left > image_right_inside)
    {
      box.style.left = image_right_inside + "px";
      box.style.right = "";
    }
  }

  PositionPreview();
  Event.observe(window, "resize", function(e) { PositionPreview(); }, false);

  function onEndCrop(coords, dimensions) {
    $("left").value = (coords.x1 / $("image").width).toFixed(4);
    $("right").value = (coords.x2 / $("image").width).toFixed(4);
    $("top").value = (coords.y1 / $("image").height).toFixed(4);
    $("bottom").value = (coords.y2 / $("image").height).toFixed(4);
  }

  // example with a preview of crop results, must have minimumm dimensions
  var width = $("image").width;
  var height = $("image").height;
  var options =
  {
    displayOnInit: true,
    onEndCrop: onEndCrop,
    previewWrap: 'crop-preview',
    minWidth: 1,
    minHeight: 1,

    resizePreview: function(dim)
    {
      var max_width = <?= CONFIG()->avatar_max_width ?>;
      var max_height = <?= CONFIG()->avatar_max_height ?>;
      var width = dim.x;
      var height = dim.y;

      if(width < max_width) { var scale = max_width / width; width *= scale; height *= scale; }
      if(height < max_height) { var scale = max_height / height; width *= scale; height *= scale; }
      if(width > max_width) { var scale = max_width / width; width *= scale; height *= scale; }
      if(height > max_height) { var scale = max_height / height; width *= scale; height *= scale; }

      return { x: Math.round(width), y: Math.round(height) }
    }
  }

  <?php if ($this->user->has_avatar() && $this->post == $this->user->avatar_post) : ?>
    options.onloadCoords = { x1: <?= $this->user->avatar_left ?> * width, y1: <?= $this->user->avatar_top ?> * height,
      x2: <?= $this->user->avatar_right ?> * width, y2: <?= $this->user->avatar_bottom ?> * height }
  <?php else: ?>
    /* Default to a square selection. */
    if(width < height)
      options.onloadCoords = { x1: width/4, y1: width/4, x2: width*2/4, y2: width*2/4 }
    else
      options.onloadCoords = { x1: height/4, y1: height/4, x2: height*2/4, y2: height*2/4 }
  <?php endif ?>

  new Cropper.ImgWithPreview("image", options)
  </script>
</div>
