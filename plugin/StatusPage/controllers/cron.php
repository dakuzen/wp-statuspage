<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
use WebuddhaInc\StatusPage\Notification as StatusPageNotification;

$app = StatusPageApp::getInstance();

// Flags
$forceUpdate  = false;
$forceProcess = false;
$forceNotice  = false;
$cacheTimeout = 300; // 5 minutes

// Stage
$scanTargetUrl = $app->getPluginOption('scanTargetUrl');
$scanConfig = $app->getScanConfig();
$scanTargetCache = $app->getPluginOption('scanTargetCache');
$scanTargetCacheTime = $app->getPluginOption('scanTargetCacheTime');

/**
$serviceNotifications = array(
  (object)array(
    'componentId' => 'ccvk876zl45w',
    'incidentUpdate' => (object)array(
      'updateType' => 'Resolved',
      'updateTypeCode' => 'resolved',
      'updateContent' => 'We are currently experiencing a service disruption that may be preventing you from using our service. We’re sorry for the impact this may be having on your Business and we’re working hard to get this back up and running.',
      'updateCreatedGMT' => '2022-08-15 00:00:00',
      'updateCreatedTime' => strtotime('2022-08-15 00:00:00')
      )
  ),
  (object)array(
    'componentId' => 'b3gplc85qrc2',
    'newStatus' => 'Maintenance',
    'oldStatus' => 'Operational'
  ),
  (object)array(
    'componentId' => 'b3gplc85qrc2',
    'incidentUpdate' => (object)array(
      'updateType' => 'Resolved',
      'updateTypeCode' => 'resolved',
      'updateContent' => 'We are currently experiencing a service disruption that may be preventing you from using our service. We’re sorry for the impact this may be having on your Business and we’re working hard to get this back up and running.',
      'updateCreatedGMT' => '2022-08-15 00:00:00',
      'updateCreatedTime' => strtotime('2022-08-15 00:00:00')
      )
  ),
  (object)array(
    'componentId' => 'thwmzjjwgdww',
    'newStatus' => 'Maintenance',
    'oldStatus' => 'Operational'
  ),
  (object)array(
    'componentId' => 'thwmzjjwgdww',
    'incidentUpdate' => (object)array(
      'updateType' => 'Resolved',
      'updateTypeCode' => 'resolved',
      'updateContent' => 'We are currently experiencing a service disruption that may be preventing you from using our service. We’re sorry for the impact this may be having on your Business and we’re working hard to get this back up and running.',
      'updateCreatedGMT' => '2022-08-15 00:00:00',
      'updateCreatedTime' => strtotime('2022-08-15 00:00:00')
      )
  ),
  (object)array(
    'componentId' => 'thwmzjjwgdww',
    'incidentUpdate' => (object)array(
      'updateType' => 'Identified',
      'updateTypeCode' => 'identified',
      'updateContent' => 'We are currently experiencing a service disruption that may be preventing you from using our service. We’re sorry for the impact this may be having on your Business and we’re working hard to get this back up and running.',
      'updateCreatedGMT' => '2022-08-15 00:00:00',
      'updateCreatedTime' => strtotime('2022-08-15 00:00:00')
      )
  )
);
StatusPageNotification::processNotificationStack( $serviceNotifications );
die(__LINE__.': '.__FILE__);
 */

// Notification Stack
$serviceNotifications = array();

// Pull Document
if ($forceUpdate || empty($scanTargetCacheTime) || ($scanTargetCacheTime < time() - $cacheTimeout)) {
  $pageContent = file_get_contents($scanTargetUrl);
  if ($pageContent) {
    $app->postActivityLog('Pulled Status Page');
    $scanTargetCacheTime = time();
    $app->savePluginOption('scanTargetCache', $pageContent);
    $app->savePluginOption('scanTargetCacheTime', $scanTargetCacheTime);
  }
  else {
    $app->postActivityLog('Failed to pull Status Page');
    return;
  }
}
else {
  $pageContent = $scanTargetCache;
}

