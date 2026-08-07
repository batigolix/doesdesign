<?php

/**
 * @file
 * Deploy hooks for doesdesign_tools.
 *
 * AI generated
 */

declare(strict_types=1);

use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Skip fontyourface_update_8002 if vocabs already exist (bead u6mj).
 *
 * fontyourface's update hook 8002 tries to create vocabularies + fields
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
