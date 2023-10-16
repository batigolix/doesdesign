<?php

namespace Drupal\doesdesign_import\Plugin\migrate\source;

use Drupal\Core\Database\Database;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;

/**
 * Provides list of D7 images.
 *
 * Images in D7 are stored in image fields, but they must become media items in
 * D9. This source plugin cycles through the defined image  fields and fetches
 * file ID, alt and title texts.
 *
 * @MigrateSource(
 *   id = "d7_image"
 * )
 *
 * Use as follows:
 *
 * @code
 * source:
 *   plugin: d7_image
 *   fields:
 *     - field_my_image
 *     - field_your_image
 * @endcode
 */
class D7Image extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids = [
      'id' => [
        'type' => 'integer',
        'unsigned' => FALSE,
        'size' => 'big',
      ],
    ];
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'id' => $this->t('Image ID'),
      'alt' => $this->t('Alt text'),
      'title' => $this->t('Title'),
      'filename' => $this->t('Filename'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return "d7_image";
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
      $query->fields('fdf');
      $query->addField('fdf', $field . '_fid', 'fid');
      $query->leftJoin('file_managed', 'fm', 'fdf.' . $field . '_fid = fm.fid');
      $query->leftJoin('node', 'n', 'fdf.entity_id = n.nid');
      $query->condition('n.status', 1);
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
    if ($items) {
      foreach ($items as $item) {
        $rows[$item->fid] = [
          'id' => $item->fid,
          'filename' => $item->filename,
          'uid' => $item->uid,
          'timestamp'=>$item->timestamp,
        ];
      }
    }
    return new \ArrayIterator($rows);
  }

}
