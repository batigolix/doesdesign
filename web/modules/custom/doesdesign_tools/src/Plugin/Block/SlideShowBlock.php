<?php

// AI generated.

namespace Drupal\doesdesign_tools\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a slideshow block with promoted object nodes.
 *
 * @Block(
 *  id = "slideshow_block",
 *  admin_label = @Translation("Slideshow"),
 * )
 */
class SlideShowBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'items' => 5,
      'order' => 'DESC',
      'order_property' => 'created',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['items'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of slides'),
      '#default_value' => $config['items'],
      '#options' => array_combine([3, 5, 7, 9, 11], [3, 5, 7, 9, 11]),
    ];

    $form['order_property'] = [
      '#type' => 'select',
      '#title' => $this->t('Sort by'),
      '#default_value' => $config['order_property'],
      '#options' => [
        'created' => $this->t('Creation date'),
        'changed' => $this->t('Date of last change'),
      ],
    ];

    $form['order'] = [
      '#type' => 'select',
      '#title' => $this->t('Sort direction'),
      '#default_value' => $config['order'],
      '#options' => [
        'DESC' => $this->t('Descending'),
        'ASC' => $this->t('Ascending'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['items'] = $form_state->getValue('items');
    $this->configuration['order'] = $form_state->getValue('order');
    $this->configuration['order_property'] = $form_state->getValue('order_property');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $storage = $this->entityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'object')
      ->condition('status', 1)
      ->condition('promote', 1)
      ->sort($config['order_property'], $config['order'])
      ->range(0, (int) $config['items']);

    $nids = $query->execute();
    if (empty($nids)) {
      return [];
    }

    $nodes = $storage->loadMultiple($nids);
    $slides = [];

    foreach ($nodes as $node) {
      if (!$node->hasField('field_media_image') || $node->get('field_media_image')->isEmpty()) {
        continue;
      }

      $media = $node->get('field_media_image')->first()->entity;
      if (!$media || !$media->hasField('field_media_image') || $media->get('field_media_image')->isEmpty()) {
        continue;
      }

      $file = $media->get('field_media_image')->entity;
      if (!$file) {
        continue;
      }

      $slides[] = [
        'image' => [
          '#theme' => 'image_style',
          '#style_name' => 'slider',
          '#uri' => $file->getFileUri(),
          '#alt' => $media->get('field_media_image')->alt ?: $node->label(),
        ],
        'title' => $node->label(),
        'url' => $node->toUrl()->toString(),
      ];
    }

    if (empty($slides)) {
      return [];
    }

    return [
      '#theme' => 'slideshow_block',
      '#slides' => $slides,
      '#attached' => [
        'library' => ['doesdesign_tools/slideshow'],
      ],
    ];
  }

}
