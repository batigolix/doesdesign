<?php

namespace Drupal\doesdesign_import\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Fetches a value from State.
 *
 * An example could be the location of the D7 files folder, which can
 * vary per environment. It can be stored in state.
 * Example using drush to set a state value: drush @self state:set aventus_import.d7_files_folder '/home/um-studenten-intranet-d7'                                                                        ✘ 1 feature/migration-setup ⬆ ✚ ✖ ✱ ◼
 *
 * @MigrateProcessPlugin(
 *   id = "get_state_value"
 * )
 * Use as follows:
 *
 * @code
 * source_base_path:
 *   plugin: get_state_value
 *   state_name: aventus_import.d7_files_folder
 * @endcode
 */
class GetStateValue extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $state_name = $this->configuration['state_name'];
    $state = \Drupal::service('state');
    $d7_files_folder = $state->get($state_name);
    return $d7_files_folder;
  }

}
