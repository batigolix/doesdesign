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
      'merriweather' => t('Merriweather (serif, elegant)'),
      'playfair-display' => t('Playfair Display (serif, high-contrast)'),
      'nunito' => t('Nunito (rounded sans)'),
      'open-sans' => t('Open Sans (neutral sans)'),
      'montserrat' => t('Montserrat (geometric sans)'),
      'poppins' => t('Poppins (contemporary sans)'),
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
  $form['shindo_colors']['button_color'] = [
    '#type' => 'color',
    '#title' => t('Button-kleur'),
    '#description' => t('Achtergrondkleur voor primaire buttons.'),
    '#config_target' => 'shindo.settings:button_color',
  ];
  $form['shindo_colors']['footer_heading_color'] = [
    '#type' => 'color',
    '#title' => t('Footer kop-kleur'),
    '#description' => t('Kleur voor h1-h6 titels binnen het footer-gebied.'),
    '#config_target' => 'shindo.settings:footer_heading_color',
  ];
  $form['shindo_colors']['accent_color'] = [
    '#type' => 'color',
    '#title' => t('Accent kleur'),
    '#description' => t('Kleur voor links, post accents en overige accents (default: Dopetrope pink).'),
    '#config_target' => 'shindo.settings:accent_color',
  ];

  $form['shindo_background'] = [
    '#type' => 'fieldset',
    '#title' => t('Background'),
  ];
  $form['shindo_background']['background_pattern'] = [
    '#type' => 'select',
    '#title' => t('Achtergrond patroon'),
    '#options' => [
      'bg' => t('Standaard (Dopetrope textuur)'),
      'none' => t('Geen (plat)'),
      'stripes' => t('Strepen'),
      'grid' => t('Raster'),
    ],
    '#config_target' => 'shindo.settings:background_pattern',
  ];
  $form['shindo_background']['background_color'] = [
    '#type' => 'color',
    '#title' => t('Achtergrond kleur'),
    '#description' => t('Kleur onder het patroon.'),
    '#config_target' => 'shindo.settings:background_color',
  ];
}
