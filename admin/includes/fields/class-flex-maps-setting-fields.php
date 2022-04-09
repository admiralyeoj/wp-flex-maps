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
class Flex_Maps_Setting_Fields extends Flex_Maps_Fields {

  public function __construct() {

  }

  public function register_fields() {

    $this->register_default_map_settings();
  }

  /* On Load Map Functions */
  protected function register_default_map_settings() {
    
    $general_tab = $this->get_general_tab();
    $filter_tab = $this->get_filter_tab();
    $merge_array = array_merge($general_tab, $filter_tab);

    acf_add_local_field_group(array(
      'key' => 'fm_group_default_map_settings',
      'title' => 'Default Map Settings',
      'fields' => $merge_array,
      'location' => array(
        array(
          array(
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'flex_maps',
          ),
        ),
      ),
      'menu_order' => 0,
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => '',
      'active' => true,
      'description' => '',
    ));
  }

  protected function get_general_tab() {
    $general_tab = array(
      'key' => 'fm_general_tab',
      'label' => 'General',
      'name' => '',
      'type' => 'tab',
      'placement' => 'left',
    );

    $load_types     = $this->get_load_type_fields();
    $latlng_fields  = $this->get_latlng_fields();
    $custom_fields  = $this->get_fields_fields();

    return array($general_tab, $load_types, $latlng_fields, $custom_fields);
  }

  protected function get_filter_tab() {
    $filter_tab = array(
      array(
        'key' => 'fm_filter_tab',
        'label' => 'Filter',
        'name' => '',
        'type' => 'tab',
        'conditional_logic' => array(
          array(
            array(
              'field' => 'fm_field_load_type',
              'operator' => '!=',
              'value' => 'static',
            ),
          ),
        ),
      ),
    );

    // $address = $this->get_address_filter_fields();
    $custom = $this->get_custom_filter_fields();
    $tax = $this->get_taxonomy_filter_fields();

    return array_merge($filter_tab, $custom, $tax);
  }

  protected function get_load_type_fields() {
    return array(
      'key' => 'fm_field_load_type',
      'label' => 'Default Load Settings',
      'name' => 'fm_load_type',
      'type' => 'select',
      'choices' => array(
        'all' => 'All Locations',
        'latlng' => 'Set Latitude and Longitude',
        'static' => 'Static Search',
      ),
    );
  }

  protected function get_latlng_fields() {
    return array(
      'key' => 'fm_group_load_type_latlng',
      'name' => 'fm_load_type_latlng',
      'type' => 'group',
      'conditional_logic' => array(
        array(
          array(
            'field' => 'fm_field_load_type',
            'operator' => '==',
            'value' => 'latlng',
          ),
        ),
      ),
      'layout' => 'row',
      'sub_fields' => array(
        array(
          'key' => 'fm_field_lat',
          'label' => 'Latitude',
          'name' => 'lat',
          'type' => 'number',
          'wrapper' => array(
            'class' => 'fm-no-arrows',
          ),
        ),
        array(
          'key' => 'fm_field_lng',
          'label' => 'Longitude',
          'name' => 'lng',
          'type' => 'number',
          'wrapper' => array(
            'class' => 'fm-no-arrows',
          ),
        ),
        array(
          'key' => 'fm_field_zoom',
          'label' => 'Zoom',
          'name' => 'zoom',
          'type' => 'number',
          'wrapper' => array(
            'class' => 'fm-no-arrows',
          ),
          'placeholder' => 10,
        ),
        array(
          'key' => 'fm_field_show_marker',
          'label' => 'Show Marker',
          'name' => 'show_marker',
          'type' => 'true_false',
          'message' => 'Show Marker at Specified Latitude and Longitude',
        ),
        array(
          'key' => 'fm_field_popup_type',
          'label' => 'Popup Window Type',
          'name' => 'popup_type',
          'type' => 'select',
          'conditional_logic' => array(
            array(
              array(
                'field' => 'fm_field_show_marker',
                'operator' => '==',
                'value' => '1',
              ),
            ),
          ),
          'choices' => array(
            'none' => 'None',
            'load' => 'Show On Load',
            'click' => 'Show On Marker Click',
          ),
        ),
        array(
          'key' => 'fm_field_popup_window_content',
          'label' => 'Popup Window Content',
          'name' => 'popup_window_content',
          'type' => 'textarea',
          'conditional_logic' => array(
            array(
              array(
                'field' => 'fm_field_show_marker',
                'operator' => '==',
                'value' => '1',
              ),
              array(
                'field' => 'fm_field_popup_type',
                'operator' => '!=',
                'value' => 'None',
              ),
            ),
          ),
          'new_lines' => 'br',
        ),
      ),
    );
  }

