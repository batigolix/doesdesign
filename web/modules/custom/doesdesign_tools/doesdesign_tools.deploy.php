<?php

/**
 * @file
 * Deploy hooks for doesdesign_tools.
 *
 * AI generated.
 */

declare(strict_types=1);

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
 * Rename footer_col_N region to footer_rowX_colY on block config (00lg).
 *
 * Safety net voor stage/live: als block config alleen in DB staat (via
 * placement in UI, niet in config/sync), zorgt deze hook dat block
 * entities verwezen naar footer_col_N automatisch worden bijgewerkt
 * naar footer_rowX_colY.
 * Idempotent: skip als new region al gezet, log het aantal migrated.
 */
function doesdesign_tools_deploy_rename_footer_regions(): void {
  $map = [
    'footer_col_1' => 'footer_row1_col1',
    'footer_col_2' => 'footer_row1_col2',
    'footer_col_3' => 'footer_row2_col1',
    'footer_col_4' => 'footer_row2_col2',
    'footer_col_5' => 'footer_row2_col3',
  ];
  $storage = \Drupal::entityTypeManager()->getStorage('block');
  $migrated = 0;
  foreach ($storage->loadMultiple() as $block) {
    $current = $block->getRegion();
    if (isset($map[$current])) {
      $block->setRegion($map[$current]);
      $block->save();
      $migrated++;
    }
  }
  \Drupal::logger('doesdesign_tools')->info(
    'Renamed @count block placements from footer_col_N to footer_rowX_colY.',
    ['@count' => $migrated]
  );
}

/**
 * Install missing fontyourface entity schemas (9hp4).
 *
 * The fontyourface module was enabled at schema 8005 without its two
 * content-entity tables (font, font_display) actually being created —
 * likely because the enable happened during a partial D7->D11 migration
 * where entity install hooks did not fire. The Drupal status report
 * flags this as "font entity type is not installed".
 *
 * This hook checks whether the entity types are registered in
 * entity.definitions.installed and installs them via the entity
 * definition update manager if not. Idempotent: safe to re-run.
 */
function doesdesign_tools_deploy_install_fontyourface_entities(): void {
  $module_handler = \Drupal::service('module_handler');
  if (!$module_handler->moduleExists('fontyourface')) {
    return;
  }
  $definition_update = \Drupal::entityDefinitionUpdateManager();
  $type_manager = \Drupal::entityTypeManager();
  $installed = 0;
  foreach (['font', 'font_display'] as $type_id) {
    if ($definition_update->getEntityType($type_id)) {
      continue;
    }
    if (!$type_manager->hasDefinition($type_id)) {
      continue;
    }
    $definition_update->installEntityType($type_manager->getDefinition($type_id));
    $installed++;
  }
  \Drupal::logger('doesdesign_tools')->info(
    'Installed @count missing fontyourface entity type(s) (font / font_display).',
    ['@count' => $installed]
  );
}
