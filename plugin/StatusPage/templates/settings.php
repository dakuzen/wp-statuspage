<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
$app = StatusPageApp::getInstance();

?>
<div id="<?php echo $app->getConfig('app-slug') ?>-app" class="settings-page">
  <h1><?php echo __('StatusPage Settings') ?></h1>
  <form method="post">
    <?php

      settings_fields( $this->getConfig('app-slug').'_settings' );
      do_settings_sections( 'settings' );

    ?>
    <div class="form-group alertEmail">
      <label for="alertEmail"><?php echo esc_html__('Email Address') ?></label>
      <input type="email" class="form-control" id="alertEmail" name="statuspage_settings[alertEmail]" aria-describedby="<?php echo esc_html__('Email Address') ?>" placeholder="eg: joe@website.com" value="<?php echo htmlspecialchars($app->getPluginOption('alertEmail')) ?>">
      <small id="alertEmailHelp" class="form-text text-muted"><?php echo esc_html__('Please provide a comma separated email address to receive notifications of plugin events.') ?></small>
    </div>
    <div class="form-group scanTargetUrl">
      <label for="scanTargetUrl"><?php echo esc_html__('Scan Target URL') ?></label>
      <input type="text" class="form-control" id="scanTargetUrl" name="statuspage_settings[scanTargetUrl]" aria-describedby="<?php echo esc_html__('Scan Target URL') ?>" placeholder="eg: https://company.statuspage.io/" value="<?php echo htmlspecialchars($app->getPluginOption('scanTargetUrl')) ?>">
      <small id="scanTargetUrlHelp" class="form-text text-muted"><?php echo esc_html__('Please provide the configuration used for scanning the remote status page.') ?></small>
    </div>
    <div class="form-group scanConfigOption">
      <label for="scanConfigOption"><?php echo esc_html__('Scan Configuration') ?></label>
      <div class="form-check">
        <input type="radio" class="form-check-input" id="scanConfigOptionFile" name="statuspage_settings[scanConfigOption]" aria-describedby="<?php echo esc_html__('Scan Configuration Option') ?>" value="file" <?php echo ($app->getPluginOption('scanConfigOption') == 'file' ? 'checked' : '') ?>>
        <label for="scanConfigOptionFile" class="form-check-label"><?php echo esc_html__('File') ?></label>
      </div>
      <div class="form-check">
        <input type="radio" class="form-check-input" id="scanConfigOptionString" name="statuspage_settings[scanConfigOption]" aria-describedby="<?php echo esc_html__('Scan Configuration Option') ?>" value="json" <?php echo ($app->getPluginOption('scanConfigOption') != 'file' ? 'checked' : '') ?>>
        <label for="scanConfigOptionString" class="form-check-label"><?php echo esc_html__('JSON String') ?></label>
      </div>
      <small id="scanConfigOptionHelp" class="form-text text-muted"><?php echo esc_html__('How do you wish to define the scan configuration.') ?></small>
    </div>
    <div class="form-group scanConfigFile <?php echo ($app->getPluginOption('scanConfigOption') == 'file' ? '' : 'hidden') ?>">
      <label for="scanConfigFile"><?php echo esc_html__('Scan Configuration File') ?></label>
      <input type="text" class="form-control" id="scanConfigFile" name="statuspage_settings[scanConfigFile]" aria-describedby="<?php echo esc_html__('Scan Configuration File') ?>" placeholder="eg: <?php echo get_stylesheet_directory() ?>/wp-statuspage.json" value="<?php echo htmlspecialchars($app->getPluginOption('scanConfigFile', get_stylesheet_directory() . '/wp-statuspage.json')) ?>">
      <small id="scanConfigFileHelp" class="form-text text-muted"><?php echo esc_html__('Please provide the path to your statuspage configuration file.') ?></small>
    </div>
    <div class="form-group scanConfig <?php echo ($app->getPluginOption('scanConfigOption') != 'file' ? '' : 'hidden') ?>">
      <label for="scanConfig"><?php echo esc_html__('Scan Configuration JSON') ?></label>
      <textarea class="form-control" id="scanConfig" name="statuspage_settings[scanConfig]" aria-describedby="<?php echo esc_html__('Scan Configuration JSON') ?>" placeholder="JSON Block"><?php echo htmlspecialchars($app->getPluginOption('scanConfig')) ?></textarea>
      <small id="scanConfigHelp" class="form-text text-muted"><?php echo esc_html__('Please provide the configuration used for scanning the remote status page.') ?></small>
    </div>
    <div class="form-buttons">
      <button type="submit" class="btn btn-success"><?php echo __('Save Changes'); ?></button>
    </div>
  </form>
  <div class="statuspage-shortcodes">
    <p>The following shortcodes are available for use within your content:</p>
    <code>[statuspage view="subscribe" button="true|false"]</code>
    <code>[statuspage view="notices"]</code>
    <code>[statuspage view="status"]</code>
    <code>[statuspage view="archive"]</code>
  </div>
  <script><!--
    document.querySelectorAll('input[type=radio][name*=scanConfigOption]').forEach(function(el){
      el.onchange = function(){
        document.querySelector('.form-group.scanConfigFile').style.display = (this.value != 'file' ? 'none' : 'block');
        document.querySelector('.form-group.scanConfig').style.display = (this.value == 'file' ? 'none' : 'block');
      }
    });
  //--></script>
</div>