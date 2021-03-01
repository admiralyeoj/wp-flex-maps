<?php
/**
 * The custom fields needed for single locations
 *
 * @link       https://www.ignitro.com/
 * @since      1.0.0
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/admin/single_location_fields
 */

/**
 * The custom fields needed for single locations
 *
 * Defines the custom fields needed for the single location data
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/admin/single_location_fields
 * @author     Ignitro <plugin@ignitro.com>
 */
class Flex_Maps_Single_Location_Fields extends Flex_Maps_Fields {

  public function __construct() {

  }

  public function register_fields() {
    $fields = self::get_single_location_fields();
    $location = $this->get_field_location();

    acf_add_local_field_group(array(
      'key' => 'fm_group_single_location',
      'title' => 'Location Fields',
      'fields' => $fields,
      'location' => $location,
      'menu_order' => 0,
      'position' => 'normal',
      'style' => 'seamless',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => '',
      'active' => true,
      'description' => '',
    ));

  }

  protected function get_general_fields() {
    if($fields = wp_cache_get('fm_location_fields_general', 'flex-maps'))
      return $fields;

    $fields = array(
      '1.0' => array(
        'key' => 'fm_tab_general_fields',
        'label' => 'General',
        'name' => '',
        'type' => 'tab',
        'placement' => 'left',
      ),
      '1.1' => array(
        'key' => 'fm_field_location_name',
        'label' => 'Location Name',
        'name' => 'fm_location_name',
        'type' => 'text',
      ),
      '1.2' => array(
        'key' => 'fm_field_phone',
        'label' => 'Phone',
        'name' => 'fm_phone',
        'type' => 'text',
        'wrapper' => array(
          'class' => 'fm-phone-mask',
        ),
      ),
      '1.3' => array(
        'key' => 'fm_field_email',
        'label' => 'Email',
        'name' => 'fm_email',
        'type' => 'email',
      )
    );
    $fields = apply_filters('fm_location_fields_general', $fields);
    wp_cache_set('fm_location_fields_general', $fields, 'flex-maps');

    return $fields;
  }

  protected function get_address_fields() {
    if($fields = wp_cache_get('fm_location_fields_address', 'flex-maps'))
      return $fields;

    $fields = array(
      '2.0' => array(
        'key' => 'fm_tab_group_address',
        'label' => 'Address',
        'name' => '',
        'type' => 'tab',
        'placement' => 'left',
      ),
      '2.1' => array(
        'key' => 'fm_field_street_address_1',
        'label' => 'Street Address 1',
        'name' => 'fm_street_address_1',
        'type' => 'text',
      ),
      '2.2' => array(
        'key' => 'fm_field_street_address_2',
        'label' => 'Street Address 2',
        'name' => 'fm_street_address_2',
        'type' => 'text',
      ),
      '2.3' => array(
        'key' => 'fm_field_city',
        'label' => 'City',
        'name' => 'fm_city',
        'type' => 'text',
        'wrapper' => array(
          'width' => '33.4',
        ),
      ),
      '2.4' => array(
        'key' => 'fm_field_state',
        'label' => 'State / Provience / Region',
        'name' => 'fm_state',
        'type' => 'text',
        'wrapper' => array(
          'width' => '33.3',
        ),
      ),
      '2.5' => array(
        'key' => 'fm_field_postal_code',
        'label' => 'Postal Code',
        'name' => 'fm_postal_code',
        'type' => 'text',
        'wrapper' => array(
          'width' => '33.3',
        ),
      ),
      '2.6' => array(
        'key' => 'fm_field_latitude',
        'label' => 'Latitude',
        'name' => 'fm_latitude',
        'type' => 'number',
        'wrapper' => array(
          'width' => '50',
          'class' => 'fm-remove-arrows',
        ),
      ),
      '2.7' => array(
        'key' => 'fm_field_longitude',
        'label' => 'Longitude',
        'name' => 'fm_longitude',
        'type' => 'number',
        'wrapper' => array(
          'width' => '50',
          'class' => 'fm-remove-arrows',
        ),
      )
    );
    $fields = apply_filters('fm_location_fields_address', $fields, 'flex-maps');
    wp_cache_set('fm_location_fields_address', $fields, 'flex-maps');

    return $fields;
  }

  protected function get_extra_fields() {
    if($fields = wp_cache_get('fm_fields_extra', 'flex-maps'))
      return $fields;

    $fields = apply_filters('fm_fields_extra', array());
    wp_cache_set('fm_fields_extra', $fields, 'flex-maps');

    return $fields;
  }

  protected function get_field_location() {
    if($location = wp_cache_get('fm_single_location_fields_location', 'flex-maps'))
      return $location;

    $location = array(
      array(
        array(
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'fm_locations',
        ),
      ),
    );

    wp_cache_set('fm_single_location_fields_location', $location, 'flex-maps');
    return apply_filters('fm_single_location_fields_location', $location);
  }

  public static function get_single_location_fields() {
    if($fields = wp_cache_get('fm_single_location_fields', 'flex-maps'))
      return $fields;

    $general_fields = self::get_general_fields();
    $address_fields = self::get_address_fields();
    $extra_fields = self::get_extra_fields();

    $location = self::get_field_location();

    $merge_array = array_merge($general_fields, $address_fields, $extra_fields);
    ksort($merge_array);

    wp_cache_set('fm_single_location_fields', $merge_array, 'flex-maps');
    return $merge_array;
  }
}