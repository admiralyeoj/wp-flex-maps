var _self = this;

(function( $ ) {
  'use strict';
  
  $( window ).load(function() {
    if (typeof acf == 'undefined') { return; }

    $(document).on('change ready', '.fm-rule-meta-key .acf-input select', function(e) {
      console.log('test');
      update_field_values_on_select_change(e, $);
    });

    $(document).on('click', '.fm-add-row', function(e) {
      $(this).parents('.fm-rule-group').find('div.acf-actions a[data-event="add-row"]').trigger('click');
    });

    acf.addAction('remove', function( $el ) {
      if(!$el)
        return;

      if($el.siblings('.acf-row:not(.acf-clone)').length == 0) {
        let field = acf.getField($el);
        if(field.data.key)
          return;
          
        let parent = field.parent();

        if(parent && parent.$el.hasClass('fm-rule-group')) {
          $(parent.$el).animate({left:20, opacity:"hide"}, 400, function() {
            parent.$el.parent().remove();
          });
        }
      }
    });

  });

})( jQuery );

function remove_repeater_group(e, $) {
  var target = $(e.target);
  var choice = target.val();
}

function update_field_values_on_select_change(e, $) {
  if (_self.request) {
    _self.request.abort();
  }

  let target = $(e.target);
  let choice = target.val();
  
  // get the city select field, and remove all exaisiting choices
  let value_selector = target.closest('.acf-row').find('.fm-rule-meta-value select');
  value_selector.empty();
  
  if (!choice)
    return;
  
  var data = {
    action: 'load_fm_rule_meta_values',
    key: choice
  }
  
  data = acf.prepareForAjax(data);
  
  _self.request = $.ajax({
    url: acf.get('ajaxurl'),
    data: data,
    type: 'post',
    dataType: 'json',
    success: function(json) {
      if (!json['success']) {
        return;
      }
      
      json.data.forEach(function(elem, index) {
        if(elem.value)
          var option = '<option value="'+elem.value+'">'+elem.label+'</option>';
        else
          var option = '<option value="'+elem+'">'+elem+'</option>';

        value_selector.append(option);
      });
    }
  });
  
}