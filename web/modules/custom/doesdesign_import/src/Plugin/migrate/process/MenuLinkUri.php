<?php

// AI generated

namespace Drupal\doesdesign_import\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Converts D7 menu link paths to D11 URIs.
 *
 * Handles node/NID paths (with migration lookup), external doesdesign.nl URLs
 * (stripped to internal paths), and special D7 paths like 'voorpagina'.
 *
 * @MigrateProcessPlugin(
 *   id = "menu_link_uri"
 * )
 */
class MenuLinkUri extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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

    // Strip doesdesign.nl domain from external URLs pointing to the same site.
    $path = preg_replace('#^https?://(www\.)?doesdesign\.nl/#', '', $path);

    // Handle D7 front page aliases.
    if ($path === '<front>' || $path === 'voorpagina' || $path === '') {
      return 'internal:/';
    }

    // Handle node/NID paths — look up D11 nid via migration.
    if (preg_match('#^node/(\d+)$#', $path, $matches)) {
      $d7_nid = (int) $matches[1];
      foreach (['page', 'article', 'object'] as $migration_id) {
        try {
          $result = $this->migrateLookup->lookup([$migration_id], [$d7_nid]);
          if (!empty($result[0]['nid'])) {
            return 'entity:node/' . $result[0]['nid'];
          }
        }
        catch (\Exception $e) {
          // Continue to next migration.
        }
      }
      $this->logger->notice(
        'MenuLinkUri: D7 node/@nid not found in any node migration.',
        ['@nid' => $d7_nid]
      );
      return 'internal:/node/' . $d7_nid;
    }

    // Handle taxonomy/term/TID paths.
    if (preg_match('#^taxonomy/term/(\d+)$#', $path, $matches)) {
      $d7_tid = (int) $matches[1];
      try {
        $result = $this->migrateLookup->lookup(['term'], [$d7_tid]);
        if (!empty($result[0]['tid'])) {
          return 'entity:taxonomy_term/' . $result[0]['tid'];
        }
      }
      catch (\Exception $e) {
        // Fall through.
      }
      return 'internal:/taxonomy/term/' . $d7_tid;
    }

    // Handle contact path (case-insensitive).
    if (strtolower($path) === 'contact') {
      return 'internal:/contact';
    }

    // Everything else: treat as internal path.
    return 'internal:/' . ltrim($path, '/');
  }

}
