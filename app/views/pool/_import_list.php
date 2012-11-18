<?php
  fields = ''.html_safe
  thumbnails = ''.html_safe
  $this->posts.each_with_index do |p, i|
    fields << hidden_field_tag("posts[#{p.id}]", "%05i" % i)
    thumbnails << print_preview(p, ['onclick' => "return removePost(#{p.id})"])
  end
?>
<div style="margin-bottom: 2em;">
  <?= check_box_tag("delete-mode") ?>
  <?= $this->content_tag('label', "Remove posts", ['onclick' => "Element::toggle('delete-mode-help')", 'for' => "delete-mode"] ?>
  <?= $this->content_tag('p', $this->content_tag('em', "When delete mode is enabled, clicking on a thumbnail will remove that post from the import."), ['style' => "display: none;", 'id' => "delete-mode-help"] ?>
</div>
<?= fields ?>
<?= thumbnails ?>
