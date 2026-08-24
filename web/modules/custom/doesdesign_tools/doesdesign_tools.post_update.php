<?php

/**
 * @file
 * Post-update hooks for doesdesign_tools.
 *
 * AI generated.
 */

declare(strict_types=1);

use Drupal\block_content\Entity\BlockContent;
use Drupal\node\NodeInterface;
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
 * This post_update hook marks 8002 as executed when the vocabularies are
 * already present, so subsequent deploys will not abort.
 */
function doesdesign_tools_post_update_skip_fontyourface_8002(): void {
  $registry = \Drupal::service('update.update_hook_registry');
  $current = $registry->getInstalledVersion('fontyourface');
  if ($current > 0 && $current < 8002 && Vocabulary::load('font_classification')) {
    $registry->setInstalledVersion('fontyourface', 8002);
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
function doesdesign_tools_post_update_migrate_orphan_text_formats(): void {
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

/**
 * Migrate promote=1 to field_show_in_banner=1 on object nodes (kcc7).
 *
 * The homepage banner (bead xgx) originally selected objects via
 * node.promote. Bead kcc7 replaces that with a dedicated boolean field
 * field_show_in_banner so the semantics no longer overload core promote.
 *
 * This hook copies promote=1 to field_show_in_banner=1 for every object
 * node that has the flag set. Idempotent: subsequent runs will only touch
 * nodes still missing the new value.
 */
function doesdesign_tools_post_update_migrate_promote_to_banner_flag(): void {
  $connection = \Drupal::database();
  if (!$connection->schema()->tableExists('node__field_show_in_banner')) {
    return;
  }

  $nids = $connection->select('node_field_data', 'n')
    ->fields('n', ['nid'])
    ->condition('type', 'object')
    ->condition('promote', 1)
    ->execute()
    ->fetchCol();

  if (!$nids) {
    return;
  }

  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $migrated = 0;
  foreach ($nids as $nid) {
    $node = $storage->load($nid);
    if (!$node instanceof NodeInterface || !$node->hasField('field_show_in_banner')) {
      continue;
    }
    if ((bool) $node->get('field_show_in_banner')->value === TRUE) {
      continue;
    }
    $node->set('field_show_in_banner', TRUE);
    $node->setNewRevision(FALSE);
    $node->save();
    $migrated++;
  }

  \Drupal::logger('doesdesign_tools')->info(
    'Migrated @count object nodes from promote=1 to field_show_in_banner=1.',
    ['@count' => $migrated]
  );
}

/**
 * Create site_branding block_content entity + wire block placement (xh7d).
 *
 * Replaces core system.branding_block with a custom block_content bundle
 * 'site_branding' so the site-owner can edit logo/naam/slogan via the block
 * UI instead of /admin/config/system/site-information. The bundle, fields,
 * form/view displays and the disabled shindo_branding placement live in
 * config/sync. The custom shindo_site_branding_custom block placement
 * references the entity by UUID, so on fresh environments (stage/live) the
 * entity must be created and the placement re-pointed to the local UUID.
 *
 * Idempotent: if an entity of type site_branding already exists (or if the
 * placement already points to a valid entity), it does nothing.
 */
function doesdesign_tools_post_update_create_site_branding_entity(): void {
  $storage = \Drupal::entityTypeManager()->getStorage('block_content');
  $existing = $storage->loadByProperties(['type' => 'site_branding']);
  if ($existing) {
    return;
  }
  $entity = BlockContent::create([
    'type' => 'site_branding',
    'info' => 'Site branding',
    'field_name' => \Drupal::config('system.site')->get('name'),
    'field_slogan' => \Drupal::config('system.site')->get('slogan'),
  ]);
  $entity->save();

  $block = \Drupal::entityTypeManager()->getStorage('block')
    ->load('shindo_site_branding_custom');
  if ($block) {
    $block->set('plugin', 'block_content:' . $entity->uuid());
    $settings = $block->get('settings');
    $settings['id'] = 'block_content:' . $entity->uuid();
    $block->set('settings', $settings);
    $block->save();
  }

  \Drupal::logger('doesdesign_tools')->info(
    'Created site_branding block_content entity @uuid and wired block placement.',
    ['@uuid' => $entity->uuid()]
  );
}
