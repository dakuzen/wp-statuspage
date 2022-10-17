<?php

$hideButton = (isset($button) && ($button == 'false' || empty($button)));

// Open Block
?>
<div class="<?php echo $app->getConfig('app-slug') ?>-app statuspage-subscribe-response">
  <div class="statuspage-message <?php echo ($response['code'] >= 300 ? 'error' : 'success') ?>">
    <p><?php echo $response['message'] ?></p>
  </div>
  <?php if ($response['code'] >= 300) { ?>
  <div class="form-buttons">
    <button type="button" class="btn btn-primary" onclick="statuspage.openSubscribeDialog();"><?php echo __('Try Again', 'statuspage'); ?></button>
    <button type="button" class="btn btn-default" onclick="statuspage.closeSubscribeDialog();"><?php echo __('Cancel', 'statuspage'); ?></button>
  </div>
  <?php } else { ?>
  <div class="form-buttons">
    <button type="button" class="btn btn-default" onclick="statuspage.closeSubscribeDialog();"><?php echo __('Close', 'statuspage'); ?></button>
  </div>
  <?php } ?>
</div>
<?php