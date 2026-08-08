<?php

/**
 * @file
 * Deploy hooks for doesdesign_tools.
 *
 * AI generated.
 */

declare(strict_types=1);

use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Skip fontyourface_update_8002 if vocabs already exist (bead u6mj).
 *
 * The fontyourface update hook 8002 tries to create vocabularies + fields
 * (font_classification, languages_supported, font_tags, font_foundry,
 * font_designer and their field_* attachments) that already ship via
 * config/sync in this project. Because `drush deploy` runs `cim` before
 * `updb`, the config is imported first, the update hook then detects
 * existing objects and aborts the deploy.
 *
 * This deploy hook runs after cim/updb (during drush deploy:hook) and
 * marks 8002 as executed when the vocabularies are already present, so
 * subsequent deploys will not abort.
 */
function doesdesign_tools_deploy_skip_fontyourface_8002(): void {
  $registry = \Drupal::service('update.update_hook_registry');
  $current = $registry->getInstalledVersion('fontyourface');
  if ($current > 0 && $current < 8002 && Vocabulary::load('font_classification')) {
    $registry->setInstalledVersion('fontyourface', 8002);
  }
}

/**
 * Drop orphaned inline_block_usage table when layout_builder is uninstalled.
 *
 * When a live DB is imported into a local/dev environment where
 * layout_builder has been uninstalled but its schema table (inline_block_usage)
 * lingers, the next drush deploy fails during updb with:
 *   SchemaException: Table 'inline_block_usage' already exists
 * ...because Drupal tries to (re)install layout_builder via config import and
 * hook_schema() attempts to create the table anew.
 *
 * This deploy hook removes the orphaned table when layout_builder is not
 * enabled, keeping the schema state consistent for subsequent deploys.
 * When layout_builder IS enabled (the normal case), this is a no-op.
 */
function doesdesign_tools_deploy_drop_orphan_inline_block_usage(): void {
  $module_handler = \Drupal::service('module_handler');
  if ($module_handler->moduleExists('layout_builder')) {
    return;
  }
  $db_schema = \Drupal::database()->schema();
  if ($db_schema->tableExists('inline_block_usage')) {
    $db_schema->dropTable('inline_block_usage');
  }
}

/**
 * Migrate orphan basic_html / restricted_html content (m2gh, 9y64).
 *
 * The formats filter.format.basic_html and filter.format.restricted_html have
 * been removed from config (see beads g9f0/b6eo). On environments where the
 * config removal is imported for the first time (stage, live), existing text
 * field values still reference the removed formats. Drupal falls back to
 * plain_text rendering for orphan format references — visually broken markup.
 *
 * This hook scans every *_format column in the DB and rewrites orphan
 * references to 'full_html'. Idempotent: safe to run repeatedly (updates 0
 * rows once migration is complete).
 */
function doesdesign_tools_deploy_migrate_orphan_text_formats(): void {
  $connection = \Drupal::database();
  $schema = $connection->schema();
  $orphan_formats = ['basic_html', 'restricted_html'];

  $tables = $connection->query(
    "SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME LIKE :suffix",
    [':suffix' => '%\_format']
  )->fetchAll();

  $total = 0;
  foreach ($tables as $row) {
    if (!$schema->tableExists($row->TABLE_NAME)) {
      continue;
    }
    $affected = $connection->update($row->TABLE_NAME)
      ->fields([$row->COLUMN_NAME => 'full_html'])
      ->condition($row->COLUMN_NAME, $orphan_formats, 'IN')
      ->execute();
    $total += (int) $affected;
  }

  \Drupal::logger('doesdesign_tools')->info(
    'Migrated @count orphan text-format references (basic_html/restricted_html) to full_html.',
    ['@count' => $total]
  );
}
