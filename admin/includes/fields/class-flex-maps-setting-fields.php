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
    $load_types     = $this->get_load_type_fields();
    $latlng_fields  = $this->get_latlng_fields();
    $custom_fields  = $this->get_fields_fields();

    acf_add_local_field_group(array(
      'key' => 'fm_group_default_map_settings',
      'title' => 'Default Map Settings',
      'fields' => array($load_types, $latlng_fields, $custom_fields),
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

  protected function get_load_type_fields() {
    return array(
      'key' => 'fm_field_load_type',
      'label' => 'Default Load Settings',
      'name' => 'fm_load_type',
      'type' => 'select',
      'choices' => array(
        'all' => 'All Locations',
        'latlng' => 'Set Latitude and Longitude',
        'fields' => 'Search Custom Fields',
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
            'value' => 'fields',
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
                    '=' => 'is equal to',
                    '!=' => 'is not equal to',
                  ),
                ),
                array(
                  'key' => 'fm_field_rule_meta_value',
                  'label' => '35',
                  'name' => 'value',
                  'type' => 'select',
                  'wrapper' => array(
                    'width' => '',
                    'class' => 'fm-rule-meta-value',
                  ),
                  'choices' => array(
                  ),
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
    
}