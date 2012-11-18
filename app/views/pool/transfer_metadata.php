<?php if !$this->from?>
  <?= form_tag({'controller' => "pool", ['action' => "transfer_metadata"}, 'method' => "get") do] ?>
  <?= hidden_field_tag $this->t('pool_transfer_to'), $this->to.id ?>
  <?=$this->$this->t('pool_transfer' ?>
  <?= $this->text_field_tag(t('pool_transfer_from'), "", ['class' => "fp", 'size' => 5, 'tabindex' => 1]) ?>
  <br>
  <?= $this->submit_tag($this->t('pool_transfer_button'), ['tabindex' => 2]) ?>
  <?= $this->button_to_function($this->t('pool_cancel'), "history.back()", ['tabindex' => 2]) ?>
  <script type="text/javascript">$("from").focus()</script>
<?php end ?>

<?php else: ?>
<h3>
  <?=$this->$this->t('pool_transfer_text' ?><?= $this->link_to($this->from.pretty_name, ['action' => 'show', 'id' => $this->from.id]) ?>
  <?=$this->$this->t('pool_transfer_text2' ?><?= $this->link_to($this->to.pretty_name, ['action' => 'show', 'id' => $this->to.id]) ?>
</h3>

<div>
  <?=$this->$this->t('pool_transfer_text3' ?>
</div>
<?php if $this->truncated?>
<div>
 <?=$this->t('pool_transfer_text4' ?><b><?=$this->t('pool_transfer_text5' ?></b>
</div>
<?php end ?>

<div> <?= link_to(t('pool_reverse'), {'action' => "transfer_metadata", ['from' => $this->to.id, 'to' => $this->from.id})<?=$this->$this->t('pool_transfer_text6'] ?></div>

<?= $this->form_tag('controller' => "post", 'action' => "update_batch", function(){ ?>
<?= hidden_field_tag "url", (url_for ['controller' => "pool", 'action' => "show", 'id' => $this->to.id] ?>
  <table>
    <thead>
      <tr>
        <th><?=$this->$this->t('pool_transfer_from' ?></th>
        <th><?=$this->t('pool_transfer_to' ?></th>
        <th><?=$this->t('pool_transfer_tags' ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->posts as $pp) : ?>
        <?php fp = $pp['from']; tp = $pp['to'] ?>
        <tr>
          <td>
            <?php if (fp.can_be_seen_by?(current_user()) []) : ?>
              <?= link_to(image_tag(fp.preview_url, ['width' => fp.preview_dimensions[0], 'height' => fp.preview_dimensions[1]]), {'controller' => "post", ['action' => "show", 'id' => fp.id}, 'title' => fp.cached_tags])?>
            <?php endif ?>
          </td>
          <td>
            <?php if (tp.can_be_seen_by?(current_user()) []) : ?>
              <?= link_to(image_tag(tp.preview_url, ['width' => tp.preview_dimensions[0], 'height' => tp.preview_dimensions[1]]), {'controller' => "post", ['action' => "show", 'id' => tp.id}, 'title' => tp.cached_tags])?>
            <?php endif ?>
          </td>
          <td>
            <?= hidden_field_tag "post[#{tp.id}][old_tags]", tp.tags ?>
            <?= $this->text_field_tag("post[#{tp.id}][tags]", $pp['tags'], ['class' => "fp", 'size' => 45, 'tabindex' => 1]) ?>
          </td>
        </tr>
      <?php endforeach ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2"><?= $this->submit_tag($this->t('pool_transfer'), ['tabindex' => 2]) ?> <?= $this->button_to_function($this->t('pool_cancel'), "history.back()", ['tabindex' => 2]) ?></td>
      </tr>
    </tfoot>
  </table>
<?php end ?>
<?php }) ?>
