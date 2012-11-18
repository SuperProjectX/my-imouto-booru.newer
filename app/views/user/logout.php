<div class="memo">
    <h3><?= $this->t('user_logout_text') ?></h3>
    <p><?= $this->t('user_logout_text2') ?></p>
    <?= $this->link_to($this->t('user_logout_link'), "/user/login") ?>
</div>

<?= $this->render_partial("footer") ?>
