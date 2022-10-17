<?php

?>
<div id="<?php echo $app->getConfig('app-slug') ?>-app" class="subscribers-page wrap">
  <h1 class="wp-heading-inline"><?php echo __('StatusPage Subscribers') ?></h1>
  <form>
    <input type="hidden" name="page" value="statuspage">
    <input type="hidden" name="paged" value="<?php echo $paged ?>">
    <input type="hidden" name="orderby" value="<?php echo $orderby ?>">
    <input type="hidden" name="order" value="<?php echo $order ?>">
    <div class="tablenav top">
      <div class="alignleft actions bulkactions">
        <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
        <select name="action" id="bulk-action-selector-top">
          <option value="-1">Bulk actions</option>
          <option value="validate">Validate</option>
          <option value="unsubscribe">Unsubscribe</option>
        </select>
        <input type="submit" id="doaction" class="button action" value="Apply">
      </div>
      <h2 class="screen-reader-text">Pages list navigation</h2>
      <div class="tablenav-pages"><span class="displaying-num"><?php echo $total . ' ' . __($total == 1 ? 'item' : 'items', 'statuspage') ?></span>
        <span class="paging-input"><label for="current-page-selector" class="screen-reader-text"><?php echo __('Current Page', 'statuspage') ?></label>
          <input onchange="jQuery(this).closest('form').submit();" class="current-page" id="current-page-selector" type="text" name="paged" value="<?php echo $paged ?>" size="1" aria-describedby="table-paging">
          <span class="tablenav-paging-text"> <?php echo __('of', 'statuspage') ?> <span class="total-pages"><?php echo $pages ?></span></span>
        </span>
      </div>
    </div>
    <table class="wp-list-table widefat fixed striped table-view-list subscribers">
      <thead>
        <tr>
          <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>
          <th scope="col" id="title" class="manage-column column-title sortable <?php echo $order ?>">
            <a href="<?php echo $app->getTableSortUrl('statuspage', 'subscriber_email', $order) ?>"><span><?php _e('Email', 'statuspage') ?></span><span class="sorting-indicator"></span></a>
          </th>
          <th scope="col" id="title" class="manage-column column-email sortable <?php echo $order ?>">
            <a href="<?php echo $app->getTableSortUrl('statuspage', 'subscriber_email', $order) ?>"><span><?php _e('Verified', 'statuspage') ?></span><span class="sorting-indicator"></span></a>
          </th>
          <th scope="col" id="date" class="manage-column column-date sortable <?php echo $order ?>">
            <a href="<?php echo $app->getTableSortUrl('statuspage', 'subscriber_email', $order) ?>"><span><?php _e('Date Subscribed', 'statuspage') ?></span><span class="sorting-indicator"></span></a>
          </th>
          <th width="100px">&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($subscribers AS $subscriber) { ?>
        <tr id="subvscriber-<?php echo $subscriber->subscriber_id ?>" class="">
          <th scope="row" class="check-column">
            <label class="screen-reader-text" for="cb-select-<?php echo $subscriber->subscriber_id ?>"><?php esc_html($subscriber->email) ?></label>
            <input id="cb-select-<?php echo $subscriber->subscriber_id ?>" type="checkbox" name="cid[]" value="<?php echo $subscriber->subscriber_id ?>">
          </th>
          <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
            <strong><?php echo esc_html($subscriber->subscriber_email) ?></strong>
          </td>
          <td>
            <span class="post-state"><?php echo _e($subscriber->subscriber_validated ? 'Validated' : 'Pending Validation', 'statuspage') ?></span>
            <?php if(!$subscriber->subscriber_validated){ ?><a class="btn btn-xs" target="_blank" onclick="if(!confirm('<?php esc_attr_e('Validate this subscriber?', 'statuspage') ?>'))return false;" href="<?php echo $app->getRouteUrl('validate?key=' . $subscriber->subscriber_validation_key) ?>"><?php _e('Validate', 'statuspage') ?></a><?php } ?>
          </td>
          <td class="date column-date" data-colname="Date"><?php echo wp_date('Y-m-d H:i:s', strtotime($subscriber->subscriber_date)) ?></td>
          <td><a class="btn btn-xs btn-warning" target="_blank" onclick="if(!confirm('<?php esc_attr_e('Unsubscribe this subscriber?', 'statuspage') ?>'))return false;" href="<?php echo $app->getRouteUrl('unsubscribe?key=' . $subscriber->subscriber_validation_key) ?>"><?php _e('Unsubscribe', 'statuspage') ?></a></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </form>
</div>