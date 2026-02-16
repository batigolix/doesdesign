<?php

namespace Drupal\doesdesign_import\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Fetches value from the D7 database.
 *
 * Value is fetched from the D7 database using the field collection
 * ID and the field name contained within that field collection.
 *
 * @MigrateProcessPlugin(
 *   id = "get_term_name"
 * )
 * Use as follows:
 *
 * @code
 * source_base_path:
 *   plugin: get_term_name
 *   source: tid
 * @endcode
 */
class GetTermName extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $fetched_value = $this->fetchDatabaseValue($value);
    if ($fetched_value) {
      return $fetched_value;
    }
  }

  /**
   * Fetches personid value from the D7 database.
   */
  private function fetchDatabaseValue($value) {
    $connection = Database::getConnection('default', 'migrate');
    $query = $connection->select('taxonomy_term_data', 'ttd');
    $query->condition('ttd.tid', $value['tid']);
    $query->addField('ttd', 'name');
    $results = $query->execute();
    return $results->fetchField();
  }

}
