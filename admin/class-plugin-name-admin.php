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
      
      add_action('admin_menu', array($this, 'logs_option_page'));
      /*
       Aggiunta colonne nella visualizzazione pagina admin del custom post type
     */
      add_filter('manage_posts_columns', array($this, 'custom_logs_table_head'));
      /*
       Assegnazione fields dei meta alle colonne sopra create
     */
      add_action('manage_posts_custom_column', array($this, 'custom_logs_table_content'), 10, 2);
      add_filter('cron_schedules', array($this, 'my_minutly'));
      add_action('bl_cron_log_retention', array($this, 'logs_retention'));
      
      add_action( 'rest_api_init', array($this, 'logs_endpoints'));
      
      /*
        Creazione metabox per debug iniziale dei meta
      */
      //  add_action('add_meta_boxes_logs', array($this, 'setupCustomPostTypeMetaboxes'));
      //  add_action('save_post_logs', array($this, 'saveCustomPostTypeMetaBoxData'));
      
      /*
       Hook che viene triggerato quando un post passa allo stato publish e trash (Create, Delete) OBSOLETA
     */
      //add_action('transition_post_status', array($this, 'log_new_post'), 10, 3);
      
      /*
    Hook che viene triggerato quando un post viene aggiornato (Update) - SPOSTATA IN PUBLIC
  */
      //add_action('post_updated', array($this, 'log_update_post'), 10, 3);
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


