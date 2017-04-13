/**
 * @file
 * Polyfill for HTML5 time input.
 */

(function ($, Modernizr, Drupal) {

  "use strict";

  /**
   * Attach timepicker fallback on time elements.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior. Accepts in `settings.time` an object listing
   *   elements to process, keyed by the HTML ID of the form element containing
   *   the human-readable value. Each element is an timepicker settings object.
   * @prop {Drupal~behaviorDetach} detach
   *   Detach the behavior destroying timepickers on effected elements.
   */
  Drupal.behaviors.time = {
    attach: function (context, settings) {
      var $context = $(context);
      // Skip if time are supported by the browser.
      if (Modernizr.inputtypes.time === true) {
        return;
      }
      $context.find('input[type=time]').once('timePicker').each(function () {
        var $input = $(this);
        var timepickerSettings = {};

        if ($input.attr('step')) {
          timepickerSettings.step = parseInt($input.attr('step')) / 60;
        }
        if (!$input.attr('required')) {
          timepickerSettings.noneOption = true;

        }
        if ($input.attr('min')) {
          timepickerSettings.minTime = $input.attr('min');
        }
        if ($input.attr('max')) {
          timepickerSettings.maxTime = $input.attr('max');
        }
        timepickerSettings.show24Hours = true;
        $input.timeEntry(timepickerSettings);
      });
    },
    detach: function (context, settings, trigger) {
      if (trigger === 'unload') {
        $(context).find('input[type=time]').findOnce('timePicker').timeEntry('destroy');
      }
    }
  };

})(jQuery, Modernizr, Drupal);
