/**
 * @file
 * Flickr sidebar block.
 */
(function ($, Drupal, drupalSettings, once) {
  'use strict';
  Drupal.behaviors.flickr = {
    attach: function (context, settings) {
      once('flickr', '#flickr_images', context).forEach(function (element) {
        var flickr_items = drupalSettings.doesdesign_tools.flickr.flickr_items;

        $.ajax({
          url: 'https://api.flickr.com/services/feeds/photos_public.gne',
          data: {
            id: '23406248@N05',
            lang: 'en-en',
            format: 'json'
          },
          dataType: 'jsonp',
          jsonpCallback: 'jsonFlickrFeed',
          success: function (data) {
            var htmlString = '<ul>';
            $.each(data.items, function (i, item) {
              var sourceSquare = (item.media.m).replace('_m.jpg', '_s.jpg');
              htmlString += '<li><a href="' + item.link + '">';
              htmlString += '<img title="' + item.title + '" src="' + sourceSquare;
              htmlString += '" alt="' + item.title + '" />';
              htmlString += '</a></li>';
              return i < flickr_items;
            });
            $(element).html(htmlString + '</ul>');
          }
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings, once);
