<?php
/**
 * Defines the custom post types needed
 *
 * @link       https://www.ignitro.com/
 * @since      1.0.0
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/admin/flex_maps_post_types
 */

/**
 * The custom fields needed for data
 *
 * Defines the custom post types needed for the Flex Maps
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/admin/flex_maps_post_types
 * @author     Ignitro <plugin@ignitro.com>
 */
class Flex_Maps_Post_Types {

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  // private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  // private $version;

  public function __construct() {

  }

  public function register_location_post_type() {
    $labels = array(
      'name'                  => _x( 'Locations', 'Post Type General Name', 'flex_maps' ),
      'singular_name'         => _x( 'Location', 'Post Type Singular Name', 'flex_maps' ),
      'menu_name'             => __( 'Locations', 'flex_maps' ),
      'name_admin_bar'        => __( 'Locations', 'flex_maps' ),
      'archives'              => __( 'Location Archives', 'flex_maps' ),
      'attributes'            => __( 'Location Attributes', 'flex_maps' ),
      'parent_item_colon'     => __( 'Parent Location:', 'flex_maps' ),
      'all_items'             => __( 'All Locations', 'flex_maps' ),
      'add_new_item'          => __( 'Add New Location', 'flex_maps' ),
      'add_new'               => __( 'Add New', 'flex_maps' ),
      'new_item'              => __( 'New Location', 'flex_maps' ),
      'edit_item'             => __( 'Edit Location', 'flex_maps' ),
      'update_item'           => __( 'Update Location', 'flex_maps' ),
      'view_item'             => __( 'View Location', 'flex_maps' ),
      'view_items'            => __( 'View Location', 'flex_maps' ),
      'search_items'          => __( 'Search Location', 'flex_maps' ),
      'not_found'             => __( 'Not found', 'flex_maps' ),
      'not_found_in_trash'    => __( 'Not found in Trash', 'flex_maps' ),
      'featured_image'        => __( 'Featured Image', 'flex_maps' ),
      'set_featured_image'    => __( 'Set featured image', 'flex_maps' ),
      'remove_featured_image' => __( 'Remove featured image', 'flex_maps' ),
      'use_featured_image'    => __( 'Use as featured image', 'flex_maps' ),
      'insert_into_item'      => __( 'Insert into Location', 'flex_maps' ),
      'uploaded_to_this_item' => __( 'Uploaded to this Location', 'flex_maps' ),
      'items_list'            => __( 'Locations list', 'flex_maps' ),
      'items_list_navigation' => __( 'Locations list navigation', 'flex_maps' ),
      'filter_items_list'     => __( 'Filter Locations list', 'flex_maps' ),
    );

    $rewrite = array(
      'slug'                  => 'locations',
      'with_front'            => true,
      'pages'                 => true,
      'feeds'                 => true,
    );

    $args = array(
      'label'                 => __( 'Location', 'flex_maps' ),
      'labels'                => $labels,
      'supports'              => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
      'hierarchical'          => true,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => 'edit.php?post_type=flex_maps',
      'menu_position'         => 25,
      'menu_icon'             => 'dashicons-location',
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => true,
      'can_export'            => true,
      'has_archive'           => true,
      'exclude_from_search'   => false,
      'publicly_queryable'    => true,
      'rewrite'               => $rewrite,
      'capability_type'       => 'page',
      'show_in_rest'          => true,
    );

    $labels = apply_filters('fm_post_type_labels', $labels);
    $rewrite = apply_filters('fm_post_type_rewrite', $rewrite);
    $args = apply_filters('fm_post_type_args', $args);
    
    register_post_type( 'fm_locations', $args );
  }

  // Register Custom Post Type
  public function register_map_settings_post_type() {

    $labels = array(
      'name'                  => _x( 'Flex Maps', 'Post Type General Name', 'flex-maps' ),
      'singular_name'         => _x( 'Flex Map', 'Post Type Singular Name', 'flex-maps' ),
      'menu_name'             => __( 'WP Flex Maps', 'flex-maps' ),
      'name_admin_bar'        => __( 'WP Flex Map', 'flex-maps' ),
      'archives'              => __( 'Map Archives', 'flex-maps' ),
      'attributes'            => __( 'Map Attributes', 'flex-maps' ),
      'parent_item_colon'     => __( 'Parent Map:', 'flex-maps' ),
      'all_items'             => __( 'Maps', 'flex-maps' ),
      'add_new_item'          => __( 'Add New Map', 'flex-maps' ),
      'add_new'               => __( 'Add Map', 'flex-maps' ),
      'new_item'              => __( 'New Map', 'flex-maps' ),
      'edit_item'             => __( 'Edit Map', 'flex-maps' ),
      'update_item'           => __( 'Update Map', 'flex-maps' ),
      'view_item'             => __( 'View Map', 'flex-maps' ),
      'view_items'            => __( 'View Maps', 'flex-maps' ),
      'search_items'          => __( 'Search Maps', 'flex-maps'),
    );
    $args = array(
      'label'                 => __( 'Flex Map', 'flex-maps' ),
      'description'           => __( 'Post Type Description', 'flex-maps' ),
      'labels'                => $labels,
      'supports'              => array('title'),
      'hierarchical'          => false,
      'public'                => false,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 5,
      'menu_icon'             => 'dashicons-location-alt',
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => false,
      'can_export'            => true,
      'has_archive'           => false,
      'exclude_from_search'   => true,
      'publicly_queryable'    => true,
      'rewrite'               => false,
      'capability_type'       => 'page',
      'show_in_rest'          => true,
    );
    register_post_type( 'flex_maps', $args );

  }
}