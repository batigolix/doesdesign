<?php

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
final class MenuLinkUri extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The migrate lookup service.
   *
   * @var \Drupal\migrate\MigrateLookupInterface
   */
  protected MigrateLookupInterface $migrateLookup;

  /**
   * Logger for migration diagnostics.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a MenuLinkUri plugin.
   *
   * @param array<string, mixed> $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\migrate\MigrateLookupInterface $migrate_lookup
   *   The migrate lookup service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   */
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
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array<string, mixed> $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
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

    if (preg_match('#^node/(\d+)$#', $path, $matches)) {
      return $this->transformNodePath((int) $matches[1]);
    }

    if (preg_match('#^taxonomy/term/(\d+)$#', $path, $matches)) {
      return $this->transformTermPath((int) $matches[1]);
    }

    if (strtolower($path) === 'contact') {
      return 'internal:/contact';
    }

    // Everything else: treat as internal path.
    return 'internal:/' . ltrim($path, '/');
  }

  /**
   * Resolves a D7 node/NID path to a D11 entity:node URI.
   *
   * @param int $d7_nid
   *   The D7 node id.
   *
   * @return string
   *   The resolved URI, or a fallback internal path if the lookup fails.
   */
  private function transformNodePath(int $d7_nid): string {
    foreach (['page', 'article', 'object'] as $migration_id) {
      $nid = $this->lookupDestinationId($migration_id, $d7_nid, 'nid');
      if ($nid !== NULL) {
        return 'entity:node/' . $nid;
      }
    }
    $this->logger->notice(
      'MenuLinkUri: D7 node/@nid not found in any node migration.',
      ['@nid' => $d7_nid]
    );
    return 'internal:/node/' . $d7_nid;
  }

  /**
   * Resolves a D7 taxonomy/term/TID path to a D11 entity:taxonomy_term URI.
   *
   * @param int $d7_tid
   *   The D7 term id.
   *
   * @return string
   *   The resolved URI, or a fallback internal path if the lookup fails.
   */
  private function transformTermPath(int $d7_tid): string {
    $tid = $this->lookupDestinationId('term', $d7_tid, 'tid');
    if ($tid !== NULL) {
      return 'entity:taxonomy_term/' . $tid;
    }
    return 'internal:/taxonomy/term/' . $d7_tid;
  }

  /**
   * Looks up a destination id in a migration mapping table.
   *
   * @param string $migration_id
   *   The migration id to consult.
   * @param int $source_id
   *   The D7 source id.
   * @param string $key
   *   The destination key ("nid", "tid", …).
   *
   * @return string|int|null
   *   The resolved destination id, or NULL when not found or on error.
   */
  private function lookupDestinationId(string $migration_id, int $source_id, string $key) {
    try {
      $result = $this->migrateLookup->lookup([$migration_id], [$source_id]);
    }
    catch (\Exception $e) {
      return NULL;
    }
    return !empty($result[0][$key]) ? $result[0][$key] : NULL;
  }

}
