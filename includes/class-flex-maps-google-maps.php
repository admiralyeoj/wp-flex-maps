<?php

/**
 * Gets info from Google Maps API
 *
 * @link       https://www.ignitro.com/
 * @since      1.0.0
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/includes
 */

/**
 * Gets info from Google Maps API.
 *
 * This class defines all code necessary to get data from the Google Maps API using PHP
 *
 * @since      1.0.0
 * @package    Flex_Maps
 * @subpackage Flex_Maps/includes
 * @author     Ignitro <plugin@ignitro.com>
 */
class Flex_Maps_Google_Maps {

  /**
   * Instance of this class.
   *
   * @since    1.0.0
   * @access   private
   * @var      object
   */
  protected static $instance = null;

  /**
   * The google maps key blocked by server IP.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $server_key    Key blocked by server IP
   */
  private $server_key = null;

  /**
   * Base url for the google api
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $server_key    Key blocked by server IP
   */
  private $base_url = '';

  /**
   * Return an instance of this class.
   *
   * @since     1.0.0
   *
   * @return    object    A single instance of this class.
   */
  public static function get_instance() {

    // If the single instance hasn't been set, set it now.
    if ( null == self::$instance ) {
      self::$instance = new self;
    }

    return self::$instance;
  }

  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   * @since    1.0.0
   */
  private function __construct($server_key='') {

    if(empty($server_key))
      $server_key = FLEX_MAPS_SERVER_API_KEY;

    $this->server_key = $server_key;
    $this->base_url = 'https://maps.googleapis.com/maps/api';
  }

  public function geocode($args = array(), $single=false, $return_type='json') {
    $endpoint_url = "{$this->base_url}/geocode/{$return_type}";
    $result = ['lat' => null, 'lng' => null];
    $query = [];
    $components = [];

    if (!empty($this->server_key)) {
      $query['key'] = $this->server_key;
    }
    foreach($args as $key => $arg) {
      $arg = $this->clean($arg);
      $arg = str_replace ( ' ', '+', urlencode( $arg ) );

      $query[$key] = urlencode($arg);
    }

    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $endpoint_url . '?' . http_build_query( $query ) );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $results = curl_exec( $ch );
    $response = json_decode( $results );
    curl_close($ch);

    // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED, OVER_DAILY_LIMIT, UNKNOWN_ERROR, or INVALID_REQUEST
    if ($response->status != 'OK') {
      return $response;
    }

    if($single) {
      $return = new stdClass;
      $return->result = $response->results[0];
      $return->status = $response->status;

      return $return;
    }

    return $response;
    

    /*switch ($response['results'][0]['types'][0]){
      case "administrative_area_level_1":

        $address_components = $response['results'][0]['address_components'];
        $geometry = $response['results'][0]['geometry'];

        $result['search'] = 'state';
        $result['state'] = $address_components[0]['short_name'];
        $result['long_name'] = $address_components[0]['long_name'];
        $result['bounds'] = $geometry['bounds'];
        break;
        
      default:

        $geometry = $response['results'][0]['geometry'];

        $result['search'] = 'latlng';
        $result['address'] = $response['results'][0]['formatted_address'];
    
        $result['lat'] = $geometry['location']['lat'];
        $result['lng'] = $geometry['location']['lng'];

        break;
    }*/

    return $result;
  }

  protected function clean($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
  }

}
