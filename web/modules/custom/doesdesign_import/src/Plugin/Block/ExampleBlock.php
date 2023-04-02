<?php

namespace Drupal\doesdesign_import\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "doesdesign_import_example",
 *   admin_label = @Translation("Example"),
 *   category = @Translation("doesdesign_import")
 * )
 */
class ExampleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    return $build;
  }

}
