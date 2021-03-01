<?php
global $fm_rule_group_index, $fm_rule_row_index;
$fm_rule_group_index = $fm_rule_row_index = null;

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
class Flex_Maps_Settings {

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

  public function __construct( $plugin_name, $version ) {
    $this->plugin_name = $plugin_name;
    $this->version = $version;
  }

  public function example_map_section() {
    add_meta_box( 'fm-preview', __('Flex Map Preview', $this->plugin_name), array($this, 'map_preview'), 'flex_maps', 'advanced' );
  }

  public function map_preview() {

    $mapId = intval($_GET['post']);
    $attributes = array();
    $load_type = get_field('fm_load_type', $mapId);

    if($load_type == 'latlng')
      $attributes = get_field('fm_load_type_latlng', $mapId);
    
    ?>
    <div class="fm-map-container">
      <div id="<?= $unique_id ?>" style="height: 400px;" data-map="<?= $mapId ?>" class="fm-google-map flex-map-<?= $mapId ?>" data-load-type="<?= $load_type ?>" <?php foreach ($attributes as $key => $attr) { echo " data-{$key}='".html_entity_decode($attr)."' "; } ?>>
        <span class="fm-spinner"></span>
      </div>
    </div>
    <?php

  }

  function load_fm_meta_keys( $field ) {
    if($choices = wp_cache_get("fm_meta_keys_{$field['key']}", $this->plugin_name)){
      $field['choices'] = $choices;
      return $field;
    }

    $field['choices'] = array();
    
    $all_fields = Flex_Maps_Single_Location_Fields::get_single_location_fields();
    $label = '';

    foreach($all_fields as $row) {
      if(empty($row['name'])) {
        $label = $row['label'];
        continue;
      }

      $field['choices'][$label][$row['name']] = $row['label'];
    }

    wp_cache_set("fm_meta_keys_{$field['key']}", $field['choices'], $this->plugin_name);
    return $field; 
  }

  function load_search_values( $field ) {
    if($choices = wp_cache_get("fm_search_values_{$field['key']}", $this->plugin_name)){
      $field['choices'] = $choices;
      return $field;
    }

    // reset choices
    $field['choices'] = array();
    
    $all_fields = Flex_Maps_Single_Location_Fields::get_single_location_fields();
    $label = '';

    if(!empty($all_fields)) {
      foreach($all_fields as $row) {
        if(empty($row['name'])) {
          $label = $row['label'];
          continue;
        }

        $field['choices'][$label][$row['name']] = $row['label'];
      }
    }

    $taxonomies = get_object_taxonomies('fm_locations', 'object');
    if(!empty($taxonomies)) {
      foreach ($taxonomies as $tax) {
        $field['choices']['Taxonomies'][$tax->name] = "{$tax->labels->singular_name} ({$tax->name})";
      }
    }

    wp_cache_set("fm_search_values_{$field['key']}", $field['choices'], $this->plugin_name);
    return $field;
  }

  public function load_rule_group_repeater($field) {
    global $fm_rule_group_index, $fm_rule_row_index;

    if(!isset($fm_rule_group_index)) {
      $fm_rule_group_index = -1;
      return $field;
    }

    $fm_rule_row_index = null;
    $fm_rule_group_index++;

    return $field;
  }

  public function load_fm_meta_values($field) {
    global $post, $fm_rule_group_index, $fm_rule_row_index;
    if(!isset($fm_rule_row_index)) {
      $fm_rule_row_index = 0;

      return $field;
  }
    
    //group_index = $fm_rule_group_index; //wp_cache_get('fm_rule_group_index', $this->plugin_name);
    $filter = get_post_meta($post->ID, "fm_load_type_fields_rule_container_{$fm_rule_group_index}_rule_group_{$fm_rule_row_index}_key", true);
    $field_group = Flex_Maps_Single_Location_Fields::get_single_location_fields();

    // if filter is empty. Gets the first field with the name value entered
    if(empty($filter)) {
      foreach ($field_group as $row) {
        if(!empty($row['name'])) {
          $filter = $row['name'];
          break;
        }
      }
    }

    $options = $this->get_filter_fields($filter);
    $tmp_field = $choices = array();

    foreach($field_group as $row) {
      if($row['name'] == $filter) {
        $tmp_field = $row;
        break;
      }
    }
 
    if(!empty($tmp_field['choices'])) {
      foreach($options as $val) {
        $choices[$val] = $tmp_field['choices'][$val];
      }
    } else {
      foreach($options as $val) {
        $choices[$val] = $val;
      }
    }
    $field['choices'] = $choices;

    $fm_rule_row_index++;
    return $field;
  }

  function ajax_load_fm_rule_meta_values( ) {
    if (!wp_verify_nonce($_POST['nonce'], 'acf_nonce')) {
      die();
    }

    $options = $this->get_filter_fields($_POST['key']);
    $field_group = Flex_Maps_Single_Location_Fields::get_single_location_fields();
    $tmp_field = $choices = array();

    foreach($field_group as $row) {
      if($row['name'] == $_POST['key']) {
        $tmp_field = $row;
        break;
      }
    }

    if(!empty($tmp_field['choices'])) {
      foreach($options as $val) {
        $choices[] = array(
          'label' => $tmp_field['choices'][$val],
          'value' => $val,
        );
      }
    } else {
      $choices = $options;
    }

    if(empty($choices))
      wp_send_json_error();
    else
      wp_send_json_success($choices);
  }

  protected function get_filter_fields( $meta_key='' ) {
    if(empty($meta_key))
      return;

    global $wpdb;

    $sql = $wpdb->prepare("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != '' ORDER BY meta_value", $meta_key);
    $values = $wpdb->get_col($sql);

    return $values;
  }

  public function rule_group_repeater_before( $field ) {
    if($field['key'] != 'fm_repeater_rule_container')
      return;

    echo '<h4>Load the content if</h4>';
  }

  public function rule_group_repeater_after( $field ) {
    echo '<h4>or</h4>';
  }

}