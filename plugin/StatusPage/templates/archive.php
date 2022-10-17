<?php

// Open Block
?><div id="<?php echo $app->getConfig('app-slug') ?>-app" class="statuspage-archive"><?php

// Loop Notices
foreach ($incidentPosts AS $post) {

  $incidentSeverity = get_post_meta($post->ID, 'incidentSeverity')[0];
  ?>
  <div class="statuspage-notice impact-<?php echo esc_attr($incidentSeverity) ?>">
    <div class="statuspage-notice-label"><?php echo esc_html($post->post_title) ?></div>
    <div class="statuspage-notice-content"><?php echo $post->post_content ?></div>
  </div>
  <?php
}

// Close Block
?></div><?php
