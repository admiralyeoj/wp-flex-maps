<?php
/**
 * The custom fields needed for single locations
 *
 * @link       https://www.ignitro.com/
 * @since      1.0.0
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/admin/settings_fields
 */

/**
 * The custom fields needed for single locations
 *
 * Defines the custom fields needed for the single location data
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/admin/settings_fields
 * @author     Ignitro <plugin@ignitro.com>
 */
class Flex_Maps_Global_Setting_Fields extends Flex_Maps_Fields {

  public function __construct() {

  }

  public function register_fields() {
    $general_fields = $this->get_settings_fields();
    $extra_fields = apply_filters('fm_settings_fields_extra', array());

    $merge_array = array_merge($general_fields, $extra_fields);
    ksort($merge_array);

    $group = array(
      'key' => 'fm_group_global_settings',
      'title' => 'WP Flex Map Settings',
      'fields' => $merge_array,
      'location' => $this->get_field_location(),
      'menu_order' => 0,
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => '',
      'active' => true,
      'description' => '',
    );

    acf_add_local_field_group($group);

  }

  public static function get_settings_fields() {
    if($fields = wp_cache_get('fm_setting_fields_general'))
      return $fields;

    $fields = array(
      '1.0' => array(
        'key' => 'fm_field_option_browser_key',
        'label' => 'Google Maps Browser Key',
        'name' => 'fm_google_maps_browser_key',
        'type' => 'text',
        'instructions' => 'Restrict key using site URL',
      ),
      '1.1' => array(
        'key' => 'fm_field_option_server_key',
        'label' => 'Google Maps Server Key',
        'name' => 'fm_google_maps_server_key',
        'type' => 'text',
        'instructions' => 'Restrict this key using the servers IP',
      ),
      '1.2' => array(
        'key' => 'fm_field_option_radius',
        'label' => 'Radius',
        'name' => 'radius',
        'type' => 'text',
        'placeholder' => '25',
      ),
    );

    $fields = apply_filters('fm_global_settings_fields_general', $fields);
    wp_cache_add('fm_setting_fields_general', $fields);

    return $fields;
  }

  public static function get_field_location() {
    if($location = wp_cache_get('fm_global_settings_fields_location'))
      return $location;

    $location = array(
      array(
        array(
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'flex-maps-settings',
        ),
      ),
    );

    $location = apply_filters('fm_global_settings_fields_location', $location);
    wp_cache_set('fm_global_settings_fields_location', $location);

    return $location;
  }
}