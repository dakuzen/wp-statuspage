<?php

namespace WebuddhaInc\StatusPage;

class App {

  /**
   * [$version description]
   * @var string
   */
  public static $version = '1.0.0';

  /**
   * Instance
   * @var App object
   */
  public static $instance;

  /**
   * Configuration
   * @var array
   */
  public $config = array(
    'app-slug' => 'statuspage',
    'compile-less' => true
    );

  /**
   * [__construct description]
   */
  public function __construct(){
    $this->registerPostType();
    $this->registerTaxonomy();
    $this->registerShortcodes();
    $this->registerManager();
    $this->registerAdminMenu();
  }

  /**
   * [getInstance description]
   * @return [type] [description]
   */
  public static function getInstance(){
    if (empty(static::$instance))
      static::$instance = new self();
    return static::$instance;
  }

  /**
   * [init description]
   * @return [type] [description]
   */
  public function init(){

    /**
     * Autoloader
     */
    if (file_exists(__DIR__ . '/vendor/autoload.php'))
      require __DIR__ . '/vendor/autoload.php';

    /**
     * Enque Styles
     */
    wp_register_style('statuspage-fa', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css');
    wp_enqueue_style('statuspage-fa');
    wp_register_style('statuspage-style', plugins_url('assets/styles.' . ($this->getConfig('compile-less') ? 'less' : 'css'), __FILE__));
    wp_enqueue_style('statuspage-style');

    /**
     * Enque Scripts
     */
    wp_register_script('statuspage-script', plugins_url('assets/script.js', __FILE__), array('jquery'), '1.'.rand(1000,9999));
    wp_enqueue_script('statuspage-script');
    wp_register_script('jquery-ui', 'https://code.jquery.com/ui/1.13.1/jquery-ui.min.js', array('jquery'), '1.13.1');
    wp_enqueue_script('jquery-ui');

    /**
     * Cron
     */
    if (preg_match('/^\/'.$this->getConfig('app-slug').'\/cron(|\/.*?)(\?.*$|)$/', $_SERVER['REQUEST_URI'], $match)){
      $this->loadController('cron.php', array());
      exit;
    }

    /**
     * Validate User
     */
    $user = wp_get_current_user();
    if (in_array('cc_manager', (array)$user->roles) || in_array('cc_admin', (array)$user->roles)) {

      /** Abort for Special Pages */
      if (strpos($_SERVER['REQUEST_URI'], '/wp-login.php') !== false) {
        return;
      }

      /** Limit to Chart Manager */
      if (strpos($_SERVER['REQUEST_URI'], '/'.$this->getConfig('app-slug')) === false) {
        wp_redirect('/'.$this->getConfig('app-slug'));
        exit;
      }

    }

    // Redirect Guest
    else if (strpos($_SERVER['REQUEST_URI'], '/'.$this->getConfig('app-slug')) !== false) {
      wp_redirect('/');
      exit;
    }

  }

  /**
   * [getConfig description]
   * @param  [type] $key     [description]
   * @param  [type] $default [description]
   * @return [type]          [description]
   */
  public function getConfig($key, $default=null){
    if (array_key_exists($key, $this->config))
      return $this->config[$key];
    return $default;
  }

  /**
   * [install description]
   * @return [type] [description]
   */
  public function install(){
    global $wpdb;
  }

  /**
   * [upgrade description]
   * @return [type] [description]
   */
  public function upgrade(){
  }

  /**
   * [uninstall description]
   * @return [type] [description]
   */
  public function uninstall(){
  }

  /**
   * [route description]
   * @param  [type] $continue [description]
   * @param  [type] $route    [description]
   * @param  array  $request  [description]
   * @return [type]           [description]
   */
  public function route($continue, $route, $request=array()){
    global $wpdb;
    if (!is_user_logged_in())
      return $continue;
    $user = wp_get_current_user();
    $isManager = in_array('cc_manager', $user->roles);
    $isAdmin = in_array('cc_admin', $user->roles);
    if (!$isManager && !$isAdmin)
      return $continue;
    $route = preg_replace('/^\//', '', preg_replace('/[^a-z\-\_\/]/', '', $route));
    $routeParts = explode('/', $route);
    $routeParams = array();
    while (count($routeParts)) {
      $routeFile = implode('/', $routeParts).'.php';
      if (!file_exists(__DIR__.'/controllers/'.$routeFile)){
        $routeFile = null;
        $routeParams[] = array_pop($routeParts);
      }
      else
        break;
    }
    if (empty($routeFile))
      $routeFile = 'index.php';
    $this->loadController($routeFile, array(
      'isAdmin' => $isAdmin,
      'isManager' => $isManager,
      'routeParams' => $routeParams
      ));
    exit;
  }

  /**
   * [loadController description]
   * @param  [type] $controller [description]
   * @param  array  $request    [description]
   * @return [type]             [description]
   */
  public function loadController($controller, $request=array()) {

    global $wpdb;

    // Stage
    unset($request['wpdb'], $request['controller']);
    extract($request);

    // Controller
    $controllerPath = __DIR__.'/controllers/' . $controller;
    if (file_exists($controllerPath))
      require $controllerPath;
    else
      echo '[invalid controller '.$controller.']';

  }

  /**
   * [loadView description]
   * @param  [type] $view    [description]
   * @param  array  $request [description]
   * @return [type]          [description]
   */
  public function loadView($view, $request=array()){

    global $wpdb;

    // Stage
    unset($request['wpdb'], $request['view']);
    extract($request);

    // View Template
    $viewPath = locate_template('statuspage/'.$view);
    if (empty($viewPath))
      $viewPath = __DIR__.'/templates/'.$view;
    if (file_exists($viewPath))
      require $viewPath;
    else
      echo '[invalid view '.$controller.']';

  }

  /**
   * Undocumented function
   *
   * @return void
   */
  public function registerPostType(){
    register_post_type('statuspage_incident', array(
        'labels' => array(
          'name' => __('Status Page Incidents'),
          'singular_name' => __('Status Page Incident'),
        ),
        'public'              => true,
        'has_archive'         => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'exclude_from_search' => true,
        'publicly_queryable'  => true
      )
    );
    register_post_status( 'archive', array(
      'label'                     => _x( 'Archived', 'post' ),
      'public'                    => true,
      'label_count'               => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>' ),
      'post_type'                 => array( 'statuspage_incident' ), // Define one or more post types the status can be applied to.
      'show_in_admin_all_list'    => true,
      'show_in_admin_status_list' => true,
      'show_in_metabox_dropdown'  => true,
      'show_in_inline_dropdown'   => true,
      'dashicon'                  => 'dashicons-businessman',
    ));
    register_post_status( 'invalid', array(
      'label'                     => _x( 'Invalid', 'post' ),
      'public'                    => true,
      'label_count'               => _n_noop( 'Invalid <span class="count">(%s)</span>', 'Invalid <span class="count">(%s)</span>' ),
      'post_type'                 => array( 'statuspage_incident' ), // Define one or more post types the status can be applied to.
      'show_in_admin_all_list'    => true,
      'show_in_admin_status_list' => true,
      'show_in_metabox_dropdown'  => true,
      'show_in_inline_dropdown'   => true,
      'dashicon'                  => 'dashicons-businessman',
    ));

    add_action('admin_footer-post.php',function(){
      global $post;
      $complete = '';
      $label = '';
      if($post->post_type == 'statuspage_incident') {
        foreach (array(
          'archive' => 'Archived',
          'invalid' => 'Invalid'
          ) AS $statusKey => $statusLabel) {
          if ( $post->post_status == $statusKey ) {
            $label = $statusLabel;
            $complete = ' selected=\"selected\"';
          }
          $script = <<<SD
          jQuery(document).ready(function($){
            $("select#post_status").append("<option value=\"{$statusKey}\" '.$complete.'>{$statusLabel}</option>");
            if( "{$post->post_status}" == "{$statusKey}" ){
              $("span#post-status-display").html("{$statusLabel}");
              $("input#save-post").val("Save {$statusLabel}");
            }
            var jSelect = $("select#post_status");
            $("a.save-post-status").on("click", function(){
              if( jSelect.val() == "{$statusKey}" ){
                $("input#save-post").val("Save {$statusLabel}");
              }
            });
          });
SD;
          echo '<script type="text/javascript">' . $script . '</script>';
        }
      }
    });

    add_action('admin_footer-edit.php',function() {
      global $post;
      if( $post->post_type == 'statuspage_incident' ) {
        echo "<script>jQuery(document).ready( function() {
          jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"archive\">Archived</option>' );
          jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"invalid\">Invalid</option>' );
        });</script>";
      }
    });

