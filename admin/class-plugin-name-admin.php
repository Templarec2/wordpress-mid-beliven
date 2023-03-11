<?php
  
  /**
   * The admin-specific functionality of the plugin.
   *
   * @link       http://example.com
   * @since      1.0.0
   *
   * @package    Plugin_Name
   * @subpackage Plugin_Name/admin
   */
  
  class Plugin_Name_Admin
  {
    
    private $plugin_name;
    
    private $version;
    
    public function __construct($plugin_name, $version)
    {
      
      $this->plugin_name = $plugin_name;
      $this->version = $version;
      
      /*
        Registrazione nuovo custom post type logs
      */
      add_action('init', array($this, 'register_custom_post_types'));
      
      /*
       Assegnazione capabilities ad editor e Admin
     */
      add_action('admin_init', array($this, 'add_theme_caps'));
      
      /*
      Creazione option page
    */
      add_action('admin_menu', array($this, 'logs_option_page'));
      
      /*
       Aggiunta colonne nella visualizzazione pagina admin del custom post type
     */
      add_filter('manage_posts_columns', array($this, 'custom_logs_table_head'));
      
      /*
       Assegnazione fields dei meta alle colonne sopra create
     */
      add_action('manage_posts_custom_column', array($this, 'custom_logs_table_content'), 10, 2);
      
      /*
      Aggiunta schedule per il cron
    */
      add_filter('cron_schedules', array($this, 'my_minutly'));
      
      /*
      Funzione da eseguire per il cron
    */
      add_action('bl_cron_log_retention', array($this, 'logs_retention'));
      
      /*
      Registrazione rotte
    */
      
      add_action( 'rest_api_init', array($this, 'logs_endpoints'));
      
    }
    
    /*
      Funzioni
    */
    public function enqueue_styles()
    {
      
      
      wp_enqueue_style(
          $this->plugin_name,
          plugin_dir_url(__FILE__).'css/plugin-name-admin.css',
          array(),
          $this->version,
          'all'
      );
      
    }
    
    public function enqueue_scripts()
    {
      
      wp_enqueue_script(
          $this->plugin_name,
          plugin_dir_url(__FILE__).'js/plugin-name-admin.js',
          array('jquery'),
          $this->version,
          false
      );
      
    }
    
    /*
     Funzione che registra il CPT
    */
    public function register_custom_post_types()
    {
      $customPostTypeArgs = array(
          'label' => 'logs',
          'labels' =>
              array(
                  'name' => _x('logs', 'plural'),
                  'singular_name' => _x('log', 'singular'),
                  'add_new' => _x('Aggiungi nuovo log', 'add new'),
                  'add_new_item' => __('Aggiungi nuovi logs'),
                  'edit_item' => __('Modifica log'),
                  'new_item' => __('Nuovo log'),
                  'view_item' => __('Visualizza log'),
                  'search_items' => __('Cerca log'),
                  'not_found' => __('Log non trovato'),
                  'not_found_in_trash' => __('Nessun log trovato nel cestino'),
                  'menu_name' => _x('logs', 'admin menu'),
                  'name_admin_bar' => _x('logs', 'admin bar'),
              ),
          'public' => true,
          'description' => 'Logs',
          'exclude_from_search' => false,
          'show_ui' => true,
          'menu_position' => 26,
          'menu_icon' => "dashicons-text",
          'capabilities' => array(
              'edit_post' => 'edit_log',
              'edit_posts' => 'edit_logs',
              'edit_others_posts' => 'edit_other_logs',
              'publish_posts' => 'publish_logs',
              'read_post' => 'read_log',
              'read_private_posts' => 'read_private_logs',
              'delete_post' => 'delete_log',
          ),
          'map_meta_cap' => true,
          'taxonomies' => array('category', 'post_tag'),
      );

      register_post_type('logs', $customPostTypeArgs);
    }
    
    /*
       Funzione che assegna capabilities
     */
    public function add_theme_caps()
    {
     
      $admins = get_role('administrator');
      
      $admins->add_cap('edit_log');
      $admins->add_cap('edit_logs');
      $admins->add_cap('edit_other_logs');
      $admins->add_cap('publish_logs');
      $admins->add_cap('read_log');
      $admins->add_cap('read_private_logs');
      $admins->add_cap('delete_log');
      
      $editors = get_role('editor');
      
      $editors->add_cap('edit_log');
      $editors->add_cap('edit_logs');
      $editors->add_cap('edit_other_logs');
      $editors->add_cap('publish_logs');
      $editors->add_cap('read_log');
      $editors->add_cap('read_private_logs');
      $editors->add_cap('delete_log');
      
      $authors = get_role('author');
      $authors->add_cap('edit_log', false);
      $authors->add_cap('edit_logs', false);
      $authors->add_cap('edit_other_logs', false);
      $authors->add_cap('publish_logs');
      $authors->add_cap('read_log', false);
      $authors->add_cap('read_private_logs', false);
      $authors->add_cap('delete_log', false);
      
      $contributors = get_role('contributor');
      
      $contributors->add_cap('publish_logs');
      
    }
    
    /*
      Funzione che crea colonne nel view del CPT
    */
    public function custom_logs_table_head($columns)
    {
      $post_type = get_post_type();
      if ($post_type === 'logs') {
        unset($columns['categories']);
        unset($columns['tags']);
        unset($columns['date']);
        $columns['log_datetime'] = 'Data/ora log';
        $columns['user_id'] = 'id Utente';
        $columns['user_ip'] = 'IP Utente';
        $columns['log_post_type'] = 'Tipo post';
        $columns['log_action'] = 'Azione';
        $columns['log_metadata'] = 'Dati aggiuntivi';
        
      }
      return $columns;
     }
    
    /*
     Funzione che assegna fields meta alle colonne CPT
    */
    public function custom_logs_table_content($name)
    {
      global $post;
      $post_type = get_post_type();
      if ($post_type === 'logs') {
        switch ($name) {
          case 'log_datetime':
            $eventDate = get_post_meta(get_the_id(), $this->plugin_name.'_log_datetime', true);
            echo $eventDate;
            break;
          case 'user_id':
            $eventDate = get_post_meta(get_the_id(), $this->plugin_name.'_user_id', true);
            echo $eventDate;
            break;
          case 'user_ip':
            $eventDate = get_post_meta(get_the_id(), $this->plugin_name.'_user_ip', true);
            echo $eventDate;
            break;
          case 'log_post_type':
            $eventDate = get_post_meta(get_the_id(), $this->plugin_name.'_log_post_type', true);
            echo $eventDate;
            break;
          case 'log_action':
            $eventDate = get_post_meta(get_the_id(), $this->plugin_name.'_log_action', true);
            echo $eventDate;
            break;
          case 'log_metadata':
            $eventDate = get_post_meta(get_the_id(), $this->plugin_name.'_log_metadata', true);
            echo $eventDate;
            break;
        }
        
      }
      
    }
  
    /*
          Funzione options page
        */
    public function logs_option_page()
    {
     
      add_menu_page(
          'Beliven logger options',
          'Beliven logger options',
          'manage_options',
          'beliven_logger',
          array($this, 'beliven_option_page'),
          'dashicons-text',
          27
      );
    }
    
    /*
     Funzione che crea la option page
    */
    public function beliven_option_page()
    {
      if (isset($_POST['del_logs'])) {
        $value = $_POST['del_logs'];
        update_option('del_logs', $value);
      }
      if (isset($_POST['retention_datetime_logs'])) {
        $value = $_POST['retention_datetime_logs'];
        update_option('retention_datetime_logs', $value);
      }
      
      
      require_once 'partials/options.php';
    }
  
    /*
      Impostazione schedule ogni minuto
    */
    function my_minutly($schedules)
    {
      $schedules['minutly'] = array(
          'interval' => 60,
          'display' => __('Every minute'),
      );
      
      return $schedules;
    }
  
    /*
      Funziona che elimina i log controllando se sono abbastanza vecchi
    */
    public function logs_retention()
    {
      
      $num_gg = get_option('retention_datetime_logs');
      $num_gg = (int)$num_gg;
      $oggi = date_create();
      date_sub($oggi, date_interval_create_from_date_string("$num_gg days"));

      $args = array(
          'numberposts' => -1,
          'post_type' => 'logs',
          'date_query' => array('before' => date_format($oggi, 'Y-m-d')),
      );
    
      $logs = get_posts($args);
      if (empty($logs)) {
        return;
      }
      foreach ($logs as $log) {

        wp_delete_post($log->ID, true);
        delete_post_meta($log->ID, $this->plugin_name.'_log_datetime');
        delete_post_meta($log->ID, $this->plugin_name.'_user_id');
        delete_post_meta($log->ID, $this->plugin_name.'_user_ip');
        delete_post_meta($log->ID, $this->plugin_name.'_log_post_type');
        delete_post_meta($log->ID, $this->plugin_name.'_log_action');
        delete_post_meta($log->ID, $this->plugin_name.'_log_metadata');
      
      }

    
    
    }
    /*
          Registrazione rotte endpoint
        */
    public function logs_endpoints(){
      register_rest_route( 'logger',
          '/token',
          array(
              'methods' => 'POST',
              'args' => array(),
              'callback' => array($this, 'generate_token'),
          ) );
      register_rest_route( 'logger',
          '/logs',
          array(
          'methods' => 'GET',
              'args' => array(),
          'callback' => array($this, 'log_list'),
              'permission_callback' => function () {
                return current_user_can('edit_others_posts');
              }
      ) );
    }
  
    /*
      Funzionalità endpoint token per auth
    */
    public function generate_token(){
      if(!isset($_POST['username'])) return 'Username required';
      if(!isset($_POST['password'])) return 'password required';
      if(!isset($_POST['app_pass'])) return 'app_pass required';
      $username = $_POST['username'];
      $password = $_POST['password'];
      $app_pass = $_POST['app_pass'];
      
       if(user_pass_ok($username, $password)){
         $token = base64_encode($username . ':'.$app_pass);
         $result = [
             'token' => $token
         ];
         return $result;
       } else {
         return [
             'status' => false,
             'message' => 'Invalid credentials'
         ];
       }
    }
  
    /*
      Funzionalità endpoint per visualizzare i log
    */
    public function log_list(){
      $args = array(
          'numberposts' => -1,
          'post_type' => 'logs',
        
      );
      $logs = get_posts($args);
      $results = [];
      foreach ($logs as $log){
        $log_datetime = get_post_meta($log->ID, $this->plugin_name.'_log_datetime', true );
        $user_id = get_post_meta($log->ID, $this->plugin_name.'_user_id', true );
        $user_ip = get_post_meta($log->ID, $this->plugin_name.'_user_ip', true );
        $log_action = get_post_meta($log->ID, $this->plugin_name.'_log_action', true );
        $log_metadata = get_post_meta($log->ID, $this->plugin_name.'_log_metadata', true );
        $log_post_type = get_post_meta($log->ID, $this->plugin_name.'_log_post_type', true );
        
        $result = [
            'log' => [
                'log_datetime' => $log_datetime,
                'user_id' => $user_id,
                'user_ip' => $user_ip,
                'log_post_type' => $log_post_type,
                'log_action' => $log_action,
                'log_metadata' => json_decode($log_metadata),
            ]
        ];
        array_push($results, $result);
      }
      return $results;
  }
  }
  
  

