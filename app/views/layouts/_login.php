<?php if (CONFIG()->enable_account_email_activation) return ?>

<div id="login-background" style="display: none;">&nbsp;</div>

<div id="login-container" style="display: none;">
  <div id="login-container-inner">
    <div id="login-container-with-tabs">
      <div>
        <ul class="flat-list login-tabs" id="login-tabs">
        <li id="tab-login"><a href="#"><?= $this->t('layout_login') ?></a></li>
        <li id="tab-reset"><a href="#"><?= $this->t('layout_reset') ?></a></li>
        </ul>
      </div>
      <div id="login" style="position: relative;">
        <p id="tab-login-text" class="tab-header-text">
          <?= $this->t('layout_login_text') ?>
        </p>
        <p id="tab-reset-text" class="tab-header-text">
          <?= $this->t('layout_email_text') ?>
        </p>

        <?= $this->form_tag("user#authenticate", array('id' => "login-popup"), function() { ?>
          <div style="position: absolute; top: 0; right: 0;">
            <a href="#" id="login-popup-cancel" style="font-size: 1.2em; padding: 2px;">ⓧ</a>
          </div>

          <table class="form" style="width: 80%; max-width: 30em; margin-bottom: .5em; margin-left: auto; margin-right: auto;">
            <tr>
              <th style="width: 8em"><label class="block" for="login-popup-username"><?= $this->t('layout_name') ?></label></th>
              <td style="width: 10em" align="left"><input id="login-popup-username" name="username" type="text" style="width: 100%;"></td>
            </tr>
            <tr id="login-popup-email-box">
              <th><label class="block" for="login-popup-email"><?= $this->t('layout_email') ?></label></th>
              <td align="left"><input id="login-popup-email" name="email" type="text" style="width: 100%;"></td>
            </tr>
            <tr id="login-popup-password-box">
              <th><label class="block" for="login-popup-password"><?= $this->t('layout_password') ?></label></th>
              <td align="left"><input id="login-popup-password" name="password" type="password" style="width: 100%;"></td>
            </tr>
            <tr id="login-popup-password-confirm-box" style="display: none;">
              <th><label class="block" for="login-popup-password-confirm"><?= $this->t('layout_confirm') ?></label></th>
              <td align="left"><input id="login-popup-password-confirm" name="password-confirm" type="password" style="width: 100%;"></td>
            </tr>
            <tr>
              <th style="background: none;"></th>
              <td align="left">
              </td>
            </tr>
          </table>
          <a href="#" id="login-popup-submit" style="margin-bottom: 1em; margin-left: auto; margin-right: auto;"><?= $this->t('layout_login') ?></a>
        <?php }) ?>

        <div id="login-popup-notices" class="login-popup-notice">
          <span id="login-popup-login-confirm-password">
            <?= $this->t('layout_not_exist') ?>
          </span>
          <span id="login-popup-login-user-exists">
            <?= $this->t('layout_exists') ?>
          </span>
          <span id="login-popup-reset-user-exists">
            <?= $this->t('layout_email_reset') ?>
          </span>
          <span id="login-popup-reset-user-has-no-email">
            <?= $this->t('layout_no_email') ?>
          </span>
          <span id="login-popup-reset-successful">
            <?= $this->t('layout_reset_successful') ?>
          </span>
          <span id="login-popup-reset-unknown-user">
            <?= $this->t('layout_unknown_user') ?>
          </span>
          <span id="login-popup-reset-blank">
          </span>
          <span id="login-popup-reset-user-email-incorrect">
            <?= $this->t('layout_wrong_email') ?>
          </span>
          <span id="login-popup-reset-user-email-invalid">
            <?= $this->t('layout_invalid_email') ?>
          </span>
          <span id="login-popup-message">
          </span>
        </div>
      </div>
    </div>

  </div>
</div>

<script type="text/javascript">document.observe("dom:loaded", function() { User.init(); });</script>

