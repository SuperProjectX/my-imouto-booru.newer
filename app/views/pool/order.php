<h3><?=$this->t('pool_order') ?><?= $this->link_to($this->pool->pretty_name(), ['action' => 'show', 'id' => $this->pool->id]) ?></h3>
<p><?=$this->t('pool_order_text') ?></p>

<script type="text/javascript">
  function orderAutoFill() {
    var i = 0
    var step = parseInt(prompt('<?=$this->t('pool_interval') ?>'))

    $$(".pp").each(function(x) {
      x.value = i
      i += step
    })
  }

  function orderReverse() {
    var orders = []
    $$(".pp").each(function(x) {
      orders.push(x.value)
    })
    var i = orders.size() - 1
    $$(".pp").each(function(x) {
      x.value = orders[i]
      i -= 1
    })
  }

  function orderShift(start, offset) {
    var found = false;
    $$(".pp").each(function(x) {
      if(x.id == "pool_post_sequence_" + start)
        found = true;
      if(!found)
        return;
      x.value = Number(x.value) + offset;
    });
  }
</script>

<?= $this->form_tag(function(){ ?>
  <?= $this->hidden_field_tag("id", $this->pool->id) ?>
  <table>
    <thead>
      <tr>
        <th></th>
        <th><?=$this->t('pool_order2') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->pool_posts as $pp) : ?>
        <tr>
          <td>
            <?php if ($pp->post->can_be_seen_by($this->current_user)) : ?>
              <?= $this->link_to($this->image_tag($pp->post->preview_url, ['width' => $pp->post->preview_dimensions()[0], 'height' => $pp->post->preview_dimensions()[1]]), ["post#show", 'id' => $pp->post_id], ['title' => $pp->post->tags]) ?>
            <?php endif ?>
          </td>
          <td>
            <?= $this->text_field_tag("pool_post_sequence[{$pp->id}]", $pp->sequence, ['class' => "pp", 'size' => 5, 'tabindex' => 1]) ?>
            <?= $this->link_to_function($this->t('pool_order_plus_one'), "orderShift({$pp->id}, +1)", ['class'=>"text-button"]) ?>
            <?= $this->link_to_function($this->t('pool_order_minus_one'), "orderShift({$pp->id}, -1)", ['class'=>"text-button"]) ?>
          </td>
        </tr>
      <?php endforeach ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2"><?= $this->submit_tag($this->t('pool_save'), ['tabindex' => 2]) ?> <?= $this->button_to_function($this->t('pool_order_auto'), "orderAutoFill()", ['tabindex' => 2]) ?> <?= $this->button_to_function($this->t('pool_reverse'), "orderReverse()", ['tabindex' => 2]) ?> <?= $this->button_to_function($this->t('pool_cancel'), "history.back()", ['tabindex' => 2]) ?></td>
      </tr>
    </tfoot>
  </table>
<?php }) ?>
