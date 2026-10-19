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
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Create 'jaar' taxonomy terms from unique node__field_year values (ozhi).
 *
 * Reads every DISTINCT value from node__field_year and creates one term in the
 * 'jaar' vocabulary per unique year string. Idempotent: if a term with the
 * same name already exists in 'jaar' it is skipped.
 *
 * Weight strategy: weight = -(year as integer), so that a default
 * ascending-weight sort presents years newest-first (2026 weight -2026,
 * 1990 weight -1990). This matches the portfolio chronology convention.
 */
function doesdesign_tools_post_update_create_jaar_terms(): void {
  // AI generated.
  $connection = \Drupal::database();

  if (!$connection->schema()->tableExists('node__field_year')) {
    return;
  }

  $years = $connection->select('node__field_year', 'y')
    ->distinct()
    ->fields('y', ['field_year_value'])
    ->orderBy('field_year_value', 'ASC')
    ->execute()
    ->fetchCol();

  if (empty($years)) {
    return;
  }

  $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
  $created = 0;

  foreach ($years as $year) {
    // Skip if a term with this name already exists in the 'jaar' vocabulary.
    $existing = $term_storage->loadByProperties([
      'vid' => 'jaar',
      'name' => $year,
    ]);
    if ($existing) {
      continue;
    }

    $term = Term::create([
      'vid' => 'jaar',
      'name' => $year,
      // Negative weight so ascending-weight sort = newest year first.
      'weight' => -(int) $year,
      'langcode' => 'nl',
    ]);
    $term->save();
    $created++;
  }

  \Drupal::logger('doesdesign_tools')->info(
    'Created @count jaar taxonomy terms from unique field_year values (@skipped already existed).',
    [
      '@count' => $created,
      '@skipped' => count($years) - $created,
    ]
  );
}

/**
 * Backfill field_jaar on object nodes from a local snapshot (ozhi).
 *
 * On production the field_year storage was removed via config:import
 * before the backfill was in place, so field_jaar is empty on objects
 * that previously had a field_year value. This hook restores the
 * mapping (nid => year) from a snapshot taken on the local DB after
 * the first-pass backfill, resolved against the live 'jaar' terms by
 * name. Idempotent: skips nodes that already have a field_jaar
 * reference, missing nodes, or years not present in the vocab.
 */
