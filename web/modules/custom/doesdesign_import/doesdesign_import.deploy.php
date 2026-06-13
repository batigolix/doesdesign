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

/**
 * Copy still-missing original image files from the D7 source tree.
 *
 * # Purpose
 * After the Drupal 7 → 11 migration some images referenced from inline HTML
 * (in node body / block content body) point at files that were never
 * transferred to the D11 public files directory. They surface in watchdog as
 * "page not found" warnings for URLs under `sites/doesdesign.nl/files/`.
 * The legacy D7 codebase is still present on the production server, so the
 * fix is to copy the missing originals across.
 *
 * # Why not in `_rewrite_d7_file_paths`
 * Rewriting paths (DB updates) is reversible; copying files (FS writes) is
 * not. Splitting the two hooks keeps each focused and lets a future operator
 * roll back the path rewrite without also reverting copied bytes.
 *
 * # Why only originals, no style derivatives
 * Watchdog typically logs the *derivative* URL (e.g. `…/styles/large/public/
 * Foo.jpg`), but Drupal regenerates derivatives on demand from the original.
 * Copying the originals is enough — the next request for the derivative URL
 * will trigger ImageStyle::transformDimensions() and the derivative gets
 * recreated. Hence the list below contains only original relative paths.
 *
 * # Path arithmetic
 * `DRUPAL_ROOT` is the absolute path to `web/`. The user-supplied relative
 * paths walk the on-disk layout like so:
 *
 *   web/ (= DRUPAL_ROOT)
 *     ../    → production checkout root
 *       ../  → doesdesign-project (shared dir lives here as `files/`)
 *         ../ → public_html (D7 site is doesdesign7/ here)
 *
 * So from `web/`:
 *   - Source root: `../../../doesdesign7/sites/doesdesign.nl/files/`
 *   - Target root: `../../files/`  (shared dir symlinked from
 *     `web/sites/default/files` by the production deploy workflow)
 *
 * # Idempotency
 * Each iteration skips when the target already exists. Re-running the hook
 * (or re-deploying) is therefore a no-op once the bytes are in place.
 *
 * # How to replicate this for a future cleanup round
 *
 * Over time new "does not exist" entries may appear in watchdog for other
 * historical content. To handle a future batch:
 *
 *   1. Copy this entire function and rename it, e.g.
 *      `doesdesign_import_deploy_copy_missing_d7_images_round2`.
 *      Deploy hooks run in file order, and only un-applied hooks fire — so
 *      a renamed copy will execute exactly once on the next deploy.
 *   2. Refresh the file list. From local DDEV:
 *
 *        ddev drush @live watchdog:show --count=500 --format=json > wd.json
 *        jq -r '.[] | .message + " | " + .location' wd.json \
 *          | grep -oE 'sites/doesdesign\.nl/files/[^" )?]+' \
 *          | sort -u \
 *          | sed -E 's|sites/doesdesign\.nl/files/styles/[^/]+/public/||' \
 *          | sed -E 's|sites/doesdesign\.nl/files/||' \
 *          | python3 -c "import sys,urllib.parse;[print(urllib.parse.unquote(l.strip())) for l in sys.stdin if l.strip()]" \
 *          | sort -u
 *
 *      Paste the result into `$missing_files`.
 *   3. Leave the source/target relative paths alone unless the on-disk layout
 *      changes (D7 archive moved or shared dir relocated).
 *   4. Test locally by seeding a canary file (see the deploy.php README in
 *      the module if present, or the project plan file).
 *
 * # When to remove
 * Once `drush @live ws --count=500` shows zero `sites/doesdesign.nl/files/`
 * 404s for at least one full deploy cycle, both this function and its
 * historical sibling(s) can be deleted along with the path-rewrite hook
 * above. Removing them is safe because deploy hooks are idempotent record-
 * keepers, not stateful migrations.
 *
 * @return string
 *   Human-readable summary printed by `drush deploy` to the deploy log.
 */
function doesdesign_import_deploy_copy_missing_d7_images(): string {
  // List of file paths still missing from the D11 files dir, relative to
  // `sites/doesdesign.nl/files/` on the legacy D7 site. Generated once from
  // live watchdog — see the function docblock for the regeneration recipe.
  // Style derivative URLs (…/styles/<name>/public/…) have been stripped to
  // their originals; Drupal regenerates derivatives on demand.
  $missing_files = [
    'Amfibieën-bril.jpg',
    'Anonymous-hanger-met-bergkristal.jpg',
    'Armband Faces2.jpg',
  ];

  // Source and target *roots*, expressed relative to `web/` (DRUPAL_ROOT)
  // exactly as the user requested. Keeping them relative makes the hook
  // portable: it works in DDEV (where DRUPAL_ROOT is /var/www/html/web) and
  // in production (where it is /home/doesborg/public_html/doesdesign-project/
  // production/web), as long as the on-disk layout in the docblock holds.
  $source_root = DRUPAL_ROOT . '/../../../doesdesign7/sites/doesdesign.nl/files/';
  $target_root = DRUPAL_ROOT . '/../../files/';

  $copied = 0;
  $skipped = 0;
  $missing_source = 0;
  $failed = 0;

  foreach ($missing_files as $relative_path) {
    $source = $source_root . $relative_path;
    $target = $target_root . $relative_path;

    // Idempotency guard: never overwrite an existing file. If a later content
    // edit on production replaced the original, we do not want this hook to
    // silently roll it back to the D7 version on the next re-run.
    if (file_exists($target)) {
      $skipped++;
      continue;
    }

    // The D7 archive may itself be incomplete — record but do not fail.
    if (!file_exists($source)) {
      $missing_source++;
      continue;
    }

    // Preserve subdirectory structure (e.g. `node_images/foo.jpg`) so the
    // file lands at the exact path the rewritten body references expect.
    // 0775 matches the permissions Drupal's File module uses for new public
    // upload directories on this hoster.
    $target_dir = dirname($target);
    if (!is_dir($target_dir) && !mkdir($target_dir, 0775, TRUE) && !is_dir($target_dir)) {
      $failed++;
      continue;
    }

    if (copy($source, $target)) {
      $copied++;
    }
    else {
      $failed++;
    }
  }

  return sprintf(
    'Copied %d, skipped %d (already present), missing in D7 archive: %d, failed: %d.',
    $copied,
    $skipped,
    $missing_source,
    $failed
  );
}
