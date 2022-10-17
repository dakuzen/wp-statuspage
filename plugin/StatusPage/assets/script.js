statuspage = new function(){
  this.subscribe = function(el){
    if (!jQuery('#statuspage_subscribe_dialog').length) {
      jQuery.get('');
    }
    return false;
  }
}