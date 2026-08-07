<?php

declare(strict_types=1);

namespace Drupal\doesdesign_tools\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Renders the homepage without node content.
 *
 * The homepage layout is composed entirely of blocks and regions
 * (banner, intro, portfolio, footer). This controller intentionally
 * returns an empty render array so page--front.html.twig can drive
 * the visual composition through regions rather than a node body.
 */
class HomeController extends ControllerBase {

  /**
   * Empty render output — blocks/regions provide the homepage content.
   *
   * @return array<string, mixed>
   *   The render array with only cache metadata.
   */
  public function render(): array {
    return [
      '#cache' => [
        'contexts' => ['url.path'],
        'tags' => ['node_list:object'],
      ],
    ];
  }

}
