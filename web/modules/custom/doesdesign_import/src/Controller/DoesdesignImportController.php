<?php

namespace Drupal\doesdesign_import\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for doesdesign_import routes.
 */
class DoesdesignImportController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