// Content Available
if ($pageContent) {

  // Stage Document
  $xpath = $app->getDocumentXPath($pageContent);

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
      if ($forceNotice || empty($serviceHistory) || ($serviceHistory->status != $serviceStatus)) {

        // inspect($service, $serviceHistory, $serviceName, $serviceStatus);

        // Save Status
        $app->savePluginOption('serviceHistory_'.$service->componentId, (object)array(
          'time' => time(),
          'name' => $serviceName,
          'status' => $serviceStatus
        ));

        // Trigger Notification
        $app->postActivityLog($serviceName . ' status changed from ' . (empty($serviceHistory) ? 'N/A' : $serviceHistory->status) . ' to ' . $serviceStatus);
        $serviceNotifications[] = (object)array(
          'componentId' => $service->componentId,
          'newStatus' => $serviceStatus,
          'oldStatus' => $serviceHistory->status
          );

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
  $publishedIncidentPosts = wp_list_pluck($query->posts, 'post_name'); // array_combine(wp_list_pluck($query->posts, 'ID'), wp_list_pluck($query->posts, 'post_name'));
  $relevantIncidentPosts  = array();
  $resolvedIncidentPosts  = array();
  $activeIncidentRows     = array();

  /**
   * Scan for Active Notifications
   */
  $incidentRows = $xpath->query('.//div[contains(@class, "unresolved-incidents")]//div[contains(@class, "unresolved-incident")]');
  foreach ($incidentRows AS $incidentRow) {

    /**
     * Parse Incident Details
     */
    $titleRow = $xpath->query('.//a[contains(@class, "actual-title")]', $incidentRow)->item(0);
    $incidentTitle = $app->translateContent($titleRow->textContent, $scanConfig->translations);
    $incidentPath = $titleRow->getAttribute('href');
    $incidentId = explode('/', $incidentPath);
    $incidentId = end($incidentId);
    $incidentUpdates = array();
    $incidentSeverity = preg_replace('/.*impact-(\w)/', '$1', $incidentRow->getAttribute('class'));
    if ($updateRows = $xpath->query('.//div[contains(@class, "updates")]//div[contains(@class, "update")]', $incidentRow)) {
      foreach ($updateRows AS $updateRow) {
        $updateType = $xpath->query('.//strong', $updateRow)->item(0)->textContent;
        $updateContent = $xpath->query('.//span[contains(@class, "whitespace-pre-wrap")]', $updateRow)->item(0)->textContent;
        $updateContent = $app->translateContent($updateContent, $scanConfig->translations);
        if ($updateCreated = $xpath->query('.//small', $updateRow)->item(0)) {
          $updateCreated = str_replace(' - ', ', ', $updateCreated->textContent);
          if ($updateCreatedTime = $xpath->query('.//small//span[@class="ago"]', $updateRow)->item(0)) {
            $updateCreatedTime = substr($updateCreatedTime->getAttribute('data-datetime-unix'), 0, 10);
          }
          else {
            $updateCreatedTime = strtotime($updateCreated);
          }
        }
        else {
          // No Created Date/Time
          continue;
        }
        $updateCreatedGMT = date('Y-m-d H:i:s', $updateCreatedTime);
        $incidentUpdates[] = (object)array(
          'updateType' => $updateType,
          'updateContent' => $updateContent,
          'updateCreated' => $updateCreated,
          'updateCreatedGMT' => $updateCreatedGMT,
          'updateCreatedTime' => $updateCreatedTime
          );
      }
    }

    // Stage if Valid
    if ($incidentUpdates) {
      $activeIncidentRows[ $incidentId ] = (object)array(
        'incidentTitle'      => $incidentTitle,
        'incidentPath'       => $incidentPath,
        'incidentUpdates'    => $incidentUpdates,
        'incidentSeverity'   => $incidentSeverity,
        'incidentLastUpdate' => $incidentUpdates ? $incidentUpdates[0]->updateCreatedGMT : null
        );
    }

  }

  /**
   * Isolate Incidents for Processing
   */
  $resolvedIncidentPosts = array_diff($publishedIncidentPosts, array_keys($activeIncidentRows));
  $updateIncidentIds = array_merge($resolvedIncidentPosts, array_keys($activeIncidentRows));
  $app->postActivityLog('Processing ' . count($updateIncidentIds) . ' incident(s) from ' . count($activeIncidentRows) . ' active, ' . count($resolvedIncidentPosts) . ' resolved.');

  /**
   * Process Resolved and Active Incidents
   */
  foreach ($updateIncidentIds AS $incidentId) {

    /**
     * Load Incident Post
     */
    $incidentPost = get_page_by_path( $incidentId, OBJECT, 'statuspage_incident' );

    /**
     * Check against last updated date
     * If this is Active, and the last update was already logged, we can skip
     */
    if ($incidentPost && !$forceProcess) {
      if (!empty($activeIncidentRows[$incidentId])) {
        if (
          $incidentPost->post_date_gmt == $activeIncidentRows[$incidentId]->incidentLastUpdate
          ||
          $incidentPost->post_modified_gmt == $activeIncidentRows[$incidentId]->incidentLastUpdate
          ) {
          // Skip
          continue;
        }
      }
    }

    /**
     * Pull Incident Details
     */
    $incidentPath = empty($activeIncidentRows[$incidentId]) ? '/incidents/' . $incidentId : $activeIncidentRows[$incidentId]->incidentPath;
    if ($incidentPost && ($incidentPathMeta = get_post_meta($incidentPost->ID, 'incidentPath'))){
      $incidentPath = $incidentPathMeta[0];
    }
    $incidentPageCacheTime = null;
    if ($incidentPost && ($incidentPageCacheTime = get_post_meta($incidentPost->ID, 'incidentPageCacheTime'))){
      $incidentPageCacheTime = $incidentPageCacheTime[0];
    }
    $incidentPageContent = null;
    if ($forceUpdate || empty($incidentPageCacheTime) || ($incidentPageCacheTime < time() - $cacheTimeout)) {
      $incidentPageContent = file_get_contents($scanTargetUrl . $incidentPath);
      if ($incidentPageContent) {
        $app->postActivityLog('Pulled Incident Details Page');
        $incidentPageCacheTime = time();
        if ($incidentPost) {
          update_post_meta($incidentPost->ID, 'incidentPageCache', $incidentPageContent);
          update_post_meta($incidentPost->ID, 'incidentPageCacheTime', $incidentPageCacheTime);
        }
      }
      else {
        // Failed, abort
        $app->postActivityLog('Failed to pull Incident Details Page');
        continue;
      }
    }
    else {
      $incidentPageContent = get_post_meta($incidentPost->ID, 'incidentPageCache')[0];
    }

    /**
     * Stage Document
     */
    $detailsXPath = $app->getDocumentXPath($incidentPageContent);

    /**
     * Parse Main Details
     */
    $titleRow = $detailsXPath->query('.//div[contains(@class, "incident-name")]')->item(0);
    $incidentTitle = $app->translateContent($titleRow->textContent, $scanConfig->translations);
    $incidentSeverity = preg_replace('/.*impact-(\w)/', '$1', $titleRow->getAttribute('class'));
    $incidentAffectedServices = $app->trimContent($detailsXPath->query('//div[contains(@class, "components-affected")]')->item(0)->textContent);

    /**
     * Parse Update Rows
     */
    $incidentUpdates = array();
    $incidentContent = array();
    $updateRows = $detailsXPath->query('.//div[contains(@class, "incident-updates-container")]//div[contains(@class, "update-row")]');
    foreach ($updateRows AS $updateRow) {
      $updateType        = $app->trimContent($detailsXPath->query('.//div[contains(@class, "update-title")]', $updateRow)->item(0)->textContent);
      $updateTypeCode    = strtolower(preg_replace('/\s+/', '-', $updateType));
      $updateContent     = $app->trimContent($detailsXPath->query('.//div[contains(@class, "update-body")]//span[contains(@class, "whitespace-pre-wrap")]', $updateRow)->item(0)->textContent);
      $updateContent     = $app->translateContent($updateContent, $scanConfig->translations);
      $updateCreatedTime = $detailsXPath->query('.//div[contains(@class, "update-timestamp")]//span[@class="ago"]', $updateRow)->item(0)->getAttribute('data-datetime-unix');
      $updateCreatedTime = substr($updateCreatedTime, 0, 10);
      $updateCreatedGMT  = date('Y-m-d H:i:s', $updateCreatedTime);
      $incidentUpdates[] = (object)array(
        'updateType' => $updateType,
        'updateTypeCode' => $updateTypeCode,
        'updateContent' => $updateContent,
        'updateCreatedGMT' => $updateCreatedGMT,
        'updateCreatedTime' => $updateCreatedTime
        );
      $incidentContent[] = '<div class="statuspage-incident '. $updateTypeCode .'">'
                         . '<div class="statuspage-incident-type">'. esc_html($updateType) .'</div>'
                         . '<div class="statuspage-incident-content">'. esc_html($updateContent) .'</div>'
                         . '<div class="statuspage-incident-date">'. esc_html(get_date_from_gmt($updateCreatedGMT, 'F jS, Y - g:ia')) .'</div>'
                         . '</div>';
    }
    $incidentLastUpdated = reset($incidentUpdates)->updateCreatedGMT;

    /**
     * Create or Update Post
     */
    $isNew = empty($incidentPost);
    $isUpdated = false;
    if ($isNew) {
      $post_id = wp_insert_post(array(
        'post_type'         => 'statuspage_incident',
        'post_title'        => $incidentTitle,
        'post_status'       => 'invalid',
        'post_content'      => implode("\n", $incidentContent),
        'post_name'         => $incidentId,
        'post_date'         => get_date_from_gmt($incidentLastUpdated),
        'post_date_gmt'     => $incidentLastUpdated,
        'post_modified'     => get_date_from_gmt($incidentLastUpdated),
        'post_modified_gmt' => $incidentLastUpdated,
        'meta_input'        => array(
          'incidentSeverity'         => $incidentSeverity,
          'incidentLastUpdated'      => $incidentLastUpdated,
          'incidentAffectedServices' => $incidentAffectedServices,
          'incidentPageCache'        => $incidentPageContent,
          'incidentPageCacheTime'    => $incidentPageCacheTime,
        )
      ));
      if (empty($post_id)) {
        // Failed
        continue;
      }
      $query = new WP_Query(array(
        'p' => $post_id,
        'post_type' => 'statuspage_incident'
      ));
      $incidentPost = reset($query->posts);
    }
    $incidentPostLastUpdated = get_post_meta($incidentPost->ID, 'incidentLastUpdated')[0];
    $isUpdated = ($incidentLastUpdated != $incidentPostLastUpdated);
    if ($isUpdated || $forceProcess) {
      $res = wp_update_post(array(
        'ID'                => $incidentPost->ID,
        'post_title'        => $incidentTitle,
        'post_content'      => implode("\n", $incidentContent),
        'post_modified'     => get_date_from_gmt($incidentLastUpdated),
        'post_modified_gmt' => $incidentLastUpdated,
        'meta_input'        => array(
          'incidentSeverity'         => $incidentSeverity,
          'incidentLastUpdated'      => $incidentLastUpdated,
          'incidentAffectedServices' => $incidentAffectedServices
        )
      ), true);
    }

    /**
     * Review Affected Services
     */
    if (in_array($incidentId, array_keys($activeIncidentRows))) {
      foreach ($scanConfig->services AS $service) {
        if (strpos(strtolower($incidentAffectedServices), '('.strtolower($service->componentName).')') !== false) {
          $relevantIncidentPosts[] = $incidentId;
          if ($isNew || $isUpdated) {
            $updatesFound = 0;
            foreach ($incidentUpdates AS $incidentUpdate) {
              if (!$updatesFound || ($incidentPostLastUpdated < $incidentUpdate->updateCreatedGMT))
                $updatesFound++;
                $serviceNotifications[] = (object)array(
                  'componentId' => $service->componentId,
                  'incidentUpdate' => $incidentUpdate
                  );
            }
          }
        }
      }
    }

  }

  /**
   * Publish Relevant Posts
   */
  foreach ($relevantIncidentPosts AS $incidentId) {
    if (!in_array($incidentId, $publishedIncidentPosts)) {
      $app->postActivityLog('Publishing Incident ' . $incidentId);
      $post = get_page_by_path( $incidentId, OBJECT, 'statuspage_incident' );
      wp_update_post(array(
        'ID' => $post->ID,
        'post_status' => 'publish'
      ));
    }
  }

  /**
   * Disable Old Incidents
   */
  foreach ($publishedIncidentPosts AS $incidentId) {
    if (!in_array($incidentId, $relevantIncidentPosts)) {
      $app->postActivityLog('Archiving Incident ' . $incidentId);
      $post = get_page_by_path( $incidentId, OBJECT, 'statuspage_incident' );
      wp_update_post(array(
        'ID' => $post->ID,
        'post_status' => 'archive'
      ));
    }
  }

  /**
   * Process Notification Stack
   */
  StatusPageNotification::processNotificationStack($serviceNotifications);

}

exit;