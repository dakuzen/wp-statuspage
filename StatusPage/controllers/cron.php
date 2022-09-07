<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
$app = StatusPageApp::getInstance();

// Flags
$forceUpdate = true;

// Stage
$scanTargetUrl = $app->getPluginOption('scanTargetUrl');
$scanConfig = $app->getScanConfig();
$scanTargetCache = $app->getPluginOption('scanTargetCache');
$scanTargetCacheTime = $app->getPluginOption('scanTargetCacheTime');

// Notification Stack
$serviceNotifications = array();

// Pull Document
if (empty($scanTargetCacheTime) || ($scanTargetCacheTime < time() - 3600)) {
  inspect('New Request');
  $pageContent = file_get_contents($scanTargetUrl);
  if ($pageContent) {
    $scanTargetCacheTime = time();
    $app->savePluginOption('scanTargetCache', $pageContent);
    $app->savePluginOption('scanTargetCacheTime', $scanTargetCacheTime);
  }
}
else {
  inspect('Cached Request');
  $pageContent = $scanTargetCache;
}

// Content Available
if ($pageContent) {

  // Stage Document
  $doc = new DOMDocument();
  $doc->loadHTML($pageContent);
  $xpath = new DomXpath($doc);

  // Scan for Service Status Changes
  foreach ($scanConfig->services AS $service) {
    $serviceRows = $xpath->query('//div[@data-component-id="'. $service->componentId .'"]');
    foreach ($serviceRows AS $serviceRow) {

      // Service Name
      $rowNode = $xpath->query('./span[contains(@class, "name")]', $serviceRow);
      $serviceName = preg_replace('/^[\s\r\n]*(.*?)[\s\r\n]*$/', '$1', $rowNode->item(0)->textContent);

      // Service Status
      $rowNode = $xpath->query('./span[contains(@class, "component-status")]', $serviceRow);
      $serviceStatus = preg_replace('/^[\s\r\n]*(.*?)[\s\r\n]*$/', '$1', $rowNode->item(0)->textContent);

      // Review History
      $serviceHistory = $app->getPluginOption('serviceHistory_'.$service->componentId);
      inspect($service, $serviceHistory, $serviceName, $serviceStatus);
      if (empty($serviceHistory) || $serviceHistory->status != $serviceStatus) {

        // Save Status
        $app->savePluginOption('serviceHistory_'.$service->componentId, (object)array(
          'time' => time(),
          'name' => $serviceName,
          'status' => $serviceStatus
        ));

        // Trigger Notification
        if ($serviceHistory->status != $serviceStatus) {
          $serviceNotifications[] = array(
            'componentId' => $service->componentId,
            'newStatus' => $serviceStatus,
            'oldStatus' => $serviceHistory->status
            );
        }

      }

    }
  }

  /**
   * Load Stored Published Incident IDs
   */
  $query = new WP_Query(array(
    'post_type' => 'statuspage_incident',
    'post_status' => 'publish'
    ));
  $publishedIncidentPosts = array_combine(wp_list_pluck($query->posts, 'ID'), wp_list_pluck($query->posts, 'post_name'));
  $relevantIncidentPosts = array();

  /**
   * Scan for Notifications
   */
  $incidentRows = $xpath->query('.//div[contains(@class, "unresolved-incidents")]//div[contains(@class, "unresolved-incident")]', $incidentBlock);
  foreach ($incidentRows AS $incidentRow) {

    /**
     * Parse Notification
     * Separate Update Rows
     */
    $titleRow = $xpath->query('.//a[contains(@class, "actual-title")]', $incidentRow)->item(0);
    $incidentTitle = $app->translateContent($titleRow->textContent, $scanConfig->translations);
    $incidentPath = $titleRow->getAttribute('href');
    $incidentId = explode('/', $incidentPath);
    $incidentId = end($incidentId);
    $incidentSeverity = preg_replace('/.*impact-(\w)/', '$1', $incidentRow->getAttribute('class'));
    $updateRows = $xpath->query('.//div[contains(@class, "updates")]//div[contains(@class, "update")]', $incidentRow);
    $incidentUpdates = array();
    $incidentContent = array();

    /**
     * Parse Notification Updates
     */
    foreach ($updateRows AS $updateRow) {
      $updateType = $xpath->query('.//strong', $updateRow)->item(0)->textContent;
      $updateContent = $xpath->query('.//span[contains(@class, "whitespace-pre-wrap")]', $updateRow)->item(0)->textContent;
      $updateContent = $app->translateContent($updateContent, $scanConfig->translations);
      $updateCreated = $xpath->query('.//small', $updateRow)->item(0)->textContent;
      $updateCreated = str_replace(' - ', ', ', $updateCreated);
      if ($updateCreatedTime = $xpath->query('.//small//span[@class="ago"]', $updateRow)->item(0)->getAttribute('data-datetime-unix')) {
        $updateCreatedTime = substr($updateCreatedTime, 0, 10);
      }
      else {
        $updateCreatedTime = strtotime($updateCreated);
      }
      $updateCreatedGMT = date('Y-m-d H:i:s', $updateCreatedTime);
      $incidentUpdates[] = (object)array(
        'updateType' => $updateType,
        'updateContent' => $updateContent,
        'updateCreated' => $updateCreated,
        'updateCreatedGMT' => $updateCreatedGMT,
        'updateCreatedTime' => $updateCreatedTime
        );
      $incidentContent[] = '<div class="statuspage-incident '.strtolower($updateType) .'">'
                         . '<div class="statuspage-incident-type">' . esc_html($updateType) .'</div>'
                         . '<div class="statuspage-incident-content">' . esc_html($updateContent) .'</div>'
                         . '<div class="statuspage-incident-date">' . esc_html(get_date_from_gmt($updateCreatedGMT, 'F nS, Y - g:ia')) .'</div>'
                         . '</div>';
    }

    /**
     * Load Stored Incident
     */
    $incidentLastUpdated = reset($incidentUpdates)->updateCreatedGMT;
    inspect($incidentTitle, $incidentPath, $incidentId, $incidentLastUpdated, $incidentUpdates, $incidentContent);
    $query = new WP_Query(array(
      'post_type' => 'statuspage_incident',
      'post_status' => array('publish', 'archive', 'invalid', 'draft', 'trash'),
      'name' => $incidentId
      ));

    /**
     * Create or Update Post
     */
    $isNew = false;
    $isUpdated = false;
    $isRelevant = false;
    $incidentPost = null;
    if (empty($query->posts)) {
      $isNew = true;
      $post_id = wp_insert_post(array(
        'post_type'     => 'statuspage_incident',
        'post_title'    => $incidentTitle,
        'post_status'   => 'invalid',
        'post_content'  => implode("\n", $incidentContent),
        'post_name'     => $incidentId,
        'post_date'     => get_date_from_gmt($incidentLastUpdated),
        'post_date_gmt' => $incidentLastUpdated,
        'meta_input'    => array(
          'incidentSeverity' => $incidentSeverity,
          'incidentLastUpdated' => $incidentLastUpdated,
          'incidentDetailsCache' => '',
          'incidentAffectedServices' => ''
        )
      ));
      $query = new WP_Query(array(
        'p' => $post_id,
        'post_type' => 'statuspage_incident'
      ));
      $incidentPost = reset($query->posts);
    }
    else {
      $incidentPost = reset($query->posts);
    }
    $incidentPostLastUpdated = get_post_meta($incidentPost->ID, 'incidentLastUpdated')[0];
    $isUpdated = ($incidentLastUpdated != $incidentPostLastUpdated);
    if ($isUpdated || $forceUpdate) {
      wp_update_post(array(
        'ID'                => $incidentPost->ID,
        'post_title'        => $incidentTitle,
        'post_content'      => implode("\n", $incidentContent),
        'post_modified'     => get_date_from_gmt($incidentLastUpdated),
        'post_modified_gmt' => $incidentLastUpdated,
        'meta_input'        => array(
          'incidentSeverity' => $incidentSeverity,
          'incidentLastUpdated' => $incidentLastUpdated
        )
      ));
    }

    /**
     * Update Details / Affected Services
     */
    if ($isNew || $isUpdated) {

      /**
       * Pull Incident Details
       */
      $detailsContent = file_get_contents($scanTargetUrl . $incidentPath);
      if ($detailsContent) {
        update_post_meta($incidentPost->ID, 'incidentDetailsCache', $detailsContent);
        update_post_meta($incidentPost->ID, 'incidentAffectedServices', '');
      }

      /**
       * Parse Affected Services String
       */
      $detailsContent = get_post_meta($incidentPost->ID, 'incidentDetailsCache')[0];
      $detailsDoc = new DOMDocument();
      $detailsDoc->loadHTML($detailsContent);
      $detailsXPath = new DomXpath($detailsDoc);
      $textNode = $detailsXPath->query('//div[contains(@class, "components-affected")]');
      if ($affectedString = $textNode->item(0)->textContent) {
        update_post_meta($incidentPost->ID, 'incidentAffectedServices', $affectedString);
      }

    }

    /**
     * Review Affected Services
     */
    $incidentAffectedServices = get_post_meta($incidentPost->ID, 'incidentAffectedServices')[0];
    foreach ($scanConfig->services AS $service) {

      // Service Name Match
      if (strpos(strtolower($incidentAffectedServices), '('.strtolower($service->componentName).')') !== false) {
        $isRelevant = true;
        if ($isNew || $isUpdated) {
          $serviceNotifications[] = array(
            'componentId' => $service->componentId,
            'incidentUpdate' => reset($incidentUpdates)
            );
        }
      }

    }

    if ($isRelevant) {
      $relevantIncidentPosts[$incidentPost->ID] = $incidentId;
    }

  }

  /**
   * Publish Relevant Posts
   */
  inspect('relevantIncidentPosts', $relevantIncidentPosts);
  foreach ($relevantIncidentPosts AS $post_id => $incidentId) {
    if (!in_array($incidentId, $publishedIncidentPosts)) {
      wp_update_post(array(
        'ID' => $post_id,
        'post_status' => 'publish'
      ));
    }
  }

  /**
   * Disable Old Incidents
   */
  inspect('publishedIncidentPosts', $publishedIncidentPosts);
  foreach ($publishedIncidentPosts AS $post_id => $incidentId) {
    if (!in_array($incidentId, $relevantIncidentPosts)) {
      wp_update_post(array(
        'ID' => $post_id,
        'post_status' => 'archive'
      ));
    }
  }

}

inspect('Service Notifications', $serviceNotifications);