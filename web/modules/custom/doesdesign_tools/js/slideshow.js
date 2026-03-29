// AI generated.
(function (Drupal, once) {
  Drupal.behaviors.slideshow = {
    attach: function (context) {
      once('slideshow', '.splide', context).forEach(function (el) {
        new Splide(el, {
          type: 'fade',
          rewind: true,
          autoplay: true,
          interval: 3000,
          speed: 500,
          pauseOnHover: true,
          pagination: true,
          arrows: false,
        }).mount();
      });
    }
  };
})(Drupal, once);
