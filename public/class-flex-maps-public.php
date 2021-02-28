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

	}

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles($hook) {

    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'dist/css/index.css', array(), $this->version, 'all' );
  }

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'dist/js/index.min.js', array( 'jquery' ), $this->version, false );

    wp_enqueue_script( $this->plugin_name.'-map-api', 'https://maps.googleapis.com/maps/api/js?key='.FLEX_MAPS_BROWSER_API_KEY.'&callback=FM_Init_Map&libraries=places', array( 'jquery', $this->plugin_name ), $this->version, true );
    
    $javascript_data = array(
      'file_path' => FLEX_MAPS_PLUGIN_PATH.'public/',
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'search' => get_query_var('search'),
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
      'height' => null,
    ), $atts));

    ob_start();

    if(!$this->is_valid_id($id)) {
      include( 'partials/fm-display-error.php' );
    } else {
      $mapId = intval($id);

      $attributes = array();
      $load_type = get_field('fm_load_type', $mapId);

      if($load_type == 'latlng')
        $attributes = get_field('fm_load_type_latlng', $mapId);
      
      include( 'partials/fm-google-map.php' );
    }

    return ob_get_clean();
  }

  /*
     * location_search - added by this plugin
     * 
     * @param search - parameter to search by - Default = ''
     * @param search_type - Default = geocode
     *   - geocode - geocodes location
     *   - location_name - search location name
     *   - state - searches state field
     *   - country - Future version
     * @params - compare - compare works on any except geocode - Default =
     * @param radius - radius to search by. Uses settings radius if empty
     *   - Only Works with Geocoding
     */
    /*'location_search' => array(
        'search' => 'Ohio',
        'search_type' => 'state',
        'radius' => 15,
      ),*/

  /**
   * Output for the location element displayed on the page
   *
   * @since 1.0.0
   */
  public function fm_location_element( $atts = array() ) {

    extract(shortcode_atts(array(
      'id' => null,
      'height' => '300px',
    ), $atts));

    if(!$this->is_valid_id($id)){
      ob_start();
      include( 'partials/fm-display-error.php' );
      return ob_get_clean();
    }
    
    $posts = $this->get_locations_by_map_id(intval($id));

    ob_start();
    echo "<div class='flex-map-element flex-map-{$id}'>";
    if(!empty($posts)) {
      global $post; $i = 1;

      foreach($posts as $post) {
        $post = get_post( $post->ID, OBJECT );
        setup_postdata( $post );

        $data = $this->get_location_data(get_the_ID());

        include( 'partials/fm-location-element.php' );
        $i++;
      }
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
    if(!$this->is_valid_id($_POST['mapId']))
       wp_send_json_error(array('message'=>'Valid map ID is required'));

    $posts = $this->get_locations_by_map_id(intval($_POST['mapId']));

    if($posts) {
      global $post;

      foreach($posts as $post) {
        $post = get_post( $post->ID, OBJECT );
        setup_postdata( $post );

        $data = $this->get_location_data($post->ID);

        ob_start();
        include( 'partials/fm-marker-popup.php' );
        $marker_html = ob_get_clean();

        $locations[] = array(
          'ID' => $post->ID,
          'lat' => $data['latitude'],
          'lng' => $data['longitude'],
          'marker_html' => $marker_html,
        );
      } wp_reset_postdata();
    }

    wp_send_json_success( array('locations' => $locations) );
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
      global $wpdb;
      $google_maps = Flex_Maps_Google_Maps::get_instance();

      if($args['search_type'] == 'geocode') {
        $clauses = $this->search_by_geocode($clauses);
      }
    }
    
    return $clauses;
  }

  public function query_custom_fields($query) {
    $args = $query->get('location_search');
    if(!empty($args) && $args['search_type'] != 'geocode') {

      if($args['search_type'] == 'state' && (empty($args['compare']) || $args['compare'] == '=')) {
        $states = get_state_array_name_abbr();
        $tmp_search = ucwords(strtolower($args['search']));
        $args['search'] = !empty($states[$tmp_search]) ? $states[$tmp_search] : $args['search'];
      }

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

  private function search_by_geocode($clauses) {
    $geocode = $google_maps->geocode(array('address' => $args['location']), true);

    if($geocode->status != 'OK') {
      $clauses['where'] .= ' AND 1=0 ';
      return $clauses;
    }

    $latlng = $geocode->result->geometry->location;
    $radius = !empty($args['radius']) ? $args['radius'] : FLEX_MAPS_DEFAULT_RADIUS;

    $clauses['join']   .= " INNER JOIN {$wpdb->postmeta} as fm_latitude ON ( {$wpdb->posts}.ID = fm_latitude.post_id AND  fm_latitude.meta_key = 'fm_latitude' ) INNER JOIN {$wpdb->postmeta} as fm_longitude ON ( {$wpdb->posts}.ID = fm_longitude.post_id AND fm_longitude.meta_key = 'fm_longitude')";
    $clauses['fields']  .= $wpdb->prepare(", (ROUND ( 3959 * acos( cos( radians(%f) ) * cos( radians( fm_latitude.meta_value ) ) * cos( radians( fm_longitude.meta_value ) - radians(%f) ) + sin( radians(%f) ) * sin( radians( fm_latitude.meta_value ) ) ), 2 ) ) AS distance", $latlng->lat, $latlng->lng, $latlng->lat);

    if(empty($clauses['groupby'])) {
      $clauses['groupby'] = "{$wpdb->posts}.ID";
    }
    $clauses['groupby'] .= $wpdb->prepare(' HAVING distance <= %f', $radius);

    return $clauses;
  }

  private function get_locations_by_map_id($madId = null) {
    if($mapId = $this->is_valid_id($mapId)) 
      return false;

    global $wpdb;
    $mapId = intval($madId);
    $load_type = get_field('fm_load_type', $mapId);

    if( $load_type == 'latlng' )
      return;

    $query = array(
      'select' => 'SELECT posts.*',
      'from' => "FROM {$wpdb->posts} posts",
      'join' => '',
      'where' => 'WHERE posts.post_type = "fm_locations" AND posts.post_status = "publish"',
      'groupby' => 'GROUP BY posts.ID',
      'orderby' => 'ORDER BY posts.post_title',
    );

    if($load_type == 'fields') {
      $currentJoins = 0;
      $settings = get_field('fm_load_type_fields', $mapId);
      
      if(!empty($settings['rule_container'])) {
        $where = array();
        foreach($settings['rule_container'] as $group) {
          $key = 1; $args = array();
          $count = count($group['rule_group']);

          if($currentJoins < $count) {
            for ($i=$currentJoins; $i < $count; $i++) {
              $query['join'] .= "INNER JOIN {$wpdb->postmeta} pm{$currentJoins} ON ( posts.ID = pm{$currentJoins}.post_id ) ";
              $currentJoins++;
            }
          }

          foreach ($group['rule_group'] as $key => $row) {
            $compare = esc_sql($row['compare']);
            $args[] = $wpdb->prepare("(pm{$key}.meta_key = %s AND pm{$key}.meta_value {$compare} %s)", $row['key'], $row['value']);
          }
          $where[] = implode(' AND ', $args);
        }        
      }
      $query['where'] .= ' AND ('.implode(' OR ', $where).')';
    }

    $sql = implode(' ', $query);
    return $wpdb->get_results($sql);
  }

  private function is_valid_id($mapId) {
    return (!empty($mapId) && get_post_type($mapId) == 'flex_maps') ? true : false;
  }

}
