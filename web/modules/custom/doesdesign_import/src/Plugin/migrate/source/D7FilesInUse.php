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
