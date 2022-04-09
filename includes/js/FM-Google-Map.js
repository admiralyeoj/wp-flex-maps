export class FM_Google_Map {
  constructor(element, query = []) {
    this.valid = true;
    if(!element){
      this.valid = false;
      return false;
    }

    let _self = this;
    this.markers = [];
    this.zoom = 10;

    this.element = element;
    this.mapId = this.element.getAttribute("data-map");
    this.meta_vars = query['meta_vars'] || [];
    this.tax_vars = query['tax_vars'] || [];
    this.initialZoom = true;

    this.myOptions = {
      zoom: 4,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
    };
    this.infoWindow = new google.maps.InfoWindow({post_id: null});

    google.maps.event.addListener(this.infoWindow, 'domready', function() {
      let dom = document.getElementById('post-'+this.post_id).parentNode.parentNode.parentNode;
      dom.classList.add("fm-marker");
    });

    this.map = new google.maps.Map(element, {
      center: { lat: 0, lng: 0 },
      zoom: 1,
      zoomControl: true,
      zoomControlOptions: {
        position: google.maps.ControlPosition.LEFT_BOTTOM,
      },
      fullscreenControl: true,
      fullscreenControlOptions: {
        position: google.maps.ControlPosition.RIGHT_BOTTOM,
      },
      mapTypeControl: false,
      streetViewControl: false,
      scaleControl: false,
    });

    google.maps.event.addListener(this.map, 'zoom_changed', function() {
      var zoomChangeBoundsListener = 
        google.maps.event.addListener(_self.map, 'bounds_changed', function(event) {
          if (this.getZoom() > 17 && _self.initialZoom == true) {
            this.setZoom(17);
            _self.initialZoom = false;
          }
        google.maps.event.removeListener(zoomChangeBoundsListener);
      });
    });

    if(this.element.getAttribute("data-load-type") == 'latlng')
      this.load_by_latlng();
    else
      this.load_by_search();
  }

  load_by_search() {
    let _self = this;
    if(!this.valid)
      return;
    
    var data = {
      'action': 'flex_maps_get_locations',
      'mapId' : this.mapId,
      'meta_vars': this.meta_vars,
      'tax_vars': this.tax_vars,
    };

    jQuery.post(flex_map.ajax_url, data, function(response) {
      let data = response.data;
      jQuery(_self.element).siblings('.fm-spinner-container').fadeOut();

      if(response.success) {
        let bounds = new google.maps.LatLngBounds();

        if(!data.locations || data.locations.length == 0) {
          let loc = {
            lat: data['origin']['geometry']['location']['lat'],
            lng: data['origin']['geometry']['location']['lng'],
            ID: 'origin',
            title: 'Current Location',
            marker_html: data['origin']['html'],
          };

          var latlng = new google.maps.LatLng(
            parseFloat(loc['lat']),
            parseFloat(loc['lng']));

          _self.create_marker(loc);
          bounds.extend(latlng);
          _self.map.fitBounds(bounds);
        } else {
          for(var i in data['locations']) {
            var loc = data['locations'][i];
            var latlng = new google.maps.LatLng(
              parseFloat(loc['lat']),
              parseFloat(loc['lng']));

            if(latlng.lat() && latlng.lng()){
              _self.create_marker(loc);
              
              bounds.extend(latlng);
            }
          }

          _self.map.fitBounds(bounds);
        }
        
      } else {
        console.error(data.message);
      }
    });
  }

  load_by_latlng() {
    let _self   = this;
    let lat     = (this.element.getAttribute("data-lat") ? this.element.getAttribute("data-lat") : 0);
    let lng     = (this.element.getAttribute("data-lng") ? this.element.getAttribute("data-lng") : 0);
    let zoom    = (parseInt(this.element.getAttribute("data-zoom")) ? parseInt(this.element.getAttribute("data-zoom")) : 10);
    let latlng  = new google.maps.LatLng(lat, lng);

    this.map.setCenter(latlng);
    this.map.setZoom(zoom);

    if(this.element.getAttribute("data-show_marker")) {
      let marker = new google.maps.Marker({
        position: latlng,
        html: '<div id="post-'+this.element.id+'" class="fm-container">'+this.element.getAttribute('data-popup_window_content')+'</div>',
        map: this.map,
        post_id: this.element.id
      });

      let popup_type = this.element.getAttribute("data-popup_type");
      if(popup_type && popup_type != 'none') {

        google.maps.event.addListener(marker, 'click', function() {
          _self.infoWindow.setContent(marker.html);
          _self.infoWindow.open(_self.map, marker);
          _self.infoWindow.setOptions({post_id:marker.post_id});
        });

        if(popup_type == 'load') {
          google.maps.event.trigger(marker, 'click');
        }

        this.markers.push(marker);
      }
    }

    jQuery(_self.element).siblings('.fm-spinner-container').fadeOut();
  }

  create_marker(data) {
    var latlng = new google.maps.LatLng(
      parseFloat(data['lat']),
      parseFloat(data['lng']));

    let marker_options = {
      map: this.map,
      position: latlng,
      title: name,
      post_id: data.ID,
      html: '<div id="post-'+data.ID+'" class="fm-container">'+data.marker_html+'</div>',
    };

    if(data.icon)
      marker_options['icon'] = data.icon;

    let marker = new google.maps.Marker(marker_options);
    let _self = this;

    google.maps.event.addListener(marker, 'click', function() {
      _self.infoWindow.setContent(marker.html);
      _self.infoWindow.open(_self.map, marker);
      _self.infoWindow.setOptions({post_id:marker.post_id});
    });

    this.markers.push(marker);
  }

  clear_markers() {
    this.infoWindow.close();
    for (var i = 0; i < this.markers.length; i++) {

      this.markers[i].setMap(null);
    }
    this.markers = [];
  }
}