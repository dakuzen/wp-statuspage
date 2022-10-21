<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
$app = StatusPageApp::getInstance();
$pagePosts = $wpdb->get_results("SELECT `ID`, `post_title` FROM `{$wpdb->prefix}posts` WHERE `post_status` = 'publish' AND `post_type` = 'page'");
$emailTemplates = $wpdb->get_results("SELECT `ID`, `post_title` FROM `{$wpdb->prefix}posts` WHERE `post_status` = 'publish' AND `post_type` = 'statuspage_emailtmpl'");

?>
<div class="<?php echo $app->getConfig('app-slug') ?>-app settings-page wrap">
  <h1 class="wp-heading-inline"><?php echo __('StatusPage Settings') ?></h1>
  <div class="statuspage-tabs">
    <a class="statuspage-tab statuspage-active" href="#tab-general">General</a>
    <a class="statuspage-tab" href="#tab-pages">Pages / Templates</a>
    <a class="statuspage-tab" href="#tab-shortcodes">Shortcodes / Variables</a>
  </div>
  <div class="statuspage-tab-content statuspage-active" data-tab="general">

    <form method="post">
      <?php /* Are these needed?
        settings_fields( $this->getConfig('app-slug').'_settings' );
        do_settings_sections( 'settings' );
      */ ?>
      <?php echo wp_nonce_field('statuspage_subscribe'); ?>
      <div class="form-group emailFrom">
        <label for="emailFrom"><?php echo esc_html__('Email From Name / Address', 'statuspage') ?></label>
        <input type="text" class="form-control half" id="emailFromName" name="statuspage_settings[emailFromName]" aria-describedby="<?php echo esc_html__('Email From Name', 'statuspage') ?>" placeholder="<?php echo esc_attr__('eg: Joe', 'statuspage'); ?>" value="<?php echo htmlspecialchars($app->getPluginOption('emailFromName')) ?>">
        <input type="text" class="form-control half" id="emailFrom" name="statuspage_settings[emailFrom]" aria-describedby="<?php echo esc_html__('Email From Address', 'statuspage') ?>" placeholder="<?php echo esc_attr__('eg: joe@website.com', 'statuspage'); ?>" value="<?php echo htmlspecialchars($app->getPluginOption('emailFrom')) ?>">
        <small id="emailFromHelp" class="form-text text-muted"><?php echo esc_html__('Provide an email address that will be the sender for system alerts.', 'statuspage') ?></small>
      </div>
      <div class="form-group scanTargetUrl">
        <label for="scanTargetUrl"><?php echo esc_html__('Scan Target URL', 'statuspage') ?></label>
        <input type="text" class="form-control" id="scanTargetUrl" name="statuspage_settings[scanTargetUrl]" aria-describedby="<?php echo esc_html__('Scan Target URL', 'statuspage') ?>" placeholder="eg: https://company.statuspage.io/" value="<?php echo htmlspecialchars($app->getPluginOption('scanTargetUrl')) ?>">
        <small id="scanTargetUrlHelp" class="form-text text-muted"><?php echo esc_html__('Please provide the configuration used for scanning the remote status page.', 'statuspage') ?></small>
      </div>
      <div class="form-group scanConfigOption">
        <label for="scanConfigOption"><?php echo esc_html__('Scan Configuration', 'statuspage') ?></label>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="scanConfigOptionFile" name="statuspage_settings[scanConfigOption]" aria-describedby="<?php echo esc_html__('Scan Configuration Option', 'statuspage') ?>" value="file" <?php echo ($app->getPluginOption('scanConfigOption') == 'file' ? 'checked' : '') ?>>
          <label for="scanConfigOptionFile" class="form-check-label"><?php echo esc_html__('File') ?></label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="scanConfigOptionString" name="statuspage_settings[scanConfigOption]" aria-describedby="<?php echo esc_html__('Scan Configuration Option', 'statuspage') ?>" value="json" <?php echo ($app->getPluginOption('scanConfigOption') != 'file' ? 'checked' : '') ?>>
          <label for="scanConfigOptionString" class="form-check-label"><?php echo esc_html__('JSON String') ?></label>
        </div>
        <small id="scanConfigOptionHelp" class="form-text text-muted"><?php echo esc_html__('How do you wish to define the scan configuration.', 'statuspage') ?></small>
      </div>
      <div class="form-group scanConfigFile <?php echo ($app->getPluginOption('scanConfigOption') == 'file' ? '' : 'hidden') ?>">
        <label for="scanConfigFile"><?php echo esc_html__('Scan Configuration File', 'statuspage') ?></label>
        <input type="text" class="form-control" id="scanConfigFile" name="statuspage_settings[scanConfigFile]" aria-describedby="<?php echo esc_html__('Scan Configuration File', 'statuspage') ?>" placeholder="eg: <?php echo get_stylesheet_directory() ?>/wp-statuspage.json" value="<?php echo htmlspecialchars($app->getPluginOption('scanConfigFile', get_stylesheet_directory() . '/wp-statuspage.json')) ?>">
        <small id="scanConfigFileHelp" class="form-text text-muted"><?php echo esc_html__('Please provide the path to your statuspage configuration file.', 'statuspage') ?></small>
      </div>
      <div class="form-group scanConfig <?php echo ($app->getPluginOption('scanConfigOption') != 'file' ? '' : 'hidden') ?>">
        <label for="scanConfig"><?php echo esc_html__('Scan Configuration JSON', 'statuspage') ?></label>
        <textarea class="form-control" id="scanConfig" name="statuspage_settings[scanConfig]" aria-describedby="<?php echo esc_html__('Scan Configuration JSON', 'statuspage') ?>" placeholder="JSON Block"><?php echo htmlspecialchars($app->getPluginOption('scanConfig')) ?></textarea>
        <small id="scanConfigHelp" class="form-text text-muted"><?php echo esc_html__('Please provide the configuration used for scanning the remote status page.', 'statuspage') ?></small>
      </div>
      <div class="form-buttons">
        <button type="submit" class="btn btn-success"><?php echo __('Save Changes', 'statuspage'); ?></button>
      </div>
    </form>
  </div>

  <div class="statuspage-tab-content" data-tab="pages">
    <form method="post">
      <?php echo wp_nonce_field('statuspage_subscribe'); ?>
      <div class="form-group pageId_statusPage">
        <label for="pageId_statusPage"><?php echo esc_html__('Status Page', 'statuspage') ?></label>
        <select class="form-control" id="pageId_statusPage" name="statuspage_settings[pageId_statusPage]" aria-describedby="<?php echo esc_html__('Page Post', 'statuspage') ?>"><?php
          $selectedId = $app->getPluginOption('pageId_statusPage');
          foreach ($pagePosts AS $pagePost)
            echo '<option value="'.$pagePost->ID.'"'.($pagePost->ID==$selectedId?' selected':'').'>' . esc_html($pagePost->post_title) . '</option>';
        ?></select>
        <small id="pageId_statusPageHelp" class="form-text text-muted"><?php echo esc_html__('Please select the page where status is displayed.', 'statuspage') ?></small>
      </div>
      <div class="form-group emailTmplId_emailValidate">
        <label for="emailTmplId_emailValidate"><?php echo esc_html__('Subscription Validation Email', 'statuspage') ?></label>
        <select class="form-control" id="emailTmplId_emailValidate" name="statuspage_settings[emailTmplId_emailValidate]" aria-describedby="<?php echo esc_html__('Subscription Validation Email', 'statuspage') ?>"><?php
          $selectedId = $app->getPluginOption('emailTmplId_emailValidate');
          foreach ($emailTemplates AS $emailTemplate)
            echo '<option value="'.$emailTemplate->ID.'"'.($emailTemplate->ID==$selectedId?' selected':'').'>' . esc_html($emailTemplate->post_title) . '</option>';
        ?></select>
        <small id="emailTmplId_emailValidateHelp" class="form-text text-muted"><?php echo esc_html__('Please select the email template for new subscription email validation.', 'statuspage') ?></small>
      </div>
      <div class="form-group emailTmplId_unsubscribe">
        <label for="emailTmplId_unsubscribe"><?php echo esc_html__('Un-Subscribe Email', 'statuspage') ?></label>
        <select class="form-control" id="emailTmplId_unsubscribe" name="statuspage_settings[emailTmplId_unsubscribe]" aria-describedby="<?php echo esc_html__('Un-Subscribe Email', 'statuspage') ?>"><?php
          $selectedId = $app->getPluginOption('emailTmplId_unsubscribe');
          foreach ($emailTemplates AS $emailTemplate)
            echo '<option value="'.$emailTemplate->ID.'"'.($emailTemplate->ID==$selectedId?' selected':'').'>' . esc_html($emailTemplate->post_title) . '</option>';
        ?></select>
        <small id="emailTmplId_unsubscribeHelp" class="form-text text-muted"><?php echo esc_html__('Please select the un-subscribe email template.', 'statuspage') ?></small>
      </div>
      <div class="form-group emailTmplId_serviceEvent">
        <label for="emailTmplId_serviceEvent"><?php echo esc_html__('Service Status Change Email', 'statuspage') ?></label>
        <select class="form-control" id="emailTmplId_serviceEvent" name="statuspage_settings[emailTmplId_serviceEvent]" aria-describedby="<?php echo esc_html__('Service Status Change Email', 'statuspage') ?>"><?php
          $selectedId = $app->getPluginOption('emailTmplId_serviceEvent');
          foreach ($emailTemplates AS $emailTemplate)
            echo '<option value="'.$emailTemplate->ID.'"'.($emailTemplate->ID==$selectedId?' selected':'').'>' . esc_html($emailTemplate->post_title) . '</option>';
        ?></select>
        <small id="emailTmplId_serviceEventHelp" class="form-text text-muted"><?php echo esc_html__('Please select the email template for service event alerts.', 'statuspage') ?></small>
      </div>
      <div class="form-group emailTmplId_serviceUpdate">
        <label for="emailTmplId_serviceUpdate"><?php echo esc_html__('Service Update Email', 'statuspage') ?></label>
        <select class="form-control" id="emailTmplId_serviceUpdate" name="statuspage_settings[emailTmplId_serviceUpdate]" aria-describedby="<?php echo esc_html__('Service Update Email', 'statuspage') ?>"><?php
          $selectedId = $app->getPluginOption('emailTmplId_serviceUpdate');
          foreach ($emailTemplates AS $emailTemplate)
            echo '<option value="'.$emailTemplate->ID.'"'.($emailTemplate->ID==$selectedId?' selected':'').'>' . esc_html($emailTemplate->post_title) . '</option>';
        ?></select>
        <small id="emailTmplId_serviceUpdateHelp" class="form-text text-muted"><?php echo esc_html__('Please select the email template for service incident update alerts.', 'statuspage') ?></small>
      </div>
      <div class="form-buttons">
        <button type="submit" class="btn btn-success"><?php echo __('Save Changes', 'statuspage'); ?></button>
      </div>
    </form>
  </div>

  <div class="statuspage-tab-content" data-tab="shortcodes">
    <div class="statuspage-shortcodes">
      <p>The following shortcodes are available for use within your content:</p>
      <code>
        [statuspage view="subscribe" button="true|false"]<br>
        [statuspage view="notices"]<br>
        [statuspage view="status"]<br>
        [statuspage view="archive"]
      </code>
      <hr>
      <p>The following variables are available for use within your email templates:</p>
      <code>
        {$service_name}<br>
        {$service_status}<br>
        {$service_status_date}<br>
        {$service_status_detail}<br>
        {$service_previous_status}<br>
        {$subscriber_email}<br>
        {$statuspage_link}<br>
        {$statuspage_url}<br>
        {$subscriber_validation_link}<br>
        {$subscriber_validation_url}<br>
        {$unsubscribe_link}<br>
        {$unsubscribe_url}
      </code>
    </div>
  </div>

  <script><!--
    document.querySelectorAll('a.statuspage-tab').forEach(function(el){
      el.onclick = function(){
        let tabKey = String(el.href).replace(/^.*#tab-/,'');
        document.querySelectorAll('.statuspage-tab').forEach(function(tel){
          tel.className = String(tel.className).replace(/ statuspage-active/, '');
          if (tel == el)
            tel.className = tel.className + ' statuspage-active';
        });
        document.querySelectorAll('.statuspage-tab-content').forEach(function(tel){
          tel.className = String(tel.className).replace(/ statuspage-active/, '');
          if (tel.dataset.tab == tabKey)
            tel.className = tel.className + ' statuspage-active';
        });
      }
    });
    document.querySelectorAll('input[type=radio][name*=scanConfigOption]').forEach(function(el){
      el.onchange = function(){
        document.querySelector('.form-group.scanConfigFile').style.display = (this.value != 'file' ? 'none' : 'block');
        document.querySelector('.form-group.scanConfig').style.display = (this.value == 'file' ? 'none' : 'block');
      }
    });
  //--></script>
</div>