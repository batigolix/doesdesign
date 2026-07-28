<?php

/**
 * @file
 * Theme settings form for Shindo.
 *
 * AI generated.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter() for system_theme_settings.
 */
function shindo_form_system_theme_settings_alter(array &$form, FormStateInterface $form_state): void {
  $form['shindo_typography'] = [
    '#type' => 'fieldset',
    '#title' => t('Typography'),
  ];
  $form['shindo_typography']['body_font'] = [
    '#type' => 'select',
    '#title' => t('Body font'),
    '#description' => t('Applies to all body text on the frontend.'),
    '#options' => [
      'source-sans-pro' => t('Source Sans Pro (default, Dopetrope original)'),
      'lora' => t('Lora (serif, editorial)'),
      'roboto' => t('Roboto (modern sans-serif)'),
      'system' => t('System font (native OS)'),
    ],
    '#config_target' => 'shindo.settings:body_font',
  ];

  $form['shindo_colors'] = [
    '#type' => 'fieldset',
    '#title' => t('Colors'),
  ];
  $form['shindo_colors']['primary_color'] = [
    '#type' => 'color',
    '#title' => t('Hoofdkleur'),
    '#description' => t('Kleur voor titels (h1-h6), donkere knoppen en accents.'),
    '#config_target' => 'shindo.settings:primary_color',
  ];
}
