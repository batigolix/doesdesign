<?php

// AI generated

namespace Drupal\doesdesign_import\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Translates D7 internal paths to D11 paths using migration lookup.
 *
 * Converts node/NID and taxonomy/term/TID paths from D7 IDs to D11 IDs.
 * Skips the row if no mapping is found.
 *
 * @MigrateProcessPlugin(
 *   id = "alias_path_lookup"
 * )
 */
class AliasPathLookup extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  protected MigrateLookupInterface $migrateLookup;
  protected LoggerInterface $logger;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrateLookupInterface $migrate_lookup,
    LoggerInterface $logger,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migrateLookup = $migrate_lookup;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('migrate.lookup'),
      $container->get('logger.factory')->get('doesdesign_import'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $path = trim($value);

    // Handle node/NID paths.
    if (preg_match('#^node/(\d+)$#', $path, $matches)) {
      $d7_nid = (int) $matches[1];
      foreach (['page', 'article', 'object'] as $migration_id) {
        try {
          $result = $this->migrateLookup->lookup([$migration_id], [$d7_nid]);
          if (!empty($result[0]['nid'])) {
            return '/node/' . $result[0]['nid'];
          }
        }
        catch (\Exception $e) {
          // Continue to next migration.
        }
      }
      throw new MigrateSkipRowException(sprintf('D7 node/%d not found in any node migration.', $d7_nid));
    }

    // Handle taxonomy/term/TID paths.
    if (preg_match('#^taxonomy/term/(\d+)$#', $path, $matches)) {
      $d7_tid = (int) $matches[1];
      try {
        $result = $this->migrateLookup->lookup(['term'], [$d7_tid]);
        if (!empty($result[0]['tid'])) {
          return '/taxonomy/term/' . $result[0]['tid'];
        }
      }
      catch (\Exception $e) {
        // Fall through.
      }
      throw new MigrateSkipRowException(sprintf('D7 taxonomy/term/%d not found in term migration.', $d7_tid));
    }

    // Other paths (e.g., user/*, search/*): pass through with leading slash.
    return '/' . ltrim($path, '/');
  }

}
