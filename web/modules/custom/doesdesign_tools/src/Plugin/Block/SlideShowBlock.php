<?php

namespace Drupal\doesdesign_tools\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a slideshow block with promoted object nodes.
 *
 * @Block(
 *  id = "slideshow_block",
 *  admin_label = @Translation("Slideshow"),
 * )
 */
final class SlideShowBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   *
   * @param array<string, mixed> $configuration
   *   Plugin configuration.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   *
   * @return array<string, mixed>
   *   The default block configuration.
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
   *
   * @param array<string, mixed> $form
   *   The parent form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array<string, mixed>
   *   The augmented block configuration form.
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
   *
   * @param array<string, mixed> $form
   *   The block form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);
    $this->configuration['items'] = $form_state->getValue('items');
    $this->configuration['order'] = $form_state->getValue('order');
    $this->configuration['order_property'] = $form_state->getValue('order_property');
  }

  /**
   * {@inheritdoc}
   *
   * @return array<string, mixed>
   *   The block render array, or [] when no slides are available.
   */
  public function build() {
    $nodes = $this->loadPromotedNodes();
    if (empty($nodes)) {
      return [];
    }

    $slides = [];
    foreach ($nodes as $node) {
      $slide = $this->buildSlide($node);
      if ($slide !== NULL) {
        $slides[] = $slide;
      }
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

  /**
   * Loads promoted object nodes according to the block configuration.
   *
   * @return \Drupal\node\NodeInterface[]
   *   The loaded nodes keyed by nid, or [] if none match.
   */
  private function loadPromotedNodes(): array {
    $config = $this->getConfiguration();
    $storage = $this->entityTypeManager->getStorage('node');

    $nids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'object')
      ->condition('status', 1)
      ->condition('promote', 1)
      ->sort($config['order_property'], $config['order'])
      ->range(0, (int) $config['items'])
      ->execute();

    if (empty($nids)) {
      return [];
    }

    return $storage->loadMultiple($nids);
  }

  /**
   * Builds a single slide render array from a node, or NULL if unusable.
   *
   * @param mixed $node
   *   Candidate node entity (typed loosely to allow the isinstance check).
   *
   * @return array<string, mixed>|null
   *   The slide render array, or NULL when required media is missing.
   */
  private function buildSlide($node): ?array {
    if (!$node instanceof NodeInterface) {
      return NULL;
    }
    $media = $this->getFirstReferencedMedia($node);
    if ($media === NULL) {
      return NULL;
    }
    $file = $this->getMediaFile($media);
    if ($file === NULL) {
      return NULL;
    }

    return [
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

  /**
   * Returns the first Media referenced from the node's field_media_image.
   */
  private function getFirstReferencedMedia(NodeInterface $node): ?MediaInterface {
    if (!$node->hasField('field_media_image') || $node->get('field_media_image')->isEmpty()) {
      return NULL;
    }
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem<\Drupal\media\MediaInterface> $media_field */
    $media_field = $node->get('field_media_image')->first();
    $media = $media_field->entity;
    return $media instanceof MediaInterface ? $media : NULL;
  }

  /**
   * Returns the underlying File entity referenced from a media entity.
   */
  private function getMediaFile(MediaInterface $media): ?FileInterface {
    if (!$media->hasField('field_media_image') || $media->get('field_media_image')->isEmpty()) {
      return NULL;
    }
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem<\Drupal\file\FileInterface> $file_field */
    $file_field = $media->get('field_media_image')->first();
    $file = $file_field->entity;
    return $file instanceof FileInterface ? $file : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'node_list:object',
      'media_list:image',
    ]);
  }

}
