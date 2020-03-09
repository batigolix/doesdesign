<?php

namespace Drupal\doesdesign_tools\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ContactFormBlock' block.
 *
 * @Block(
 *  id = "contact_form_block",
 *  admin_label = @Translation("Contact form block"),
 * )
 */
class ContactFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'contact_form_block';
     $build['contact_form_block']['#markup'] = 'Implement ContactFormBlock.';

    return $build;
  }

}
