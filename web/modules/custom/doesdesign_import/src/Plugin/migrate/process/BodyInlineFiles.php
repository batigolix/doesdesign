<?php

// AI generated

namespace Drupal\doesdesign_import\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Replaces inline D7 file paths in body HTML with D11 file URLs.
 *
 * Scans body HTML for src attributes referencing files under
 * sites/doesdesign.nl/files/, looks each file up in the D7 migrate database
 * and the file migration map, and rewrites the path to the D11 public URL.
 * Paths that cannot be resolved are left unchanged and a notice is logged.
 *
 * @MigrateProcessPlugin(
 *   id = "body_inline_files"
 * )
 *
 * Use as follows:
 *
 * @code
 * body/value:
 *   plugin: body_inline_files
 *   source: body
 * @endcode
 */
class BodyInlineFiles extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The migrate lookup service.
   *
   * @var \Drupal\migrate\MigrateLookupInterface
   */
  protected MigrateLookupInterface $migrateLookup;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a BodyInlineFiles process plugin instance.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\migrate\MigrateLookupInterface $migrate_lookup
   *   The migrate lookup service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel for doesdesign_import.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrateLookupInterface $migrate_lookup,
    EntityTypeManagerInterface $entity_type_manager,
    FileUrlGeneratorInterface $file_url_generator,
    LoggerInterface $logger,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migrateLookup = $migrate_lookup;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUrlGenerator = $file_url_generator;
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
      $container->get('entity_type.manager'),
      $container->get('file_url_generator'),
      $container->get('logger.factory')->get('doesdesign_import'),
    );
  }

  /**
   * {@inheritdoc}
   *
   * Expects $value to be the raw body HTML string.
   *
   * Returns the body HTML with inline D7 file paths replaced by D11 URLs.
   * Paths that cannot be resolved are left unchanged.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($value)) {
      return $value;
    }

    // Match src and href attributes that contain a sites/doesdesign.nl/files/
    // path. Handles optional leading slash, URL-encoded characters (%xx) and
    // both single- and double-quoted attributes.
    $pattern = '#((?:src|href)=["\'])([^"\']*(?:%2F|/)sites(?:%2F|/)doesdesign\.nl(?:%2F|/)files(?:%2F|/)[^"\']+)(["\'])#i';

    $body = preg_replace_callback($pattern, function (array $matches) {
      $attr_open  = $matches[1];
      $original   = $matches[2];
      $attr_close = $matches[3];

      $replacement = $this->resolveFilePath($original);

      return $attr_open . $replacement . $attr_close;
    }, $value);

    // Return original value when preg_replace_callback fails.
    return $body ?? $value;
  }

  /**
   * Resolves a D7 inline file path to a D11 public URL.
   *
   * Returns the original path when any resolution step fails.
   *
   * @param string $original_path
   *   The raw path found inside a src attribute, possibly URL-encoded.
   *
   * @return string
   *   The resolved D11 URL, or $original_path when resolution fails.
   */
  protected function resolveFilePath(string $original_path): string {
    // Decode any percent-encoded characters so we can parse the path cleanly.
    $decoded_path = urldecode($original_path);

    // Extract the relative path after sites/doesdesign.nl/files/.
    // The leading slash before "sites" is optional.
    if (!preg_match('#sites/doesdesign\.nl/files/(.+)$#', $decoded_path, $path_matches)) {
      $this->logger->notice(
        'BodyInlineFiles: could not extract relative path from "@path".',
        ['@path' => $original_path]
      );
      return $original_path;
    }

    $relative_path = $path_matches[1];
    $d7_uri        = 'public://' . $relative_path;

    // Look up the file in the D7 migrate database.
    $d7_fid = $this->findD7Fid($d7_uri);
    if ($d7_fid === NULL) {
      // Fallback: for files not in file_managed (e.g. legacy u2/ uploads),
      // rewrite the path to the D11 public files directory directly.
      $this->logger->notice(
        'BodyInlineFiles: no D7 file_managed record found for URI "@uri", using direct path rewrite.',
        ['@uri' => $d7_uri, '@path' => $original_path]
      );
      $encoded = implode('/', array_map('rawurlencode', explode('/', $relative_path)));
      return '/sites/default/files/' . $encoded;
    }

    // Look up the D11 file ID via the migration map.
    $d11_fid = $this->findD11Fid($d7_fid);
    if ($d11_fid === NULL) {
      $this->logger->notice(
        'BodyInlineFiles: D7 fid @fid not found in the file migration map (URI "@uri").',
        ['@fid' => $d7_fid, '@uri' => $d7_uri]
      );
      return $original_path;
    }

    // Load the D11 file entity and generate its public URL.
    $d11_url = $this->generateD11FileUrl($d11_fid, $original_path);

    return $d11_url;
  }

  /**
   * Queries the D7 migrate database to find a fid by URI.
   *
   * @param string $uri
   *   The D7 public URI, e.g. public://some/file.jpg.
   *
   * @return int|null
   *   The D7 fid, or NULL when not found.
   */
  protected function findD7Fid(string $uri): ?int {
    try {
      $connection = Database::getConnection('default', 'migrate');
      $fid = $connection->select('file_managed', 'fm')
        ->fields('fm', ['fid'])
        ->condition('fm.uri', $uri)
        ->execute()
        ->fetchField();

      return ($fid !== FALSE) ? (int) $fid : NULL;
    }
    catch (\Exception $e) {
      $this->logger->notice(
        'BodyInlineFiles: error querying D7 file_managed for URI "@uri": @message.',
        ['@uri' => $uri, '@message' => $e->getMessage()]
      );
      return NULL;
    }
  }

  /**
   * Uses the migration map to find the D11 file ID for a given D7 fid.
   *
   * @param int $d7_fid
   *   The D7 file ID.
   *
   * @return int|null
   *   The D11 file ID, or NULL when not found in the migration map.
   */
  protected function findD11Fid(int $d7_fid): ?int {
    try {
      $result = $this->migrateLookup->lookup(['file'], [$d7_fid]);

      if (!empty($result[0]['fid'])) {
        return (int) $result[0]['fid'];
      }

      return NULL;
    }
    catch (\Exception $e) {
      $this->logger->notice(
        'BodyInlineFiles: error during migration lookup for D7 fid @fid: @message.',
        ['@fid' => $d7_fid, '@message' => $e->getMessage()]
      );
      return NULL;
    }
  }

  /**
   * Loads a D11 file entity and returns its absolute public URL.
   *
   * @param int $d11_fid
   *   The D11 file entity ID.
   * @param string $original_path
   *   The original path, used in log messages on failure.
   *
   * @return string
   *   The generated absolute URL, or $original_path when the entity cannot
   *   be loaded or the URL cannot be generated.
   */
  protected function generateD11FileUrl(int $d11_fid, string $original_path): string {
    try {
      /** @var \Drupal\file\FileInterface|null $file */
      $file = $this->entityTypeManager->getStorage('file')->load($d11_fid);

      if ($file === NULL) {
        $this->logger->notice(
          'BodyInlineFiles: D11 file entity @fid could not be loaded (original path: "@path").',
          ['@fid' => $d11_fid, '@path' => $original_path]
        );
        return $original_path;
      }

      return $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
    }
    catch (\Exception $e) {
      $this->logger->notice(
        'BodyInlineFiles: error generating URL for D11 fid @fid: @message.',
        ['@fid' => $d11_fid, '@message' => $e->getMessage()]
      );
      return $original_path;
    }
  }

}
