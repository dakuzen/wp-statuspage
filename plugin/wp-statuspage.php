<?php
/*
Plugin Name:  Status Page
Plugin URI:   https://github.com/WebuddhaInc/wp-plugin-statuspage
Description:  Atlassian Status Page Mirroring
Version:      1.5.0
Author:       David Hunt
Author URI:   ~
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

use WebuddhaInc\StatusPage\App as StatusPageApp;

if( !function_exists('inspect') ){
  function inspect(){
    echo '<pre>' . print_r(func_get_args(), true) . '</pre>';
  }
}

require 'StatusPage/App.php';
require 'StatusPage/Subscriber.php';
require 'StatusPage/Notification.php';

add_action('init', function(){
  StatusPageApp::getInstance()->init();
});

register_activation_hook( __FILE__, function(){
  StatusPageApp::getInstance()->install();
});

register_deactivation_hook( __FILE__, function(){
  StatusPageApp::getInstance()->uninstall();
});

add_filter('do_parse_request', function($continue, WP $wp){
  $appSlug = StatusPageApp::getInstance()->getConfig('app-slug');
  if (preg_match('/^\/'.$appSlug.'(|\/.*?)(\?.*$|)$/', $_SERVER['REQUEST_URI'], $match)){
    do_action('parse_request', $wp);
    do_action('wp', $wp);
    return StatusPageApp::getInstance()->route($continue, $match[1], $_REQUEST);
  }
  return $continue;
}, 1, 2);
