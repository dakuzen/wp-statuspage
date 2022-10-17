<?php

$hideButton = (isset($button) && ($button == 'false' || empty($button)));

// Open Block
?>
<div class="<?php echo $app->getConfig('app-slug') ?>-app statuspage-subscribe">
  <?php if (!$hideButton)
  { ?><button class="btn btn-default" onclick="subscribe.openSubscribeDialog(this);"><?php _e('Subscribe', 'statuspage') ?></button><?php } ?>
</div>
<div class="<?php echo $app->getConfig('app-slug') ?>-app statuspage-subscribe-modal">
  <form method="post" action="<?php echo get_site_url() . '/' . $app->getConfig('app-slug') . '/subscribe'; ?>" onsubmit="return statuspage.postSubscribeForm(this);">
    <?php wp_nonce_field('statuspage_subscribe'); ?>
    <h3><?php _e('Subscribe to Updates'); ?></h3>
    <p><?php echo esc_html__('Please provide an email address to receive notifications of service events.', 'statuspage') ?></p>
    <div class="form-group subscribeEmail">
      <label for="subscribeEmail"><?php echo esc_html__('Email Address') ?></label>
      <input type="email" class="form-control" id="subscribeEmail" name="subscribeEmail" aria-describedby="<?php echo esc_html__('Email Address') ?>" placeholder="<?php echo esc_attr__('eg: joe@website.com', 'statuspage'); ?>">
    </div>
    <div class="form-buttons">
      <button type="submit" class="btn btn-success"><?php echo __('Subscribe', 'statuspage'); ?></button>
    </div>
  </form>
</div>
<?php
