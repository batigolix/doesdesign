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

  $form['shindo_layout'] = [
    '#type' => 'fieldset',
    '#title' => t('Layout'),
    '#description' => t('Applied as body classes and read by the SCSS layout partials.'),
  ];
  $form['shindo_layout']['container_width'] = [
    '#type' => 'radios',
    '#title' => t('Container width'),
    '#options' => [
      'narrow' => t('Narrow (960px)'),
      'default' => t('Default (1200px)'),
      'wide' => t('Wide (1400px)'),
    ],
    '#config_target' => 'shindo.settings:container_width',
  ];
  $form['shindo_layout']['sidebar_position'] = [
    '#type' => 'radios',
    '#title' => t('Sidebar position'),
    '#options' => [
      'left' => t('Left'),
      'right' => t('Right'),
      'none' => t('No sidebar (full-width content)'),
    ],
    '#config_target' => 'shindo.settings:sidebar_position',
  ];
  $form['shindo_layout']['show_hero'] = [
    '#type' => 'checkbox',
    '#title' => t('Show hero on the front page'),
    '#config_target' => 'shindo.settings:show_hero',
  ];
}