function doesdesign_tools_post_update_backfill_field_jaar(): void {
  // AI generated.
  $mapping = [
    3 => 2012,
    4 => 2001,
    5 => 2012,
    7 => 1992,
    9 => 1992,
    10 => 1990,
    11 => 1992,
    13 => 1992,
    16 => 2010,
    17 => 2017,
    18 => 2006,
    19 => 2012,
    20 => 2014,
    22 => 2001,
    23 => 2007,
    24 => 1992,
    25 => 2004,
    26 => 2004,
    27 => 2004,
    28 => 2012,
    29 => 2010,
    30 => 2004,
    31 => 2004,
    32 => 1999,
    33 => 2004,
    34 => 2004,
    35 => 2004,
    36 => 2004,
    37 => 2015,
    39 => 2005,
    40 => 2005,
    41 => 2012,
    42 => 2005,
    44 => 2005,
    45 => 2005,
    47 => 2005,
    48 => 2007,
    49 => 2005,
    50 => 2010,
    51 => 2005,
    52 => 2006,
    53 => 2009,
    54 => 2009,
    55 => 2006,
    56 => 2006,
    57 => 2008,
    58 => 2006,
    59 => 2006,
    61 => 2006,
    62 => 2006,
    63 => 2006,
    64 => 2006,
    65 => 2007,
    66 => 2007,
    67 => 2007,
    68 => 2008,
    69 => 2008,
    70 => 2008,
    71 => 2008,
    72 => 2008,
    73 => 2008,
    74 => 2009,
    75 => 2009,
    76 => 2009,
    77 => 2009,
    78 => 2009,
    79 => 2009,
    80 => 2012,
    81 => 2009,
    82 => 2009,
    83 => 2010,
    84 => 2010,
    85 => 2010,
    86 => 2009,
    87 => 2009,
    95 => 2010,
    96 => 2010,
    97 => 2010,
    98 => 2010,
    99 => 2010,
    100 => 2009,
    101 => 2009,
    106 => 2012,
    107 => 2011,
    108 => 2011,
    114 => 2012,
    115 => 2012,
    116 => 2012,
    117 => 2012,
    118 => 2012,
    134 => 2012,
    135 => 2013,
    138 => 2014,
    139 => 2013,
    140 => 2013,
    141 => 2013,
    142 => 2013,
    143 => 2013,
    144 => 2013,
    145 => 2013,
    146 => 2013,
    147 => 2013,
    148 => 2013,
    150 => 2013,
    151 => 2013,
    152 => 2013,
    153 => 2013,
    154 => 2014,
    155 => 2014,
    156 => 2014,
    158 => 2014,
    159 => 2014,
    160 => 2014,
    161 => 2014,
    162 => 2014,
    163 => 2014,
    164 => 2014,
    165 => 2018,
    166 => 2015,
    167 => 2015,
    168 => 2015,
    169 => 2015,
    170 => 2015,
    171 => 2015,
    172 => 2015,
    173 => 2015,
    174 => 2015,
    175 => 2015,
    176 => 2015,
    177 => 2015,
    178 => 2015,
    179 => 2015,
    180 => 2015,
    181 => 2016,
    182 => 2016,
    183 => 2016,
    184 => 2016,
    185 => 2016,
    186 => 2016,
    187 => 2016,
    188 => 2016,
    189 => 2016,
    190 => 2016,
    191 => 2016,
    192 => 2017,
    193 => 2017,
    194 => 2017,
    195 => 2017,
    196 => 2017,
    197 => 2017,
    198 => 2017,
    199 => 2017,
    200 => 2018,
    201 => 2017,
    202 => 2018,
    203 => 2018,
    204 => 2018,
    205 => 2018,
    206 => 2019,
    207 => 2019,
    208 => 2019,
    209 => 2019,
    210 => 2019,
    211 => 2019,
    212 => 2020,
    213 => 2020,
    214 => 2020,
    215 => 2020,
    216 => 2020,
    217 => 2020,
    218 => 2020,
    219 => 2020,
    220 => 2020,
    270 => 2021,
    272 => 2021,
    273 => 2021,
    274 => 2021,
  ];

  $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

  // Resolve 'jaar' vocab terms by name once up front.
  $term_cache = [];
  foreach ($term_storage->loadByProperties(['vid' => 'jaar']) as $term) {
    $term_cache[$term->getName()] = (int) $term->id();
  }

  $backfilled = 0;
  $already_set = 0;
  $missing_node = 0;
  $missing_term = 0;

  foreach ($mapping as $nid => $year) {
    $node = $node_storage->load($nid);
    if (!$node instanceof NodeInterface || $node->bundle() !== 'object') {
      $missing_node++;
      continue;
    }
    if (!$node->get('field_jaar')->isEmpty()) {
      $already_set++;
      continue;
    }
    $term_id = $term_cache[(string) $year] ?? NULL;
    if ($term_id === NULL) {
      $missing_term++;
      continue;
    }
    $node->set('field_jaar', ['target_id' => $term_id]);
    $node->setNewRevision(FALSE);
    $node->save();
    $backfilled++;
  }

  \Drupal::logger('doesdesign_tools')->info(
    'field_jaar backfill: @backfilled set, @already already present, @missing_node nodes not found, @missing_term years not in jaar vocab.',
    [
      '@backfilled' => $backfilled,
      '@already' => $already_set,
      '@missing_node' => $missing_node,
      '@missing_term' => $missing_term,
    ]
  );
}

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
