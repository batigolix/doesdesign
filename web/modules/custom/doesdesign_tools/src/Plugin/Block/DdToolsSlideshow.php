<?php

namespace Drupal\dd8_tools\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Example: configurable text string' block.
 *
 * @Block(
 *   id = "slideshow",
 *   subject = @Translation("Slideshow"),
 *   admin_label = @Translation("DD 8 tools: Slideshow")
 * )
 */
class DdToolsSlideshow extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $config = $this->getConfiguration();
    $nr_items = isset($config['slideshow_items']) ? $config['slideshow_items'] : 3;

    // Fetches the slideshow nodes.
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'object')
      ->range(0, $nr_items);
    $nids = $query->execute();
    $items = [];
    foreach ($nids as $nid) {
      $node = \Drupal::service('entity_type.manager')->getStorage('node')->load($nid);
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder($node->getEntityTypeId());
      $node_view = $view_builder->view($node, 'hero_teaser');
      $items[] = [
        '#markup' => \Drupal::service('renderer')->render($node_view),
        '#wrapper_attributes' => ['class' => ['slide']],
      ];
    }
    $build['show'] = [
      '#theme' => 'item_list',
      '#items' => $items,
      '#attributes' => ['class' => ['slideshow', 'rslides']],
    ];
    $build['#attributes']['class'][] = 'slideshow-block';
    $build['#attached']['library'][] = 'dd8_tools/slideshow';
    $build['#attached']['library'][] = 'dd8_tools/responsiveslides';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

    // Add a form field to the existing block configuration form.
    $form['slideshow_items'] = [
      '#type' => 'select',
      '#title' => t('Number of items'),
      '#options' => [
        3 => 3,
        5 => 5,
        7 => 7,
        9 => 9,
        11 => 11,
        13 => 13,
        15 => 15,
        17 => 17,
      ],
      '#description' => t('Number of items that will be shown in the slideshow.'),
      '#default_value' => isset($config['slideshow_items']) ? $config['slideshow_items'] : '',
    ];
    $form['slideshow_order'] = [
      '#type' => 'select',
      '#title' => t('Order by'),
      '#options' => [
        'created ' => t('Creation date'),
        'changed' => t('Date of last change'),
      ],
      '#description' => t('By which date the items will be ordered in the slideshow block'),
      '#default_value' => isset($config['slideshow_order']) ? $config['slideshow_order'] : '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('slideshow_items', $form_state->getValue('slideshow_items'));
    $this->setConfigurationValue('slideshow_order', $form_state->getValue('slideshow_order'));
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $slideshow_items = $form_state->getValue('slideshow_items');

    if (!is_numeric($slideshow_items)) {
      $form_state->setErrorByName('slideshow_items', t('Needs to be an interger'));
    }
  }

}
