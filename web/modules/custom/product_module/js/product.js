(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.prodBehavior = {
    attach: function (context, settings) {
      var target = $('.cart-btn', context);
      target.on('click', function () {
        $('.cart-msg').html('Product added to cart!');
      });
    },
  };
})(jQuery, Drupal, drupalSettings);

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.productBehavior = {
    attach: function (context, settings) {
      var target = $('.buy-btn', context);
      target.on('click', function () {
        location.href = '/thank-you';
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
