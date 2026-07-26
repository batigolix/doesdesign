<?php

/**
 * @file
 * AI generated
 * Theme settings form for Shindo.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter() for system_theme_settings.
 */
function shindo_form_system_theme_settings_alter(array &$form, FormStateInterface $form_state): void {
  $form['shindo_colors'] = [
    '#type' => 'fieldset',
    '#title' => t('Colors'),
    '#description' => t('Brand colors are converted to HSL and injected as CSS custom properties on the &lt;html&gt; element. Derivatives (hover, muted) are calculated in CSS using <code>calc()</code>.'),
  ];

  $colors = [
    'color_primary' => t('Primary color'),
    'color_secondary' => t('Secondary color'),
    'color_accent' => t('Accent color'),
    'color_background' => t('Background color'),
    'color_text' => t('Text color'),
  ];

  foreach ($colors as $key => $title) {
    $form['shindo_colors'][$key] = [
      '#type' => 'color',
      '#title' => $title,
      '#config_target' => "shindo.settings:$key",
    ];
  }
}
