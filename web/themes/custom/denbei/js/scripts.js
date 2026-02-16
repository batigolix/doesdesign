/**
 * @file
 * Custom JavaScript behaviors for the Denbei theme.
 * AI generated
 */
(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.denbeiFirstParagraph = {
    attach: function (context) {
      $(once('denbei-first-paragraph', '.field--name-body p:first-child', context)).addClass('first');
    }
  };
})(jQuery, Drupal, once);
