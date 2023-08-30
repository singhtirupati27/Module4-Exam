(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.prodBehavior = {
    attach: function (context, settings) {
      var target = $('.cart-btn', context);
      target.on('click', function () {
        $('.cart-msgs').html('<p>Product added to cart!</p>');
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
