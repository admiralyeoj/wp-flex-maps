<?php
/**
 * Creates the settings page and custom functionality
 *
 * @link       https://www.ignitro.com/
 * @since      1.0.0
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/admin
 */

/**
 * TCreates the settings page and custom functionality
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/admin
 * @author     Ignitro <plugin@ignitro.com>
 */

class Flex_Maps_Global_Setting {

  public function __construct() {

  }

  public function register_settings_page() {
    if( function_exists('acf_add_options_page') ) {
      $option_page = acf_add_options_page(array(
          'page_title'    => __('WP Flex Map Settings', 'flex-maps'),
          'menu_title'    => __('Settings', 'flex-maps'),
          'menu_slug'     => 'flex-maps-settings',
          'parent_slug'   => 'edit.php?post_type=flex_maps',
          'capability'    => 'edit_posts',
          'redirect'      => false,
          'post_id'       => 'flex_maps',
      ));
    }
  }
}