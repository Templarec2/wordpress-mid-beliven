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

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class Plugin_Name_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
    
    add_action('init', array( $this, 'register_custom_post_types' ));
    add_action( 'admin_init', array( $this, 'add_theme_caps' ));
    add_action('add_meta_boxes_logs', array( $this, 'setupCustomPostTypeMetaboxes' ));
    add_action( 'save_post_logs', array( $this, 'saveCustomPostTypeMetaBoxData') );
    add_action(  'save_post',  array($this, 'on_saving_post'), 10, 3 );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );

	}
  
  public function register_custom_post_types(){
    $customPostTypeArgs = array(
        'label'=>'logs',
        'labels'=>
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
                'not_found_in_trash'=>__('Nessun log trovato nel cestino'),
                'menu_name' => _x('logs', 'admin menu'),
                'name_admin_bar' => _x('logs', 'admin bar'),
            ),
        'public'=>true,
        'description'=>'Logs',
        'exclude_from_search'=>false,
        'show_ui'=>true,
        'menu_position'=>26,
        'menu_icon'=>"dashicons-text",
        'capabilities' => array(
            'edit_post' => 'edit_log',
            'edit_posts' => 'edit_logs',
            'edit_others_posts' => 'edit_other_logs',
            'publish_posts' => 'publish_logs',
            'read_post' => 'read_log',
            'read_private_posts' => 'read_private_logs',
            'delete_post' => 'delete_log'
        ),
        'map_meta_cap'    => true,
        'taxonomies'=>array('category','post_tag'));
    

