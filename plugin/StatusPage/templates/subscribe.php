<?php

$hideButton = (isset($button) && ($button == 'false' || empty($button)));

// Open Block
?><div id="<?php echo $app->getConfig('app-slug') ?>-app" class="statuspage-subscribe">
  <?php if (!$hideButton) { ?><button class="btn btn-default"><?php _e('Subscribe', 'statuspage') ?></button><?php } ?>
  <div id="statuspage_subscribe_modal" style="display: none;">
    <div class="form-group">
    </div>
  </div>
</div><?php
