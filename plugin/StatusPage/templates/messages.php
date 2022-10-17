<?php

// Open Block
?><div class="<?php echo $app->getConfig('app-slug') ?>-app statuspage-messages"><?php

// Session Notice
if (!empty($app->messages)) {
  foreach ($app->messages AS $message) {
    ?>
    <div class="statuspage-message <?php echo ($message['code'] >= 300 ? 'error' : ($message['code'] >= 200 ? 'success' : 'info')) ?>">
      <p><?php echo $message['message'] ?></p>
    </div>
    <?php
  }
}

// Close Block
?></div><?php
