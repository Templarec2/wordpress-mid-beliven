<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public
 */

class Plugin_Name_Public {

	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
    /*
         Hook che viene triggerato quando un post passa allo stato publish e trash (Create, Delete) OBSOLETA
       */
    //add_action('transition_post_status', array($this, 'log_new_post'), 10, 3);
    
    /*
  Hook che viene triggerato quando un post viene aggiornato (CUD)
*/
    add_action('post_updated', array($this, 'log_update_post'), 10, 3);
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plugin-name-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-public.js', array( 'jquery' ), $this->version, false );

	}
  
  /*
      Funzione salvataggio log post creati ed eliminati (non serve più)
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
    Funzione salvataggio log post aggiornati
  */
  public function log_update_post($post_id, $post, $post_before)
  {
    if ($post->post_type === 'logs') {
      return;
    }
    $post_status = $post->post_status;
    
    if ( $post->post_status !== 'trash' && ($post->post_date !== $post->post_modified)){
      $post_status = 'update';
    }
    
    $my_post = array(
        'post_title' => "Post: $post->ID - $post_status",
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
    update_post_meta($saved_post, $this->plugin_name.'_log_action', $post_status);
    update_post_meta($saved_post, $this->plugin_name.'_log_metadata', $other_data);
    
    
  }
}
