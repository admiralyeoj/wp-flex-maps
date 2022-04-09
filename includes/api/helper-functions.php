<?php 

  function fm_is_valid_mapId($mapId) {
    return (!empty($mapId) && get_post_type($mapId) == 'flex_maps') ? true : false;
  }

  function fm_get_meta($mapId, $meta = '', $value_only=false) {
    if(!fm_is_valid_mapId($mapId))
      return false;

    $fields = get_field('fm_parameter_list', $mapId);
    $return = array();

    if($fields) {
      foreach ($fields as $key => $val) {
        $search_param = $val['search_parameter'];
        $search_value = $val['search_value'];

        if(!empty($meta)) {
          if($meta != $search_param)
            continue;

          $search_value = apply_filters('fm_before_user_search_'.$mapId, $search_value, $mapId, $search_param );
          $search_value = apply_filters('fm_before_user_search_'.$mapId.'_'.$search_param, $search_value, $mapId, $search_param );

          $search_value = !empty($_REQUEST[$search_param]) ? sanitize_text_field($_REQUEST[$search_param]) : __($search_value, 'flex-maps');

          $search_value = apply_filters('fm_after_user_search_'.$mapId, $search_value, $mapId, $search_param );
          $search_value = apply_filters('fm_after_user_search_'.$mapId.'_'.$search_param, $search_value, $mapId, $search_param );

          $return[$search_param] = $search_value;
          break;
        }

        $search_value = apply_filters('fm_before_user_search_'.$mapId, $search_value, $mapId, $search_param );
        $search_value = apply_filters('fm_before_user_search_'.$mapId.'_'.$search_param, $search_value, $mapId, $search_param );

        $search_value = !empty($_REQUEST[$search_param]) ? sanitize_text_field($_REQUEST[$search_param]) : __($val['search_value'], 'flex-maps');

        $search_value = apply_filters('fm_after_user_search_'.$mapId, $search_value, $mapId, $search_param );
        $search_value = apply_filters('fm_after_user_search_'.$mapId.'_'.$search_param, $search_value, $mapId, $search_param );

        $return[$search_param] = $search_value;
      }
    }

    if($value_only)
      return array_values($return)[0];
    else
      return $return;
  }

  function fm_get_tax($mapId, $meta = '', $value_only=false) {
    if(!fm_is_valid_mapId($mapId))
      return false;

    $taxonomies = get_object_taxonomies('fm_locations');
    if($display && empty($taxonomies))
      $display = false;

    if(!apply_filters('maybe_disable_taxonomy_filter', $display))
      return false;

    $fields = get_field('fm_taxonomy_list', $mapId);
    $return = array();

    foreach ($fields as $key => $val) {
      $search_param = $val['search_parameter'];

      if(!empty($meta)) {
        if($meta != $val)
          continue;


        $return[$search_param] = !empty($_REQUEST[$search_param]) ? sanitize_text_field($_REQUEST[$search_param]) : __($val['search_value'], 'flex-maps');
        break;
      }

      $return[$search_param] = !empty($_REQUEST[$search_param]) ? sanitize_text_field($_REQUEST[$search_param]) : __($val['search_value'], 'flex-maps');
    }

    return $return;
  }
?>