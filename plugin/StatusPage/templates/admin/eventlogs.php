<?php

?>
<div id="<?php echo $app->getConfig('app-slug') ?>-app" class="eventlogs-page wrap">
  <h1 class="wp-heading-inline"><?php echo __('StatusPage Subscribers') ?></h1>

  <form>
    <input type="hidden" name="page" value="statuspage-eventlogs">
    <input type="hidden" name="paged" value="<?php echo $paged ?>">
    <input type="hidden" name="orderby" value="<?php echo $orderby ?>">
    <input type="hidden" name="order" value="<?php echo $order ?>">
    <div class="tablenav top">
      <div class="alignleft actions bulkactions">
        <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
        <select name="action" id="bulk-action-selector-top">
          <option value="-1">Bulk actions</option>
          <option value="delete">Delete</option>
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
          <th scope="col" id="title" class="manage-column column-title">
            <span><?php _e('Log Message', 'statuspage') ?></span>
          </th>
          <th scope="col" id="title" class="manage-column column-date column-primary sortable <?php echo $order ?>">
            <a href="<?php echo $app->getTableSortUrl('statuspage-eventlogs', 'log_date', $order) ?>"><span><?php _e('Log Date', 'statuspage') ?></span><span class="sorting-indicator"></span></a>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($eventlogs AS $eventlog) { ?>
        <tr id="eventlog-<?php echo $eventlog->log_id ?>" class="">
          <th scope="row" class="check-column">
            <label class="screen-reader-text" for="cb-select-<?php echo $eventlog->log_id ?>"><?php esc_html($eventlog->log_date) ?></label>
            <input id="cb-select-<?php echo $eventlog->log_id ?>" type="checkbox" name="cid[]" value="<?php echo $eventlog->log_id ?>">
          </th>
          <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
            <strong><?php echo esc_html($eventlog->log_message) ?></strong>
          </td>
          <td class="date column-date" data-colname="Date"><?php echo wp_date('Y-m-d H:i:s', strtotime($eventlog->log_date)) ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </form>
</div>