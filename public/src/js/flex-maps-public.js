import "./../css/location-element.css";
import "./../css/spinner.css";
import "./../css/marker.css";

import { FM_Google_Map } from './../../../includes/js/FM-Google-Map.js';

(function( $ ) {
	'use strict';

  // This enables you to define handlers, for when the DOM is ready:
  $(function() {
    
  });

  $( window ).load(function() {

  });

})( jQuery );

window.FM_Init_Map = () => {
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