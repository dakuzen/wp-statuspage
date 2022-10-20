var statuspage = new function(){
  this.init = function(){
    statuspage.initTooltip();
  }
  this.initTooltip = function(){
    jQuery('.statuspage-app').tooltip({
      position: {my: 'left+20 top+25', at: 'left top'},
      classes: {'ui-tooltip': 'ui-tooltip ui-tooltip-statuspage'},
      items:'[data-tooltip]',
      content:function(){
        let elm=jQuery(this);
        let tip = elm.data('tooltip');
        if (tip != "") {
          if (/^[A-Za-z0-9\_]$/.test(tip) && elm.find(tip).length) {
            return jQuery(tip).html();
          }
          return tip;
        }
        return elm.find('.tooltip').html();
      }
    });
  }
  this.openSubscribeDialog = function(el){
    this.closeSubscribeDialog();
    let modal = jQuery('.statuspage-app.statuspage-subscribe-modal').clone();
    let modalWidth = 420;
    if (modal.length) {
      modal.dialog({
        title: modal.find('h3').text(),
        modal: true,
        width: (jQuery(window).width() < modalWidth ? '100%' : modalWidth),
        close: function() { jQuery(this).remove(); }
      });
    }
    return false;
  }
  this.closeSubscribeDialog = function(){
    let dialog = jQuery('.ui-dialog .ui-dialog-content');
    if (dialog.length) dialog.dialog('close');
  }
  this.postSubscribeForm = function(el){
    let form = jQuery(el);
    let modal = jQuery(el).closest('.statuspage-app');
    form.find('input,button').attr('disabled', true);
    jQuery.ajax({
      type: 'POST',
      url: form.attr('action'),
      data: {
        '_wpnonce': form.find('#_wpnonce').val(),
        'subscribeEmail': form.find('#subscribeEmail').val()
      },
      beforeSend: function(){
        modal.find('.statuspage-subscribe-response').remove();
      },
      complete: function(){
      },
      success: function(res){
        jQuery(modal).html(res);return;
        jQuery(res).insertBefore(form);
        form.find('input,button').attr('disabled', false);
      },
      dataType: 'html'
      });
    return false;
  }
}
jQuery(statuspage.init);