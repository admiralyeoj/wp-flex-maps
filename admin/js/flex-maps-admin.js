import { FM_Google_Map } from 'FMGoogleMap';

(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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

  
  $(function() {
    $('.fm-phone-mask').on('input', function(e) {
      var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
      e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
    });
  });

})( jQuery );

function FM_Init_Map() {
  jQuery(function($) {
    let map_divs = document.querySelectorAll(".fm-google-map");

    if(map_divs.length > 0) {
      map_divs.forEach(function(elem, index) {
        let google_map = new FM_Google_Map(elem);
      });
    }
  });
}
window.FM_Init_Map = FM_Init_Map;