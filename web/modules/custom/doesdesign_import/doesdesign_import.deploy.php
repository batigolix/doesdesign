<?php

/**
 * @file
 * Deploy hooks for doesdesign_import module.
 *
 * AI generated.
 */

declare(strict_types=1);

use Drupal\Core\Database\Database;

/**
 * Rewrite D7 file paths (sites/doesdesign.nl/files/) to D11 (sites/default/files/).
 */
function doesdesign_import_deploy_rewrite_d7_file_paths(): string {
  $connection = Database::getConnection();
  $tables = [
    'node__body',
    'node_revision__body',
    'block_content__body',
    'block_content_revision__body',
  ];
  $total = 0;
  foreach ($tables as $table) {
    if (!$connection->schema()->tableExists($table)) {
      continue;
    }
    $total += $connection->update($table)
      ->expression('body_value', "REPLACE(body_value, :old, :new)", [
        ':old' => 'sites/doesdesign.nl/files/',
        ':new' => 'sites/default/files/',
      ])
      ->condition('body_value', '%sites/doesdesign.nl/files%', 'LIKE')
      ->execute();
  }
  return sprintf('Rewrote D7 file paths in %d row(s).', $total);
}
