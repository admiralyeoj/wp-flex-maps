<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.ignitro.com/
 * @since      1.0.0
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/public
 * @author     Ignitro <plugin@ignitro.com>
 */
class Flex_Maps_Public {

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

  protected $geocode;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

    $this->geocode = array();
	}

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles($hook) {

    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bundle.min.css', array(), $this->version, 'all' );
  }

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bundle.min.js', array( 'jquery' ), $this->version, true );

    wp_enqueue_script( $this->plugin_name.'-map-api', 'https://maps.googleapis.com/maps/api/js?key='.FLEX_MAPS_BROWSER_API_KEY.'&callback=FM_Init_Map&libraries=places', array( 'jquery', $this->plugin_name ), $this->version, true );
    
    $javascript_data = array(
      'file_path' => FLEX_MAPS_PLUGIN_PATH.'public/',
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'map' => array(),
    );

    wp_add_inline_script($this->plugin_name, "var flex_map = ".json_encode($javascript_data), 'before');
	}

  /**
   * Registers all shortcodes needed
   *
   * fm-google-map - For the map
   * fm-location-element - Display each element location
   *
   * @return [type] [description]
   */
  public function register_shortcodes() {
    add_shortcode( 'fm-google-map', array( $this, 'fm_google_map' ) );
    add_shortcode( 'fm-location-element', array( $this, 'fm_location_element' ) );
  }

  /**
   * Output for the google map container displayed on the page
   *
   * @since 1.0.0
   */
  public function fm_google_map( $atts = array() ) {
    extract(shortcode_atts(array(
      'unique_id' => wp_unique_id('flex-map-'),
      'id' => null,
      'height' => '300px',
    ), $atts));

    ob_start();

    if(!fm_is_valid_mapId($id)) {
      include( 'partials/fm-display-error.php' );
    } else {
      $mapId = intval($id);

      $attributes = array();
      $load_type = get_field('fm_load_type', $mapId);

      if($load_type == 'latlng')
        $attributes = get_field('fm_load_type_latlng', $mapId);
      
      include __DIR__.'/partials/fm-google-map.php';
    }

    $meta_vars = fm_get_meta($id);
    $tax_vars = fm_get_tax($id);

    $shortcode_data = array('query' => array('meta_vars' => $meta_vars, 'tax_vars' => $tax_vars));

    wp_add_inline_script($this->plugin_name, "flex_map['map']['{$unique_id}'] = ".json_encode($shortcode_data), 'before');
    return ob_get_clean();
  }

  public function fm_location_element( $atts = array() ) {

    extract(shortcode_atts(array(
      'id' => null,
      'height' => '300px',
    ), $atts));

    if(!fm_is_valid_mapId($id)){
      ob_start();
      include( 'partials/fm-display-error.php' );
      return ob_get_clean();
    }

    $id = intval($id);
    $params['meta_vars'] = fm_get_meta($id);
    $params['tax_vars'] = fm_get_tax($id);

    $posts = $this->get_locations_by_map_id($id, $params);

    ob_start();
    echo "<div class='flex-map-element flex-map-{$id}'>";
    if(!empty($posts)) {
      global $post; $i = 1;

      foreach($posts as $post) {
        setup_postdata( $post );

        $data = $this->get_location_data(get_the_ID());

        include locate_template('flex-maps/display-location.php') ?: __DIR__.'/partials/fm-location-element.php';
        $i++;
      } wp_reset_postdata();
    } else {
      $geo = $this->geocode[$id];
      $meta_array = get_field('fm_parameter_list', $mapId);
      $radius = !empty($meta_array['radius']) ? $meta_array['radius'] : FLEX_MAPS_DEFAULT_RADIUS;

      include locate_template('flex-maps/display-error-locations.php') ?: __DIR__.'/partials/fm-error-location-element.php';
    }
    echo "</div>";

    return ob_get_clean();
  }

  /**
   * Adds defer and async to anything with plugin_name+'-map-api' in the enqueue name
   *
   * @since 1.0.0
   */
  public function add_defer_async_attr($tag='', $handle='', $src='') {
    if ( $this->plugin_name.'-map-api' !== $handle )
      return $tag;

    return str_replace( ' src', ' async defer src', $tag );
  }

  public function get_locations() {
    if(!fm_is_valid_mapId($_POST['mapId']))
       wp_send_json_error(array('message'=>'Valid map ID is required'));

    $query['meta_vars'] = !empty($_POST['meta_vars']) ? $_POST['meta_vars'] : array();
    $query['tax_vars'] = !empty($_POST['tax_vars']) ? $_POST['tax_vars'] : array();

    $posts = $this->get_locations_by_map_id(intval($_POST['mapId']), $query);

    $origin = array();
    if(!empty($this->geocode[$_POST['mapId']])) {
      $origin = $this->geocode[$_POST['mapId']];
      ob_start();
      include locate_template(array('flex-maps/marker-error-popup.php')) ?: __DIR__.'/partials/fm-error-marker-popup.php';
      $marker_html = ob_get_clean();
      $origin->html = $marker_html;
    }

    if($posts) {
      global $post;

      foreach($posts as $post) {
        $post = get_post( $post->ID, OBJECT );
        setup_postdata( $post );

        $data = $this->get_location_data($post->ID);

        ob_start();
        if( locate_template(array('flex-maps/marker-popup-'.$_POST['mapId'].'.php')) ) {
          include locate_template(array('flex-maps/marker-popup-'.$_POST['mapId'].'.php'));
        } else if( locate_template(array('flex-maps/marker-popup.php')) ) {
          include locate_template(array('flex-maps/marker-popup.php'));
        } else {
          include __DIR__.'/partials/fm-marker-popup.php';
        }

        $marker_html = ob_get_clean();

        $marker_options = array(
          'ID' => $post->ID,
          'lat' => $data['latitude'],
          'lng' => $data['longitude'],
          'marker_html' => $marker_html,
        );

        $marker_options = apply_filters('fm_marker_options', $marker_options, $_POST['mapId'], $post->ID, $query['meta_vars'], $query['tax_vars']);

        $locations[] = $marker_options;
      } wp_reset_postdata();
    }

    wp_send_json_success( array('locations' => $locations, 'origin' => $origin) );
  }

  public function get_location_data($post_id=null) {
    if(empty($post_id))
      return false;

    global $wpdb;

    $sql = $wpdb->prepare("SELECT * FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key LIKE 'fm_%'", array($post_id));
    $results = $wpdb->get_results($sql);
    $loc_data = array();

    foreach($results as $data) {
      $key = str_replace('fm_', '', $data->meta_key);
      $loc_data[$key] = $data->meta_value;
    }

    return $loc_data;
  }

  public function modify_search_query( $clauses, $query ) {
    if($args = $query->get('location_search')) {
      if($args['search_type'] == 'geocode') {
        $clauses = $this->search_by_geocode($clauses, $args);
      }
    }
    
    return $clauses;
  }

  public function query_custom_fields($query) {
    $args = $query->get('location_search');
    if(!empty($args) && $args['search_type'] != 'geocode') {

      $meta_query = !empty($query->get('meta_query')) ? $query->get('meta_query') : array();
      $meta_query['compare'] = 'AND';
      $meta_query[] = array(
        'key' => "fm_{$args['search_type']}",
        'value' => $args['search'],
        'compare' => $args['compare'],
      );
      $query->set('meta_query', $meta_query);
    }

    return; 
  }

  protected function get_static_query( $mapId, $query ) {
    global $wpdb;
    $joins = 0;
    $settings = get_field('fm_load_type_fields', $mapId);
    
    if(!empty($settings['rule_container'])) {
      $where = array();
      foreach($settings['rule_container'] as $group) {
        $key = 1; $args = array();
        $count = count($group['rule_group']);

        if($joins < $count) {
          for ($i=$joins; $i < $count; $i++) {
            $query['join'] .= " INNER JOIN {$wpdb->postmeta} fm_pm{$joins} ON ( {$wpdb->posts}.ID = fm_pm{$joins}.post_id ) ";
            $joins++;
          }
        }

        foreach ($group['rule_group'] as $key => $row) {
          $compare = esc_sql($row['compare']);
          $args[] = $wpdb->prepare("(fm_pm{$key}.meta_key = %s AND fm_pm{$key}.meta_value {$compare} %s)", $row['key'], $row['value']);
        }
        $where[] = implode(' AND ', $args);
      }        
    }
    $query['where'] .= ' AND ('.implode(' OR ', $where).')';
    return $query;
  }

  protected function get_meta_query( $mapId, $query, $meta ) {
    global $wpdb;

    $meta_array = get_field('fm_parameter_list', $mapId);
    if(!empty($meta_array)) {

      $args = array();
      foreach ($meta_array as $row) {
        $key = $row['search_parameter'];
        $search = !empty($meta[$key]) ? sanitize_text_field($meta[$key]) : $row['search_value'];
        $custom_field = $row['parameter'];

        if(empty($search))
          continue;

        if($custom_field=='address') {
          $geo = array(
            'location' => $search,
            'radius' => !empty($meta_array['radius']) ? $meta_array['radius'] : FLEX_MAPS_DEFAULT_RADIUS,
          );
          $query = $this->search_by_geocode($query, $geo, $mapId);
        } else if($custom_field=='id') {
          $args[] = $wpdb->prepare("({$wpdb->posts}.ID = %d)", $search);
        } else {
          $query['join'] .= " INNER JOIN {$wpdb->postmeta} fm_pm{$key} ON ( {$wpdb->posts}.ID = fm_pm{$key}.post_id ) ";
          $args[] = $wpdb->prepare("(fm_pm{$key}.meta_key = %s AND fm_pm{$key}.meta_value = %s)", $custom_field, $search);
        }
      }

      if(!empty($args)){
        $query['where'] .= ' AND '.implode(' AND ', $args);
      }

    }

    return $query;
  }

  protected function get_tax_query($mapId, $query, $tax) {
    global $wpdb;
    $tax_array = get_field('fm_taxonomy_list', $mapId);

    if(!empty($tax_array) && is_array($tax_array)) {
      $join = 0;
      foreach ($tax_array as $row) {
        $key = $row['search_parameter'];
        $taxonomy = $row['parameter'];
        $term_val = !empty($tax[$key]) ? sanitize_text_field($tax[$key]) : $row['search_value'];

        if(empty($term_val))
          continue;

        $term = get_term_by('slug', $term_val, $taxonomy);

        if(!empty($term)) {
          $query['join'] .= "INNER JOIN {$wpdb->term_relationships} AS term_relationships_{$join} ON
            ({$wpdb->posts}.ID = term_relationships_{$join}.object_id)";
          $query['where'] .= $wpdb->prepare(" AND term_relationships_{$join}.term_taxonomy_id IN(%d)", $term->term_taxonomy_id);

          $join++;
        } else {
          $query['where'] .= '0=1';
        }
      }
    }

    return $query;
  }


  /* Private Functions */

  private function search_by_geocode($clauses, $args = array(), $mapId = null) {
    global $wpdb;
    $google_maps = Flex_Maps_Google_Maps::get_instance();

    $geocode = $google_maps->geocode(array('address' => $args['location']), true);

    if($geocode->status != 'OK') {
      $clauses['where'] .= ' AND 1=0 ';
      return $clauses;
    }

    $latlng = $geocode->result->geometry->location;
    $radius = !empty($args['radius']) ? $args['radius'] : FLEX_MAPS_DEFAULT_RADIUS;

    $clauses['join']   .= " INNER JOIN {$wpdb->postmeta} as fm_latitude ON ( {$wpdb->posts}.ID = fm_latitude.post_id ) INNER JOIN {$wpdb->postmeta} as fm_longitude ON ( {$wpdb->posts}.ID = fm_longitude.post_id)";
    $clauses['fields']  .= $wpdb->prepare(", (ROUND ( 3959 * acos( cos( radians(%f) ) * cos( radians( fm_latitude.meta_value ) ) * cos( radians( fm_longitude.meta_value ) - radians(%f) ) + sin( radians(%f) ) * sin( radians( fm_latitude.meta_value ) ) ), 2 ) ) AS distance", $latlng->lat, $latlng->lng, $latlng->lat);
    $clauses['where'] .= " AND fm_latitude.meta_key = 'fm_latitude' AND fm_longitude.meta_key = 'fm_longitude'";

    if( empty($clauses['groupby']) ) {
      $clauses['groupby'] = "{$wpdb->posts}.ID";
    }
    
    $clauses['groupby'] .= $wpdb->prepare(' HAVING distance <= %f', $radius);
    $clauses['orderby'] = 'distance';

    if(!empty($mapId))
      $this->geocode[$mapId] = $geocode->result;

    return $clauses;
  }

  private function get_locations_by_map_id($madId = null, $params = array()) {
    if($mapId = fm_is_valid_mapId($mapId))
        return false;
    
    global $wpdb;
    $mapId = intval($madId);
    $load_type = get_field('fm_load_type', $mapId);
    
    if( $load_type == 'latlng' )
        return;
    
    $clauses = array(
       'fields' => "{$wpdb->posts}.*",
       'from' => "{$wpdb->posts}",
       'join' => '',
       'where' => "AND {$wpdb->posts}.post_type = 'fm_locations' AND {$wpdb->posts}.post_status = 'publish'",
       'groupby' => "GROUP BY {$wpdb->posts}.ID",
       'orderby' => "{$wpdb->posts}.post_title",
       'limits' => '',
     );
    
    if($load_type == 'static') {
        $clauses = $this->get_static_query($mapId, $clauses);
    } else {
        $meta = !empty($params['meta_vars']) ? $params['meta_vars'] : array();
        $clauses = $this->get_meta_query($mapId, $clauses, $meta);
        
        $tax = !empty($params['tax_vars']) ? $params['tax_vars'] : array();
        $clauses = $this->get_tax_query($mapId, $clauses, $tax);
    }
    
    $where    = isset( $clauses['where'] ) ? $clauses['where'] : '';
    $groupby  = isset( $clauses['groupby'] ) ? $clauses['groupby'] : '';
    $join     = isset( $clauses['join'] ) ? $clauses['join'] : '';
    $orderby  = isset( $clauses['orderby'] ) ? $clauses['orderby'] : '';
    $distinct = isset( $clauses['distinct'] ) ? $clauses['distinct'] : '';
    $fields   = isset( $clauses['fields'] ) ? $clauses['fields'] : '';
    $limits   = isset( $clauses['limits'] ) ? $clauses['limits'] : '';
    
    $sql = "SELECT {$distinct} {$fields} FROM {$wpdb->posts} {$join} WHERE 1=1 {$where} {$groupby} ORDER BY {$orderby} {$limits}";

    return $wpdb->get_results($sql);
  }
}

function display_no_location_message() {
  
}