    add_filter( 'display_post_states', function( $statuses ) {
      global $post;
      if( $post->post_type == 'statuspage_incident') {
        if ( get_query_var( 'post_status' ) != 'archive' ) { // not for pages with all posts of this status
          if ( $post->post_status == 'archive' ) {
            return array( 'Archived' );
          }
        }
        if ( get_query_var( 'post_status' ) != 'invalid' ) { // not for pages with all posts of this status
          if ( $post->post_status == 'invalid' ) {
            return array( 'Invalid' );
          }
        }
      }
      return $statuses;
    });

  }

  /**
   * [registerTaxonomy description]
   * @return [type] [description]
   */
  public function registerTaxonomy(){

    // Disabled
    return;

    /**
     * Symbol
     */

    // Register the symbol taxonomy
    register_taxonomy('symbol', array('post', 'page'), array(
      // Hierarchical taxonomy (like categories)
      'hierarchical' => false,
      // This array of options controls the labels displayed in the WordPress Admin UI
      'labels' => array(
        'name'              => _x( 'Symbols', 'taxonomy general name' ),
        'singular_name'     => _x( 'Symbol', 'taxonomy singular name' ),
        'search_items'      =>  __( 'Search Symbols' ),
        'all_items'         => __( 'All Symbols' ),
        'parent_item'       => __( 'Parent Symbol' ),
        'parent_item_colon' => __( 'Parent Symbol:' ),
        'edit_item'         => __( 'Edit Symbol' ),
        'update_item'       => __( 'Update Symbol' ),
        'add_new_item'      => __( 'Add New Symbol' ),
        'new_item_name'     => __( 'New Symbol Name' ),
        'menu_name'         => __( 'Symbols' ),
      ),
      // Control the slugs used for this taxonomy
      'rewrite' => array(
        'slug' => 'symbols', // This controls the base slug that will display before each term
        'with_front' => false, // Don't display the category base before "/locations/"
        'hierarchical' => false // This will allow URL's like "/locations/boston/cambridge/"
      ),
    ));

    // Add Symbol / Inception Date to the Taxonomy forms
    add_action('symbol_add_form_fields', function( $taxonomy ) {
      ?>
      <div class="form-field">
        <label for="inception_date">Inception Date</label>
        <input type="text" name="inception_date" id="inception_date" />
        <p>Date of product inception.</p>
      </div>
      <?php
    }, 10, 1);
    add_action('symbol_edit_form_fields', function( $term, $taxonomy ) {
      $value = get_term_meta($term->term_id, 'inception_date', true);
      ?>
      <tr class="form-field">
        <th>
          <label for="inception_date">Inception Date</label>
        </th>
        <td>
          <input name="inception_date" id="inception_date" type="text" value="<?= esc_attr( $value ) ?>" />
          <p class="description">Date of product inception.</p>
        </td>
      </tr><?php
    }, 10, 2);
    add_action( 'created_symbol', function( $term_id ) {
      update_term_meta($term_id, 'inception_date', sanitize_text_field($_POST['inception_date']));
    }, 10, 1);
    add_action( 'edited_symbol', function( $term_id ) {
      update_term_meta($term_id, 'inception_date', sanitize_text_field($_POST['inception_date']));
    }, 10, 1);

  }

  /**
   * [registerShortcodes description]
   * @return [type] [description]
   */
  public function registerShortcodes(){
    add_shortcode('statuspage', function($params) {

      // Cron Time
      if (@$params['view'] == 'crontime') {
        ob_start();
        $this->loadView('crontime.php', $params);
        return ob_get_clean();
      }

      // View
      $view = in_array(@$params['view'], array('notices', 'archive', 'status', 'legend', 'subscribe')) ? @$params['view'] : 'status';

      // Load Controller
      ob_start();
      $this->loadController($view.'.php', array_merge($params, array(
        )));
      return ob_get_clean();

    });
  }

  /**
   * [registerManager description]
   * @return [type] [description]
   */
  public function registerManager(){
    add_role('sp_manager', 'StatusPage Manager', get_role('subscriber')->capabilities);
    add_role('sp_admin', 'StatusPage Admin', get_role('subscriber')->capabilities);
  }

  /**
   * Undocumented function
   *
   * @return void
   */
  public function getScanConfig(){
    if ($this->getPluginOption('scanConfigOption', 'file') == 'file') {
      $scanConfigFile = $this->getPluginOption('scanConfigFile');
      if (file_exists($scanConfigFile)) {
        $scanConfig = json_decode(file_get_contents($scanConfigFile));
      }
    }
    else {
      $scanConfig = json_decode($this->getPluginOption('scanConfig'));
    }
    if (empty($scanConfig)) {
      $scanConfig = (object)array();
    }
    return $scanConfig;
  }

  /**
   * Undocumented function
   *
   * @param [type] $key
   * @param [type] $default
   * @return void
   */
  public function getPluginOption( $key, $default=null ){
    return  get_option( $this->getConfig('app-slug').'_'.$key, $default );
  }

  /**
   * Undocumented function
   *
   * @param [type] $key
   * @param [type] $value
   * @return void
   */
  public function savePluginOption( $key, $value ){
    update_option( $this->getConfig('app-slug').'_'.$key, $value );
  }

  /**
   *
   */
  public function registerAdminMenu(){
    add_action('admin_menu', function(){
      // add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
      // add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
      add_submenu_page(
        'options-general.php',
        'StatusPage Settings',
        'StatusPage Settings',
        'administrator',
        $this->getConfig('app-slug').'-settings',
        array( $this, 'displayPluginAdminSettings' )
        );
      });
    /*
    register_setting(
      $this->getConfig('app-slug').'_settings',
      $this->getConfig('app-slug').'_settings',
      array()
      );
    add_settings_field(
      'alertEmail',
      __('Alert Email'),
      array($this, 'settingFieldCallback'),
      'statuspage-settings',
      'default',
      array()
      );
    add_filter( 'query_vars', function($vars){
      $vars[] = $this->getConfig('app-slug').'_settings';
      return $vars;
    });
    */
  }

  public function displayPluginAdminSettings(){
    $this->loadController('settings.php', array());
  }

  /**
   * [getDate description]
   * @param  string $format [description]
   * @return [type]         [description]
   */
  public function getDate($format = 'Y-m-d'){
    return current_datetime()->format($format);
  }

  /**
   * [sendNotification description]
   * @param  [type] $subject [description]
   * @param  [type] $message [description]
   * @return [type]          [description]
   */
  public function sendNotification($subject, $view, $data){
    $emails = explode("\n", get_option('statuspage_notify_emails'));
    foreach ($emails AS $email) {
      $data['recipient_email'] = $email;
      ob_start();
      $this->loadView($view, $data);
      $message = ob_get_clean();
      wp_mail($email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
    }
  }

  /**
   * Undocumented function
   *
   * @param [type] $content
   * @param [type] $translations
   * @return void
   */
  public function translateContent($content, $translations){
    foreach ($translations AS $seek => $translation) {
      $content = str_replace($seek, $translation, $content);
    }
    return $content;
  }

}
