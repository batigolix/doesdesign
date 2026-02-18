<?php

namespace Drupal\doesdesign_import\Plugin\migrate\source;

use Drupal\Core\Database\Database;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;

/**
 * Provides list of D7 files that are in use.
 *
 * Checks if file ID exists in list of field tables, e.g. field_image or
 * filed_document.
 *
 * @MigrateSource(
 *   id = "d7_files_in_use"
 * )
 *
 * Use as follows:
 *
 * @code
 * source:
 *   plugin: d7_files_in_use
 *   fields:
 *     - field_my_image
 *     - field_your_document
 *   body_fields:
 *     - body
 *   inline_file_pattern: 'sites/doesdesign.nl/files/'
 * @endcode
 */
class D7FilesInUse extends SourcePluginBase {

  /**
   * The public file directory path.
   *
   * @var string
   */
  protected $publicPath;

  /**
   * The private file directory path, if any.
   *
   * @var string
   */
  protected $privatePath;

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids = [
      'fid' => [
        'type' => 'integer',
      ],
    ];
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return "d7_files_in_use";
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Fetches image data from the database.
   */
  private function fetchItems() {
    $connection = Database::getConnection('default', 'migrate');
    $fields = $this->configuration['fields'];
    $items = [];
    foreach ($fields as $field) {
      $query = $connection->select('field_data_' . $field, 'fdf');
      $query->addField('fdf', $field . '_fid', 'id');
      $query->leftJoin('file_managed', 'fm', 'fdf.' . $field . '_fid = fm.fid');

      if ($this->configuration['parent_entity_type'] === 'node') {
        $query->leftJoin('node', 'n', 'fdf.entity_id = n.nid');
        $query->condition('n.status', 1);
      }

      // @todo fabriquate query for files that have other parent entity types
      // such as paragrpahs.

      $query->fields('fm');
      $results = $query->execute();
      foreach ($results as $record) {
        $items[] = $record;
      }
    }
    // Also discover files referenced inline in body fields.
    if (!empty($this->configuration['body_fields']) && !empty($this->configuration['inline_file_pattern'])) {
      $inline_items = $this->fetchInlineBodyFiles($connection);
      // Merge, deduplicating by fid.
      $existing_fids = [];
      foreach ($items as $item) {
        $existing_fids[$item->fid] = TRUE;
      }
      foreach ($inline_items as $item) {
        if (!isset($existing_fids[$item->fid])) {
          $items[] = $item;
          $existing_fids[$item->fid] = TRUE;
        }
      }
    }

    return $items;
  }

  /**
   * Discovers files referenced inline in body field HTML.
   *
   * Scans body field values for src attributes matching the configured
   * inline_file_pattern, extracts relative paths, and looks them up in
   * file_managed.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The D7 database connection.
   *
   * @return array
   *   Array of file_managed record objects.
   */
  private function fetchInlineBodyFiles($connection) {
    $pattern = $this->configuration['inline_file_pattern'];
    $body_fields = $this->configuration['body_fields'];
    $items = [];
    $found_uris = [];

    foreach ($body_fields as $field_name) {
      $table = 'field_data_' . $field_name;
      $column = $field_name . '_value';

      // Only fetch rows that contain the file path pattern.
      $query = $connection->select($table, 'fdf');
      $query->addField('fdf', $column, 'body_value');
      $query->condition($column, '%' . $connection->escapeLike($pattern) . '%', 'LIKE');

      if ($this->configuration['parent_entity_type'] === 'node') {
        $query->leftJoin('node', 'n', 'fdf.entity_id = n.nid');
        $query->condition('n.status', 1);
      }

      $results = $query->execute();
      foreach ($results as $record) {
        // Extract all file paths from src attributes.
        $regex = '#(?:' . preg_quote($pattern, '#') . ')([^"\'<>\s]+)#';
        if (preg_match_all($regex, $record->body_value, $matches)) {
          foreach ($matches[1] as $relative_path) {
            $relative_path = urldecode($relative_path);
            $uri = 'public://' . $relative_path;
            if (!isset($found_uris[$uri])) {
              $found_uris[$uri] = TRUE;
            }
          }
        }
      }
    }

    // Look up all discovered URIs in file_managed.
    if (!empty($found_uris)) {
      $uris = array_keys($found_uris);
      $fm_query = $connection->select('file_managed', 'fm');
      $fm_query->fields('fm');
      $fm_query->condition('fm.uri', $uris, 'IN');
      $fm_results = $fm_query->execute();
      foreach ($fm_results as $record) {
        $items[] = $record;
      }
    }

    return $items;
  }

  /**
   * Initializes the iterator with the source data.
   *
   * @return \Iterator
   *   An iterator containing the data for this source.
   */
  protected function initializeIterator() {
    $items = $this->fetchItems();
    $rows = [];
    $public_path = $this->configuration['constants']['d7_public_path'];
    $private_path = $this->configuration['constants']['d7_private_path'];

    if ($items) {
      foreach ($items as $item) {

        // Provides a filepath based on uri.
        $filepath = str_replace(['public:/', 'private:/'], [
          $public_path,
          $private_path,
        ], $item->uri);

        // Populates rows.
        $rows[] = [
          'fid' => $item->fid,
          'filename' => $item->filename,
          'uri' => $item->uri,
          'timestamp' => $item->timestamp,
          'filepath' => $filepath,
          'status' => $item->status,
          'uid'=>$item->uid,
        ];
      }
    }
    return new \ArrayIterator($rows);
  }

}
