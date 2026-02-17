<?php

// AI generated

namespace Drupal\doesdesign_import\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Builds a date-based destination URI for a file using a Unix timestamp.
 *
 * Takes a Unix timestamp and a filename and returns a destination URI
 * in the format public://YYYY-MM/filename, matching the pattern used
 * by Drupal's media file organisation.
 *
 * @MigrateProcessPlugin(
 *   id = "timestamp_to_date_path"
 * )
 *
 * Use as follows:
 *
 * @code
 * destination_uri:
 *   plugin: timestamp_to_date_path
 *   source:
 *     - timestamp
 *     - filename
 * @endcode
 */
class TimestampToDatePath extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   *
   * Expects $value to be an array with two elements:
   *   0 - Unix timestamp (integer) used to derive the YYYY-MM subfolder.
   *   1 - Filename (string) appended after the date subfolder.
   *
   * Returns a stream-wrapper URI such as public://2015-03/myfile.jpg.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    [$timestamp, $filename] = $value;

    // Format the timestamp as YYYY-MM to build the date subfolder.
    $date_subfolder = date('Y-m', (int) $timestamp);

    return 'public://' . $date_subfolder . '/' . $filename;
  }

}
