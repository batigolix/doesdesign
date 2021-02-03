<?php

namespace Drupal\doesdesign_tools\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Example: configurable text string' block.
 *
 * @Block(
 *   id = "flickr",
 *   subject = @Translation("Flickr"),
 *   admin_label = @Translation("Doesdesign tools: Flickr")
 * )
 */
class Flickr extends BlockBase {

  /**
   * Overrides \Drupal\Core\Block\BlockBase::blockForm().
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

    // Add a form field to the existing block configuration form.
    $form['flickr_items'] = [
      '#type' => 'select',
      '#title' => t('Number of items'),
      '#options' => [
        10 => 10,
        12 => 12,
        15 => 15,
        16 => 16,
        18 => 18,
        20 => 20,
      ],
      '#description' => t('Number of items that will be shown in the slideshow.'),
      '#default_value' => isset($config['flickr_items']) ? $config['flickr_items'] : '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('flickr_items', $form_state->getValue('flickr_items'));
  }

  /**
   * Implements \Drupal\Core\Block\BlockBase::blockBuild().
   */
  public function build() {
    $config = $this->getConfiguration();
    $flickr_items = isset($config['flickr_items']) ? $config['flickr_items'] : 12;
    $build = [];
    $build['container']['#markup'] = '<div id="flickr_images"></div>';
    $build['#attached']['library'][] = 'doesdesign_tools/flickr';
    $build['#attached']['drupalSettings']['doesdesign_tools']['flickr']['flickr_items'] = $flickr_items;
    $build['#attributes']['class'][] = 'doesdesign-tools-flickr';
    return $build;
  }

}
