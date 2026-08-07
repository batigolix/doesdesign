/**
 * @file
 * Custom JS for object page.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.object = {
    attach: function () {
      $("#dam_return a").click(function () {
        var value = drupalSettings.ask;
        var input = $('#edit-subject');
        input.val(value);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
