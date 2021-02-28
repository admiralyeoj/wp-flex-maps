import "./../css/location-element.css";
import "./../css/spinner.css";
import "./../css/marker.css";

import { FM_Google_Map } from './../../../includes/js/FM-Google-Map.js';

(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );

function FM_Init_Map() {

  console.log(google.maps);
  console.log(google.maps.places);

  let location_input =  document.querySelectorAll('.fm-autocomplete');
  for (let i = 0; i < location_input.length; i++) {
    new google.maps.places.Autocomplete(location_input[i]);
  }
  
  let map_divs = document.querySelectorAll(".fm-google-map");
  if(map_divs.length > 0) {
    map_divs.forEach(function(elem, index) {
      let google_map = new FM_Google_Map(elem);
    });
  }
}
window.FM_Init_Map = FM_Init_Map;
