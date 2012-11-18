<form method="get" action="/post/moderate">
  <?= $this->text_field_tag("query", "", ['size' => '40']) ?>
  <?= $this->submit_tag($this->t('post_mod_search')) ?>
</form>

<script type="text/javascript">
  function highlight_row(checkbox) {
    var row = checkbox.parentNode.parentNode
    if (row.original_class == null) {
      row.original_class = row.className
    }

    if (checkbox.checked) {
      row.className = "highlight"
    } else {
      row.className = row.original_class
    }
  }
</script>

<div style="margin-bottom: 2em;">
  <h2><?= $this->t('post_mod_pending') ?></h2>
  <form method="post" action="/post/moderate">
    <?= $this->hidden_field_tag("reason", "") ?>

    <table width="100%">
      <tfoot>
        <tr>
          <td colspan="3">
            <?= $this->button_to_function($this->t('post_mod_select'), "$$('.p').each(function (i) {i.checked = true; highlight_row(i)})") ?>
            <?= $this->button_to_function($this->t('post_mod_invert'), "$$('.p').each(function (i) {i.checked = !i.checked; highlight_row(i)})") ?>
            <?= $this->submit_tag($this->t('post_mod_approve')) ?>
            <?= $this->submit_tag($this->t('post_mod_delete'), ['onclick' => "var reason = prompt('".$this->t('post_mod_enter_reason')."'); if (reason != null) {\$('reason').value = reason; return true} else {return false}"]) ?>
          </td>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($this->pending_posts as $p) : ?>
          <tr class="<?php if ($p->score > 2): ?>good<?php elseif ($p->score < -2): ?>bad<?php endif ?> <?= $this->cycle('even', 'odd') ?>">
            <td><input type="checkbox" class="p" name="ids[<?= $p->id ?>]" onclick="highlight_row(this)"></td>
            <td><?= $this->link_to($this->image_tag($p->preview_url, ['width' => $p->preview_dimensions()[0], 'height' => $p->preview_dimensions()[1]]), ['post#show', 'id' => $p->id]) ?></td>
            <td class="checkbox-cell">
              <ul>
                <li><?= $this->t('post_mod_uploaded') ?> <?= $this->link_to($p->author, ['controller' => 'user', 'action' => 'show', 'id' => $p->user_id]) ?> <?= $this->t(['time.x_ago', 't' => $this->time_ago_in_words($p->created_at)]) ?> (<?= $this->link_to($this->t('post_mod'), ['action' => 'moderate', 'query' => 'user:'.$p->author]) ?>)</li>
                <li><?= $this->t('post_mod_rating') ?><?= $p->pretty_rating() ?></li>
                <?php if ($p->parent_id) : ?>
                  <li><?= $this->t('post_mod_parent') ?><?= $this->link_to($p->parent_id, ['action' => 'moderate', 'query' => 'parent:'.$p->parent_id]) ?></li>
                <?php endif ?>
                <li><?= $this->t('post_mod_tags') ?><?= $this->h($p->tags) ?></li>
                <li><?= $this->t('post_mod_score') ?><span id="post-score-<?= $p->id ?>"><?= $p->score ?></span></li>
                <?php if ($p->flag_detail) : ?>
                <li>
                  <?= $this->t('post_mod_reason') ?><?= $this->h($p->flag_detail->reason) ?> (<?php if (!$p->flag_detail->user_id): ?>automatic flag<?php else: ?><?= $this->link_to($this->h($p->flag_detail->author()), ['user#show', 'id' => $p->flag_detail->user_id]) ?><?php endif ?>)
                </li>
                <?php endif ?>
                <li><?= $this->t('post_mod_size') ?><?= $this->number_to_human_size($p->file_size) ?>, <?= $p->width ?>x<?= $p->height ?></li>
              </ul>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </form>
</div>

<div>
  <h2><?= $this->t('post_mod_flagged') ?></h2>
  <form method="post" action="/post/moderate">
    <?= $this->hidden_field_tag("reason2", "") ?>

    <table width="100%">
      <tfoot>
        <tr>
          <td colspan="3">
            <?= $this->button_to_function($this->t('post_mod_select'), "$$('.f').each(function (i) {i.checked = true; highlight_row(i)})") ?>
            <?= $this->button_to_function($this->t('post_mod_invert'), "$$('.f').each(function (i) {i.checked = !i.checked; highlight_row(i)})") ?>
            <?= $this->submit_tag($this->t('post_mod_approve')) ?>
            <?= $this->submit_tag($this->t('post_mod_delete'), ['onclick' => "var reason = prompt('".$this->t('post_mod_enter_reason')."'); if (reason != null) {\$('reason2').value = reason; return true} else {return false}"]) ?>
          </td>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($this->flagged_posts as $p) : ?>
          <tr class="<?= $this->cycle('even', 'odd') ?>">
            <td><input type="checkbox" class="f" name="ids[<?= $p->id ?>]" onclick="highlight_row(this)"></td>
            <td><?= $this->link_to($this->image_tag($p->preview_url, ['width' => $p->preview_dimensions()[0], 'height' => $p->preview_dimensions()[1]]), ['post#show', 'id' => $p->id]) ?></td>
            <td class="checkbox-cell">
              <ul>
                <li><?= $this->t('post_mod_uploaded') ?><?= $this->link_to($this->h($p->author), ['user#show', 'id' => $p->user_id]) ?><?= $this->t('post_mod_ago2') ?><?= $this->time_ago_in_words($p->created_at) ?><?= $this->t('post_mod_ago') ?>(<?= $this->link_to("mod", ['action' => 'moderate', 'query' => 'user:'.$p->author]) ?>)</li>
                <li><?= $this->t('post_mod_rating') ?><?= $p->pretty_rating() ?></li>
                <?php if ($p->parent_id) : ?>
                  <li><?= $this->t('post_mod_parent') ?><?= $this->link_to($p->parent_id, ['action' => 'moderate', 'query' => 'parent:'.$p->parent_id]) ?></li>
                <?php endif ?>
                <li><?= $this->t('post_mod_tags') ?><?= $this->h($p->tags) ?></li>
                <li><?= $this->t('post_mod_score') ?><?= $p->score ?> (vote <?= $this->link_to_function($this->t('post_mod_down'), "Post.vote(-1, {$p->id}, {})") ?>)</li>
                <?php if ($p->flag_detail) : ?>
                <li>
                  <?= $this->t('post_mod_reason') ?>)<?= $this->h($p->flag_detail->reason) ?> (<?php if (!$p->flag_detail->user_id): ?>automatic flag<?php else: ?><?= $this->link_to($this->h($p->flag_detail->author), ['user#show', 'id' => $p->flag_detail->user_id]) ?><?php endif ?>)
                </li>
                <?php endif ?>
                <li><?= $this->t('post_mod_size') ?><?= $this->number_to_human_size($p->file_size) ?>, <?= $p->width ?>x<?= $p->height ?></li>
              </ul>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </form>

  <script type="text/javascript">
    var cells = $$(".checkbox-cell")
    $$(".checkbox-cell").invoke("observe", "click", function(e) {this.up().firstDescendant().down("input").click()})
    <?php $this->pending_posts->merge($this->flagged_posts)->unique()->each(function($post){ ?>
      Post.register(<?= $post->to_json() ?>)
    <?php }) ?>
  </script>
</div>

<?= $this->render_partial('footer') ?>
