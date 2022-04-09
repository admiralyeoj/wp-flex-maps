<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.ignitro.com/
 * @since      1.0.0
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/admin
 * @author     Ignitro <plugin@ignitro.com>
 */
class Flex_Maps_Admin {

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


  protected $api;

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
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {
    if( $hook != 'post.php' || empty($_GET['action']) || $_GET['action'] != 'edit' ) 
      return;

    $post_type = get_post_type($_GET['post']);
    if($post_type != 'flex_maps' && $post_type != 'fm_locations')
      return;

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bundle.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
    if( $hook != 'post.php' || empty($_GET['action']) || $_GET['action'] != 'edit' ) 
      return;

    $post_type = get_post_type($_GET['post']);
    if($post_type != 'flex_maps' && $post_type != 'fm_locations')
      return;

    // wp_enqueue_script( $this->plugin_name.'-google-map', plugin_dir_url( __FILE__ ) . '../public/js/FM-Google-Map.js', array( 'jquery', ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bundle.min.js', array( 'jquery' ), $this->version, false );

    wp_enqueue_script( $this->plugin_name.'-map-api', 'https://maps.googleapis.com/maps/api/js?key='.FLEX_MAPS_BROWSER_API_KEY.'&callback=FM_Init_Map', array( 'jquery', $this->plugin_name ), $this->version, true );
    
    $javascript_data = array(
      'file_path' => FLEX_MAPS_PLUGIN_PATH.'public/',
      'ajax_url' => admin_url( 'admin-ajax.php' ),
    );
    wp_add_inline_script($this->plugin_name, "var flex_map = ".json_encode($javascript_data), 'before');
	}

  
  /**
   * Register the post types for the admin area.
   *
   * @since    1.0.0
   */
  public function register_post_types() {
    $maybe_register_locations = apply_filters('fm_register_locations', true);
    $post_types = new Flex_Maps_Post_Types();

    $post_types->register_map_settings_post_type();

    if($maybe_register_locations) {
      $post_types->register_location_post_type();
      $post_types->register_stylist_post_type();
    }
  }

  /**
   * Register the custom fields for the admin area.
   *
   * @since    1.0.0
   */
  public function register_custom_fields() {
    $single_location_fields = new Flex_Maps_Single_Location_Fields();
    $global_settings_fields = new Flex_Maps_Global_Setting_Fields();
    $settings_fields = new Flex_Maps_Setting_Fields();

    $single_location_fields->register_fields();
    $global_settings_fields->register_fields();
    $settings_fields->register_fields();
  }


  /*public function flex_maps_customize_menu() {
    global $submenu;

    add_submenu_page('edit.php?post_type=flex_maps', 'Add New Location', 'Add new Location', 'manage_options', 'post-new.php?post_type=fm_locations'); 


    $taxonomies = get_object_taxonomies( 'fm_locations' );
    if(!empty($taxonomies)) {
      foreach ($taxonomies as $tax) {
        add_submenu_page('edit.php?post_type=flex_maps', 'Taxonomy', 'Taxonomy', 'manage_options', "edit-tags.php?taxonomy={$tax}&post_type=flex_maps");
      } 
    }
  }

  function taxonomy_parent_page( $parent_file ) {
    $screen = get_current_screen();

    // print_r($parent_file); exit;

    $taxonomy = $screen->taxonomy;
    if ( $screen->post_type == 'fm_locations' && ( $taxonomy == 'taxonomy' || $taxonomy == 'taxonomy2' || $taxonomy == 'taxonomy3' ) ) {
      $parent_file = 'edit.php?post_type=flex_maps';
    }

    return $parent_file;
  }*/

}
