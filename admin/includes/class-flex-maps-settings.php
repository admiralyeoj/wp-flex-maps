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

  protected $validate_fields;

  protected $index;

  public function __construct( $plugin_name, $version ) {
    $this->plugin_name = $plugin_name;
    $this->version = $version;

    $this->validate_fields = $this->index = array();
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

  public function load_rule_tax_group_repeater($field) {
    if(strpos($field['wrapper']['class'], 'fm-tax-rule-group') === false)
      return $field;

    $key = $field['key'];

    if(!isset($this->index[$key]['index'])) {
      $this->index[$key]['index'] = -1;
      return $field;
    }

    $this->index[$key]['index']++;
    unset($this->index[$key]['row']);

    return $field;
  }

  public function load_fm_tax_values($field) {
    $key = $field['key'];
    $parent = $field['parent'];
    $post_id = get_the_ID();

    // echo '<pre>';print_r($this->index);echo '</pre>';

    if(strpos($field['wrapper']['class'], 'fm-tax-rule-row') === false)
      return $field;

    if(!isset($this->index[$key]['row'])) {
      $this->index[$key]['row'] = 0;
      return $field;
    }

    $filter = get_post_meta($post_id, "fm_taxonomy_list_{$this->index[$key]['row']}_parameter", true);

    if(empty($filter)) {
      $taxonomies = get_object_taxonomies('fm_locations', 'object');
      $tax = reset($taxonomies);
      $filter = $tax->name;
    }

    $terms = get_terms( $filter, array(
      'hide_empty' => false,
    ) );

    if($terms) {
      foreach($terms as $term) {
        $choices[$term->slug] = $term->name;
      }

      $field['choices'] = $choices; 
    }

    $this->index[$key]['row']++;
    return $field;
  }

  function ajax_load_fm_rule_tax_values( ) {
    if (!wp_verify_nonce($_POST['nonce'], 'acf_nonce') || empty($_POST['key'])) {
      die();
    }

    $terms = get_terms( $_POST['key'], array(
      'hide_empty' => false,
    ) );

    if($terms) {
      foreach($terms as $term) {
        $choices[] = array(
          'label' => $term->name,
          'value' => $term->slug,
        );
      }
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

    return $return;
  }

  public function rule_group_repeater_before( $field ) {
    if($field['key'] != 'fm_repeater_rule_container')
      return;

    echo '<h4>Load the content if</h4>';
  }

  public function rule_group_repeater_after( $field ) {
    echo '<h4>or</h4>';
  }

  public function load_param_values( $field ) {
    // echo '<pre>';print_r($field); echo '</pre>';
    if(strpos($field['wrapper']['class'], 'custom-fields') !== false) {
      $field['choices'] = $this->get_custom_fields();
    } else if(strpos($field['wrapper']['class'], 'taxonomies') !== false) {
      $field['choices'] = $this->get_taxonomy_types();
    }
    
    return $field;
  }

  public function validate_field( $valid, $value, $field, $input_name ) {
    if( $valid !== true ) {
        return $valid;
    }
    $name = $field['name'];

    if(!isset($this->validate_fields[$name])) {
      $this->validate_fields[$name] = array($value);
      return $valid;
    }

    if(!empty($this->validate_fields[$name]) && in_array($value, $this->validate_fields[$name])) {
      return __( "Only one use allowed.", 'flex-maps' );
    }

    $this->validate_fields[$name][] = $value;
    
    return $valid;
  }

  public function maybe_disable_taxonomy_filter( $field ) {
    $display = true;

    $taxonomies = get_object_taxonomies('fm_locations');
    if($display && empty($taxonomies))
      $display = false;

    if(apply_filters('maybe_disable_taxonomy_filter', $display))
      return $field;
    else
      return false;
  }

  private function get_custom_fields() {
    if($choices = wp_cache_get("fm_search_values_{$field['key']}", $this->plugin_name)){
      return $choices;
    }

    $choices = array();

    $choices['Address Search Fields'] = array('id' => 'Post ID', 'address'=>'Address', 'radius'=>'Radius');
    
    $all_fields = Flex_Maps_Single_Location_Fields::get_single_location_fields();
    $label = '';

    if(!empty($all_fields)) {
      foreach($all_fields as $row) {
        if(empty($row['name'])) {
          $label = $row['label'];
          continue;
        }

        $choices[$label][$row['name']] = $row['label'];
      }
    }

    wp_cache_set("fm_search_values_{$field['key']}", $choices, $this->plugin_name);
    return $choices;
  }

  private function get_taxonomy_types() {
    if($choices = wp_cache_get("fm_taxonomy_search_values_{$field['key']}", $this->plugin_name)){
      return $choices;
    }

    $choices = array();

    $taxonomies = get_object_taxonomies('fm_locations', 'object');
    if(!empty($taxonomies)) {
      foreach ($taxonomies as $tax) {
        $choices[$tax->name] = "{$tax->labels->singular_name} ({$tax->name})";
      }
    }

    wp_cache_set("fm_taxonomy_search_values_{$field['key']}", $choices, $this->plugin_name);
    return $choices;
  }

}