// Post type, $args - the Post Type string can be MAX 20 characters
      register_post_type('logs', $customPostTypeArgs);
    }
    
    /*
       Funzione che assegna capabilities
     */
    public function add_theme_caps()
    {
      // gets the administrator role
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
      Funzioni debug iniziali delle metabox
    */
    public function setupCustomPostTypeMetaboxes()
    {
      add_meta_box(
          'custom_post_type_data_meta_box',
          'Dati Log',
          array($this, 'custom_post_type_data_meta_box'),
          'logs',
          'normal',
          'high'
      );
    }
    
    public function custom_post_type_data_meta_box($post)
    {
//    // Add a nonce field so we can check for it later.
////    wp_nonce_field( $this->plugin_name.'_affiliate_meta_box', $this->plugin_name.'_affiliates_meta_box_nonce' );
//
//    echo '<div class="post_type_field_containers">';
//    echo '<ul class="plugin_name_product_data_metabox">';
//
//    echo '<li><label for="'.$this->plugin_name.'_log_datetime">';
//    _e( 'Data/ora log', $this->plugin_name.'_log_datetime' );
//    echo '</label>';
//    $args = array (
//        'type'      => 'input',
//        'subtype'	  => 'datetime-local',
//        'id'	  => $this->plugin_name.'_log_datetime',
//        'name'	  => $this->plugin_name.'_log_datetime',
//        'required' => '',
//        'get_options_list' => '',
//        'value_type'=>'normal',
//        'wp_data' => 'post_meta',
//        'post_id'=> $post->ID
//    );
//    // this gets the post_meta value and echos back the input
//    $this->plugin_name_render_settings_field($args);
//    echo '</li><li><label for="'.$this->plugin_name.'_user_id">';
//    _e( 'Id utente', $this->plugin_name.'_user_id' );
//    echo '</label>';
//    $args = array (
//        'type'      => 'input',
//        'subtype'	  => 'text',
//        'id'	  => $this->plugin_name.'_user_id',
//        'name'	  => $this->plugin_name.'_user_id',
//        'required' => '',
//        'get_options_list' => '',
//        'value_type'=>'normal',
//        'wp_data' => 'post_meta',
//        'post_id'=> $post->ID
//    );
//    // this gets the post_meta value and echos back the input
//    $this->plugin_name_render_settings_field($args);
//    echo '</li><li><label for="'.$this->plugin_name.'_user_ip">';
//    _e( 'IP utente', $this->plugin_name.'_user_ip' );
//    echo '</label>';
//    unset($args);
//    $args = array (
//        'type'      => 'input',
//        'subtype'	  => 'text',
//        'id'	  => $this->plugin_name.'_user_ip',
//        'name'	  => $this->plugin_name.'_user_ip',
//        'required' => '',
//        'get_options_list' => '',
//        'value_type'=>'normal',
//        'wp_data' => 'post_meta',
//        'post_id'=> $post->ID
//    );
//    // this gets the post_meta value and echos back the input
//    $this->plugin_name_render_settings_field($args);
//    echo '</li><li><label for="'.$this->plugin_name.'_log_post_type">';
//    _e( 'Tipo post type', $this->plugin_name.'_log_post_type' );
//    echo '</label>';
//    unset($args);
//    $args = array (
//        'type'      => 'input',
//        'subtype'	  => 'text',
//        'id'	  => $this->plugin_name.'_log_post_type',
//        'name'	  => $this->plugin_name.'_log_post_type',
//        'required' => '',
//        'get_options_list' => '',
//        'value_type'=>'normal',
//        'wp_data' => 'post_meta',
//        'post_id'=> $post->ID
//    );
//    // this gets the post_meta value and echos back the input
//    $this->plugin_name_render_settings_field($args);
//    echo '</li><li><label for="'.$this->plugin_name.'_log_action">';
//    _e( 'Azione', $this->plugin_name.'_log_action' );
//    echo '</label>';
//    unset($args);
//    $args = array (
//        'type'      => 'input',
//        'subtype'	  => 'text',
//        'id'	  => $this->plugin_name.'_log_action',
//        'name'	  => $this->plugin_name.'_log_action',
//        'required' => '',
//        'get_options_list' => '',
//        'value_type'=>'normal',
//        'wp_data' => 'post_meta',
//        'post_id'=> $post->ID
//    );
//    // this gets the post_meta value and echos back the input
//    $this->plugin_name_render_settings_field($args);
//    echo '</li><li><label for="'.$this->plugin_name.'_log_metadata">';
//    _e( 'Dati aggiuntivi', $this->plugin_name.'_log_metadata' );
//    echo '</label>';
//    unset($args);
//    $args = array (
//        'type'      => 'input',
//        'subtype'	  => 'text',
//        'id'	  => $this->plugin_name.'_log_metadata',
//        'name'	  => $this->plugin_name.'_log_metadata',
//        'required' => '',
//        'get_options_list' => '',
//        'value_type'=>'normal',
//        'wp_data' => 'post_meta',
//        'post_id'=> $post->ID
//    );
//    $this->plugin_name_render_settings_field($args);
//    echo '</li></ul></div>';
    }
    
    public function plugin_name_render_settings_field($args)
    {
//    if($args['wp_data'] == 'option'){
//      $wp_data_value = get_option($args['name']);
//    } elseif($args['wp_data'] == 'post_meta'){
//      $wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
//    }
//
//    switch ($args['type']) {
//      case 'input':
//        $value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
//        if($args['subtype'] != 'checkbox'){
//          $prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">'.$args['prepend_value'].'</span>' : '';
//          $prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
//          $step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
//          $min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
//          $max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
//          if(isset($args['disabled'])){
//            // hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
//            echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
//          } else {
//            echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
//          }
//          /*<input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" />*/
//
//        } else {
//          $checked = ($value) ? 'checked' : '';
//          echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'" size="40" value="1" '.$checked.' />';
//        }
//        break;
//      default:
//        # code...
//        break;
//    }
//  }
//  public function saveCustomPostTypeMetaBoxData( $post_id ) {
//    /*
//     * We need to verify this came from our screen and with proper authorization,
//     * because the save_post action can be triggered at other times.
//     */
////    echo "<pre>";
////    print_r($_POST);
////    echo "</pre>";
//
//
//    // Check if our nonce is set.
////    if ( ! isset( $_POST['plugin-name_affiliates_meta_box_nonce'] ) ) {
////      echo "case1";
////      return;
////    }
////
////    // Verify that the nonce is valid.
////    if ( ! wp_verify_nonce( $_POST['plugin-name_affiliates_meta_box_nonce']) ) {
////      echo "case2";
////      return;
////    }
//
//    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
//    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
//
//      return;
//    }
//
//    // Check the user's permissions.
//    if ( ! current_user_can( 'edit_post', $post_id ) ) {
//
//      return;
//    }
//
//    // Make sure that it is set.
//    if ( !isset( $_POST[$this->plugin_name.'_log_datetime'] ) && !isset( $_POST[$this->plugin_name.'_user_id'] ) && !isset( $_POST[$this->plugin_name.'_user_ip'] ) && !isset( $_POST[$this->plugin_name.'_log_post_type'] ) && !isset( $_POST[$this->plugin_name.'_log_action'] ) && !isset( $_POST[$this->plugin_name.'_log_metadata'] )) {
//
//      return;
//    }
//
//    /* OK, it's safe for us to save the data now. */
//    // Sanitize user input.
//    $log_datetime = sanitize_text_field( $_POST[$this->plugin_name."_log_datetime"]);
//    $user_id = sanitize_text_field( $_POST[$this->plugin_name."_user_id"]);
//    $user_ip = sanitize_text_field( $_POST[$this->plugin_name."_user_ip"]);
//    $log_post_type = sanitize_text_field( $_POST[$this->plugin_name."_log_post_type"]);
//    $log_action = sanitize_text_field( $_POST[$this->plugin_name."_log_action"]);
//    $log_metadata = sanitize_text_field( $_POST[$this->plugin_name."_log_metadata"]);
//
//
//
//
//    update_post_meta($post_id, $this->plugin_name.'_log_datetime',$log_datetime);
//    update_post_meta($post_id, $this->plugin_name.'_user_id',$user_id);
//    update_post_meta($post_id, $this->plugin_name.'_user_ip',$user_ip);
//    update_post_meta($post_id, $this->plugin_name.'_log_post_type',$log_post_type);
//    update_post_meta($post_id, $this->plugin_name.'_log_action',$log_action);
//    update_post_meta($post_id, $this->plugin_name.'_log_metadata',$log_metadata);
//
    }
    
    /*
      Funzione salvataggio log post creati ed eliminati OBSOLETA
    */
    public function log_new_post($new, $old, $post)
    {
      if ((($new == 'publish') && ($old != 'publish')) && ($post->post_type !== 'logs')) {
        $my_post = array(
            'post_title' => "Post: $post->ID - $new",
            'post_content' => "",
            'post_status' => 'publish',
            'post_type' => 'logs',
            'post_author' => 1,
        
        );
        
        $saved_post = wp_insert_post($my_post);
        $other_data = [
            'id' => $post->ID,
            'title' => $post->post_title,
        
        ];
        $other_data = json_encode($other_data);
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
          $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
          $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
          $ip = $_SERVER['REMOTE_ADDR'];
        }
        update_post_meta($saved_post, $this->plugin_name.'_log_datetime', $post->post_date);
        update_post_meta($saved_post, $this->plugin_name.'_user_id', $post->post_author);
        update_post_meta($saved_post, $this->plugin_name.'_user_ip', $ip);
        update_post_meta($saved_post, $this->plugin_name.'_log_post_type', $post->post_type);
        update_post_meta($saved_post, $this->plugin_name.'_log_action', $post->post_status);
        update_post_meta($saved_post, $this->plugin_name.'_log_metadata', $other_data);
      }
      if ((($new == 'trash') && ($old != 'trash')) && ($post->post_type !== 'logs')) {
        $my_post = array(
            'post_title' => "Post: $post->ID - $new",
            'post_content' => "",
            'post_status' => 'publish',
            'post_type' => 'logs',
            'post_author' => 1,
        
        );
        $saved_post = wp_insert_post($my_post);
        $other_data = [
            'id' => $post->ID,
            'title' => $post->post_title,
        
        ];
        $other_data = json_encode($other_data);
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
          $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
          $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
          $ip = $_SERVER['REMOTE_ADDR'];
        }
        update_post_meta($saved_post, $this->plugin_name.'_log_datetime', $post->post_date);
        update_post_meta($saved_post, $this->plugin_name.'_user_id', $post->post_author);
        update_post_meta($saved_post, $this->plugin_name.'_user_ip', $ip);
        update_post_meta($saved_post, $this->plugin_name.'_log_post_type', $post->post_type);
        update_post_meta($saved_post, $this->plugin_name.'_log_action', $post->post_status);
        update_post_meta($saved_post, $this->plugin_name.'_log_metadata', $other_data);
      }
    }
    
    /*
      Funzione salvataggio log post aggiornati SPOSTATA IN PUBLIC
    */
    public function log_update_post($post_id, $post, $post_before)
    {
      if ($post->post_type === 'logs') {
        return;
      }
      
      $my_post = array(
          'post_title' => "Post: $post->ID - Update",
          'post_content' => "",
          'post_status' => 'publish',
          'post_type' => 'logs',
          'post_author' => 1,
      
      );
      
      $saved_post = wp_insert_post($my_post);
      $other_data = [
          'id' => $post->ID,
          'title' => $post->post_title,
      
      ];
      $other_data = json_encode($other_data);
      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
      } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } else {
        $ip = $_SERVER['REMOTE_ADDR'];
      }
      update_post_meta($saved_post, $this->plugin_name.'_log_datetime', $post->post_date);
      update_post_meta($saved_post, $this->plugin_name.'_user_id', $post->post_author);
      update_post_meta($saved_post, $this->plugin_name.'_user_ip', $ip);
      update_post_meta($saved_post, $this->plugin_name.'_log_post_type', $post->post_type);
      update_post_meta($saved_post, $this->plugin_name.'_log_action', 'update');
      update_post_meta($saved_post, $this->plugin_name.'_log_metadata', $other_data);
      
      
    }
    
    public function logs_option_page()
    {
      
      add_menu_page(
          'Beliven logger options', // page <title>Title</title>
          'Beliven logger options', // link text
          'manage_options', // user capabilities
          'beliven_logger', // page slug
          array($this, 'beliven_option_page'), // this function prints the page content
          'dashicons-text', // icon (from Dashicons for example)
          27 // menu position
      );
    }
    
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
    
    function my_minutly($schedules)
    {
      // add a 'weekly' schedule to the existing set
      $schedules['minutly'] = array(
          'interval' => 60,
          'display' => __('Every minute'),
      );
      
      return $schedules;
    }
    
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
         return $token;
       } else {
         return [
             'status' => false,
             'message' => 'Invalid credentials'
         ];
       }
    }
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
  
  