  protected function get_fields_fields() {
    return array(
      'key' => 'fm_group_load_type_fields',
      'name' => 'fm_load_type_fields',
      'type' => 'group',
      'conditional_logic' => array(
        array(
          array(
            'field' => 'fm_field_load_type',
            'operator' => '==',
            'value' => 'static',
          ),
        ),
      ),
      'layout' => 'row',
      'sub_fields' => array(
        array(
          'key' => 'fm_repeater_rule_container',
          'label' => 'Search',
          'name' => 'rule_container',
          'type' => 'repeater',
          'instructions' => 'Search rules to load the content',
          'wrapper' => array(
            'class' => 'fm-logic-group',
          ),
          'min' => 1,
          'layout' => 'table',
          'button_label' => 'Add Search Group',
          'sub_fields' => array(
            array(
              'key' => 'fm_repeater_rule_group',
              'name' => 'rule_group',
              'type' => 'repeater',
              'wrapper' => array(
                'class' => 'fm-rule-group',
              ),
              'collapsed' => '',
              'min' => 1,
              'layout' => 'table',
              'button_label' => 'Add Rule',
              'sub_fields' => array(
                array(
                  'key' => 'fm_field_rule_meta_key',
                  'name' => 'key',
                  'type' => 'select',
                  'wrapper' => array(
                    'width' => '40',
                    'class' => 'fm-rule-meta-key',
                  ),
                  'choices' => array(
                  ),
                ),
                array(
                  'key' => 'fm_field_rule_meta_compare',
                  'name' => 'compare',
                  'type' => 'select',
                  'wrapper' => array(
                    'width' => '15',
                    'class' => 'fm-rule-meta-compare',
                  ),
                  'choices' => array(
                    '=' => '=',
                    '!=' => '!=',
                    '>' => '>',
                    '<' => '<',
                    '<=' => '<=',
                    '>=' => '>=',
                    'LIKE' => 'LIKE',
                  ),
                ),
                array(
                  'key' => 'fm_field_rule_meta_value',
                  'label' => '',
                  'name' => 'value',
                  'type' => 'text',
                ),
                array(
                  'key' => 'fm_message_add_new_row',
                  'name' => '',
                  'type' => 'message',
                  'instructions' => '',
                  'required' => 0,
                  'conditional_logic' => 0,
                  'wrapper' => array(
                    'width' => '1',
                    'class' => 'fm-add-btn',
                  ),
                  'message' => '<a class="button add-conditional-rule fm-add-row" href="#">add</a>',
                  'new_lines' => '',
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  /* End On Load Map Functions */

  public function get_address_filter_fields() {
    if($fields = wp_cache_get('fm_setting_address_filter_fields'))
      return $fields;

    $fields = array(
      array(
        'key' => 'fm_enable_address_seach_param',
        'label' => 'Address Search',
        'name' => 'allow_address_search',
        'type' => 'true_false',
        'wrapper' => array(
          'width' => '50',
        ),
        'message' => 'Enables address search',
      ),
      array(
        'key' => 'fm_address_seach_param',
        'label' => 'Address Search Parameter',
        'name' => 'address_search_parameter',
        'type' => 'text',
        'conditional_logic' => array(
          array(
            array(
              'field' => 'fm_enable_address_seach_param',
              'operator' => '==',
              'value' => '1',
            ),
          ),
        ),
        'wrapper' => array(
          'width' => '50',
        ),
        'placeholder' => 'fm-search',
      ),
    );

    $fields = apply_filters('fm_global_settings_address_fields_filter', $fields);
    wp_cache_add('fm_setting_address_filter_fields', $fields);

    return $fields;
  }

  public function get_custom_filter_fields() {
    if($fields = wp_cache_get('fm_setting_custom_filter_fields'))
      return $fields;

    $fields = array(
      array(
        'key' => 'fm_search_param_list',
        'label' => 'Custom Fields',
        'name' => 'fm_parameter_list',
        'type' => 'repeater',
        'layout' => 'table',
        'button_label' => '',
        'sub_fields' => array(
          array(
            'key' => 'fm_custom_param_field',
            'label' => 'Parameter',
            'name' => 'parameter',
            'type' => 'select',
            'choices' => array(
            ),
            'return_format' => 'value',
            'wrapper' => array(
              'class' => 'custom-fields',
            ),
          ),
          array(
            'key' => 'fm_custom_param_name',
            'label' => 'Search Parameter',
            'name' => 'search_parameter',
            'type' => 'text',
            'required' => 1,
          ),
          array(
            'key' => 'fm_custom_param_value',
            'label' => 'Default Value',
            'name' => 'search_value',
            'type' => 'text',
          ),
        ),
      ),
    );

    $fields = apply_filters('fm_global_settings_custom_fields_filter', $fields);
    wp_cache_add('fm_setting_custom_filter_fields', $fields);

    return $fields;
  }

  public function get_taxonomy_filter_fields() {
    if($fields = wp_cache_get('fm_setting_taxonomy_fields_filter'))
      return $fields;

    $fields = array(
      array(
        'key' => 'fm_search_taxonomy_param_list',
        'label' => 'Taxonomies',
        'name' => 'fm_taxonomy_list',
        'type' => 'repeater',
        'layout' => 'table',
        'button_label' => '',
        'wrapper' => array(
          'class' => 'fm-tax-rule-group',
        ),
        'sub_fields' => array(
          array(
            'key' => 'fm_taxonomy_param_field',
            'label' => 'Parameter',
            'name' => 'parameter',
            'type' => 'select',
            'choices' => array(
            ),
            'return_format' => 'value',
            'wrapper' => array(
              'class' => 'taxonomies fm-rule-taxonomy-key',
            ),
          ),
          array(
            'key' => 'fm_taxonomy_param_name',
            'label' => 'Search Parameter',
            'name' => 'search_parameter',
            'type' => 'text',
            'required' => 1,
          ),
          array(
            'key' => 'fm_taxonomy_param_value',
            'label' => 'Default Value',
            'name' => 'search_value',
            'type' => 'select',
            'choices' => array(),
            'wrapper' => array(
              'class' => 'fm-tax-rule-row',
            ),
            'allow_null' => true,
          ),
        ),
      ),
    );

    $fields = apply_filters('fm_global_settings_taxonomy_fields_filter', $fields);
    wp_cache_add('fm_setting_taxonomy_fields_filter', $fields);

    return $fields;
  }
    
}