$(function() {
  var $confirmLinks = $('.confirm-link');
  $confirmLinks.on('click', function() {
    if (window.confirm($(this).attr('data-confirm-msg'))) {
      return true;
    }

    return false;
  });
});