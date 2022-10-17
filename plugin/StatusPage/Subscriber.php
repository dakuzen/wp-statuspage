<?php

namespace WebuddhaInc\StatusPage;

use WebuddhaInc\StatusPage\App as StatusPageApp;

class Subscriber {

  public static function subscribe($email){
    global $wpdb;
    $email = filter_var(strtolower($email), FILTER_VALIDATE_EMAIL);
    if (is_email($email)) {
      $app = StatusPageApp::getInstance();
      $app->postActivityLog('Subscriber Added: ' . $email);
      $data = array(
        'subscriber_email' => $email,
        'subscriber_date' => current_time('mysql'),
        'subscriber_validation_key' => substr(md5(time().':'.md5($email)),rand(0,20), 12)
      );
      $result = $wpdb->insert("{$wpdb->prefix}statuspage_subscriptions", $data);
      return $result ? true : false;
    }
    return false;
  }

  public static function unsubscribe($email=null, $key=null){
    global $wpdb;
    $subscriber = $email ? self::findSubscriber($email) : null;
    if (empty($subscriber) && $key) {
      $results = $wpdb->get_results($wpdb->prepare("
      SELECT *
      FROM `{$wpdb->prefix}statuspage_subscriptions`
      WHERE `subscriber_validation_key` = %s
      LIMIT 1
      ", $key));
      if ($results)
        $subscriber = reset($results);
    }
    if ($subscriber){
      $app = StatusPageApp::getInstance();
      $app->postActivityLog('Subscriber Removed: ' . $subscriber->subscriber_email);
      if ($wpdb->delete( $wpdb->prefix.'statuspage_subscriptions', ['subscriber_id' => $subscriber->subscriber_id], [ '%d' ] )) {
        return true;
      }
    }
    return false;
  }

  public static function validate($key){
    global $wpdb;
    $results = $wpdb->get_results($wpdb->prepare("
      SELECT *
      FROM `{$wpdb->prefix}statuspage_subscriptions`
      WHERE `subscriber_validation_key` = %s
      LIMIT 1
      ", $key));
    if ($results) {
      $app = StatusPageApp::getInstance();
      $subscriber = reset($results);
      if ($subscriber->subscriber_validated)
        return 2;
      $app->postActivityLog('Subscriber Validated: ' . $subscriber->subscriber_email);
      $update = $wpdb->update(
        $wpdb->prefix.'statuspage_subscriptions',
        array('subscriber_validated' => 1),
        array('subscriber_id' => $subscriber->subscriber_id)
        );
      return $update ? 1 : 0;
    }
    return 0;
  }

  public static function sendValidation($email){
    $subscriber = self::findSubscriber($email);
    if ($subscriber) {
      $app = StatusPageApp::getInstance();
      $tmpl = get_post( $app->getPluginOption('emailTmplId_emailValidate') );
      if ($tmpl) {
        $data = array(
          'subscriber_email' => $subscriber->subscriber_email,
          'subscriber_validation_url' => $app->getRouteUrl('validate?key=' . $subscriber->subscriber_validation_key),
          'subscriber_validation_link' => '<a href="'.$app->getRouteUrl('validate?key=' . $subscriber->subscriber_validation_key).'" title="'.esc_attr__('Validate Email', 'statuspage').'">'. esc_html__('Validate Email', 'statuspage') .'</a>'
        );
        $subject = $app->interpolateContent($tmpl->post_title, $data);
        $message = $app->interpolateContent($tmpl->post_content, $data);
        wp_mail(
          $subscriber->subscriber_email,
          $subject,
          $message
          );
      }
    }
    return false;
  }

  public static function findSubscriber( $email ){
    global $wpdb;
    $email = filter_var(strtolower($email), FILTER_VALIDATE_EMAIL);
    if (is_email($email)) {
      $results = $wpdb->get_results( $wpdb->prepare("
        SELECT *
        FROM `{$wpdb->prefix}statuspage_subscriptions`
        WHERE `subscriber_email` = %s
        ", $email) );
      if ($results)
        return $results[0];
    }
    return false;
  }

}