// Post type, $args - the Post Type string can be MAX 20 characters
    register_post_type( 'logs', $customPostTypeArgs );
  }
  public function add_theme_caps() {
    // gets the administrator role
    $admins = get_role( 'administrator' );
    
    $admins->add_cap( 'edit_log' );
    $admins->add_cap( 'edit_logs' );
    $admins->add_cap( 'edit_other_logs' );
    $admins->add_cap( 'publish_logs' );
    $admins->add_cap( 'read_log' );
    $admins->add_cap( 'read_private_logs' );
    $admins->add_cap( 'delete_log' );
    
    $editors = get_role( 'editor' );
  
    $editors->add_cap( 'edit_log' );
    $editors->add_cap( 'edit_logs' );
    $editors->add_cap( 'edit_other_logs' );
    $editors->add_cap( 'publish_logs' );
    $editors->add_cap( 'read_log' );
    $editors->add_cap( 'read_private_logs' );
    $editors->add_cap( 'delete_log' );
  }
  public function setupCustomPostTypeMetaboxes(){
    add_meta_box('custom_post_type_data_meta_box', 'Dati Log', array($this,'custom_post_type_data_meta_box'), 'logs', 'normal','high' );
  }
  
  public function custom_post_type_data_meta_box($post){
    // Add a nonce field so we can check for it later.
    wp_nonce_field( $this->plugin_name.'_affiliate_meta_box', $this->plugin_name.'_affiliates_meta_box_nonce' );
    
    echo '<div class="post_type_field_containers">';
    echo '<ul class="plugin_name_product_data_metabox">';
    
    echo '<li><label for="'.$this->plugin_name.'_log_datetime">';
    _e( 'Data/ora log', $this->plugin_name.'_log_datetime' );
    echo '</label>';
    $args = array (
        'type'      => 'input',
        'subtype'	  => 'datetime-local',
        'id'	  => $this->plugin_name.'_log_datetime',
        'name'	  => $this->plugin_name.'_log_datetime',
        'required' => '',
        'get_options_list' => '',
        'value_type'=>'normal',
        'wp_data' => 'post_meta',
        'post_id'=> $post->ID
    );
    // this gets the post_meta value and echos back the input
    $this->plugin_name_render_settings_field($args);
    echo '</li><li><label for="'.$this->plugin_name.'_user_id">';
    _e( 'Id utente', $this->plugin_name.'_user_id' );
    echo '</label>';
    $args = array (
        'type'      => 'input',
        'subtype'	  => 'text',
        'id'	  => $this->plugin_name.'_user_id',
        'name'	  => $this->plugin_name.'_user_id',
        'required' => '',
        'get_options_list' => '',
        'value_type'=>'normal',
        'wp_data' => 'post_meta',
        'post_id'=> $post->ID
    );
    // this gets the post_meta value and echos back the input
    $this->plugin_name_render_settings_field($args);
    echo '</li><li><label for="'.$this->plugin_name.'_user_ip">';
    _e( 'IP utente', $this->plugin_name.'_user_ip' );
    echo '</label>';
    unset($args);
    $args = array (
        'type'      => 'input',
        'subtype'	  => 'text',
        'id'	  => $this->plugin_name.'_user_ip',
        'name'	  => $this->plugin_name.'_user_ip',
        'required' => '',
        'get_options_list' => '',
        'value_type'=>'normal',
        'wp_data' => 'post_meta',
        'post_id'=> $post->ID
    );
    // this gets the post_meta value and echos back the input
    $this->plugin_name_render_settings_field($args);
    echo '</li><li><label for="'.$this->plugin_name.'_log_post_type">';
    _e( 'Tipo post type', $this->plugin_name.'_log_post_type' );
    echo '</label>';
    unset($args);
    $args = array (
        'type'      => 'input',
        'subtype'	  => 'text',
        'id'	  => $this->plugin_name.'_log_post_type',
        'name'	  => $this->plugin_name.'_log_post_type',
        'required' => '',
        'get_options_list' => '',
        'value_type'=>'normal',
        'wp_data' => 'post_meta',
        'post_id'=> $post->ID
    );
    // this gets the post_meta value and echos back the input
    $this->plugin_name_render_settings_field($args);
    echo '</li><li><label for="'.$this->plugin_name.'_log_action">';
    _e( 'Azione', $this->plugin_name.'_log_action' );
    echo '</label>';
    unset($args);
    $args = array (
        'type'      => 'input',
        'subtype'	  => 'text',
        'id'	  => $this->plugin_name.'_log_action',
        'name'	  => $this->plugin_name.'_log_action',
        'required' => '',
        'get_options_list' => '',
        'value_type'=>'normal',
        'wp_data' => 'post_meta',
        'post_id'=> $post->ID
    );
    // this gets the post_meta value and echos back the input
    $this->plugin_name_render_settings_field($args);
    echo '</li><li><label for="'.$this->plugin_name.'_log_metadata">';
    _e( 'Dati aggiuntivi', $this->plugin_name.'_log_metadata' );
    echo '</label>';
    unset($args);
    $args = array (
        'type'      => 'input',
        'subtype'	  => 'text',
        'id'	  => $this->plugin_name.'_log_metadata',
        'name'	  => $this->plugin_name.'_log_metadata',
        'required' => '',
        'get_options_list' => '',
        'value_type'=>'normal',
        'wp_data' => 'post_meta',
        'post_id'=> $post->ID
    );
    $this->plugin_name_render_settings_field($args);
    echo '</li></ul></div>';
  }
  public function plugin_name_render_settings_field($args) {
    if($args['wp_data'] == 'option'){
      $wp_data_value = get_option($args['name']);
    } elseif($args['wp_data'] == 'post_meta'){
      $wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
    }
    
    switch ($args['type']) {
      case 'input':
        $value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
        if($args['subtype'] != 'checkbox'){
          $prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">'.$args['prepend_value'].'</span>' : '';
          $prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
          $step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
          $min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
          $max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
          if(isset($args['disabled'])){
            // hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
            echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
          } else {
            echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
          }
          /*<input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" />*/
          
        } else {
          $checked = ($value) ? 'checked' : '';
          echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'" size="40" value="1" '.$checked.' />';
        }
        break;
      default:
        # code...
        break;
    }
  }
  public function saveCustomPostTypeMetaBoxData( $post_id ) {
    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */
//    echo "<pre>";
//    print_r($_POST);
//    echo "</pre>";
 
    
    // Check if our nonce is set.
//    if ( ! isset( $_POST['plugin-name_affiliates_meta_box_nonce'] ) ) {
//      echo "case1";
//      return;
//    }
//
//    // Verify that the nonce is valid.
//    if ( ! wp_verify_nonce( $_POST['plugin-name_affiliates_meta_box_nonce']) ) {
//      echo "case2";
//      return;
//    }
    
    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      
      return;
    }
    
    // Check the user's permissions.
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
     
      return;
    }
    
    // Make sure that it is set.
    if ( !isset( $_POST[$this->plugin_name.'_log_datetime'] ) && !isset( $_POST[$this->plugin_name.'_user_id'] ) && !isset( $_POST[$this->plugin_name.'_user_ip'] ) && !isset( $_POST[$this->plugin_name.'_log_post_type'] ) && !isset( $_POST[$this->plugin_name.'_log_action'] ) && !isset( $_POST[$this->plugin_name.'_log_metadata'] )) {
     
      return;
    }
    
    /* OK, it's safe for us to save the data now. */
    // Sanitize user input.
    $log_datetime = sanitize_text_field( $_POST[$this->plugin_name."_log_datetime"]);
    $user_id = sanitize_text_field( $_POST[$this->plugin_name."_user_id"]);
    $user_ip = sanitize_text_field( $_POST[$this->plugin_name."_user_ip"]);
    $log_post_type = sanitize_text_field( $_POST[$this->plugin_name."_log_post_type"]);
    $log_action = sanitize_text_field( $_POST[$this->plugin_name."_log_action"]);
    $log_metadata = sanitize_text_field( $_POST[$this->plugin_name."_log_metadata"]);




    update_post_meta($post_id, $this->plugin_name.'_log_datetime',$log_datetime);
    update_post_meta($post_id, $this->plugin_name.'_user_id',$user_id);
    update_post_meta($post_id, $this->plugin_name.'_user_ip',$user_ip);
    update_post_meta($post_id, $this->plugin_name.'_log_post_type',$log_post_type);
    update_post_meta($post_id, $this->plugin_name.'_log_action',$log_action);
    update_post_meta($post_id, $this->plugin_name.'_log_metadata',$log_metadata);
  
  }
  public function on_saving_post( $post_id, $post ) {
   
      $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
      $txt = " - $post->ID|| $post->post_author || $post->post_date || $post->post_status || $post->post_type ";
      fwrite($myfile, $txt);
      fclose($myfile);
      $other_data = [
          'id' => $post->ID,
          
      ];
      $other_data = json_encode($other_data );
      do_action('save_post_logs');
    update_post_meta($post_id, $this->plugin_name.'_log_datetime',$post->post_date);
    update_post_meta($post_id, $this->plugin_name.'_user_id',$post->post_author);
    update_post_meta($post_id, $this->plugin_name.'_user_ip','127.0.0.1');
    update_post_meta($post_id, $this->plugin_name.'_log_post_type',$post->post_type);
    update_post_meta($post_id, $this->plugin_name.'_log_action',$post->post_status);
    update_post_meta($post_id, $this->plugin_name.'_log_metadata',$other_data);
  }
  
  

}


