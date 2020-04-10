/**
 * @file
 * Flickr sidebar block.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.flickr = {
    attach: function (context, settings) {
      var flickr_items = drupalSettings.doesdesign_tools.flickr.flickr_items;

      // Calls Flickr, gets details of recent 20 images of flickr id
      jQuery.getJSON("http://api.flickr.com/services/feeds/photos_public.gne?id=23406248@N05&lang=en-en&format=json&jsoncallback=?", displayImages);
      function displayImages(data) {

        // Constructs the HTML string
        var htmlString = "<ul>";

        // Cycles through our array of Flickr photo details.
        jQuery.each(data.items, function (i, item) {

          // Fetches thumbnails.
          var sourceSquare = (item.media.m).replace("_m.jpg", "_s.jpg");

          // Constructs the individual thumbnail HTML
          htmlString += '<li><a href="' + item.link + '" >';
          htmlString += '<img title="' + item.title + '" src="' + sourceSquare;
          htmlString += '" alt="';
          htmlString += item.title + '" />';
          htmlString += '</a></li>';
          return i < flickr_items;
        });

        // Pops HTML in the #images DIV.
        jQuery('#flickr_images').html(htmlString + "</ul>");
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
