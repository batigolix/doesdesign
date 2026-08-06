<?php

namespace Drupal\doesdesign_import\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fetches a value from State.
 *
 * An example could be the location of the D7 files folder, which can
 * vary per environment. It can be stored in state.
 * Example using drush to set a state value:
 *   drush @self state:set aventus_import.d7_files_folder '/home/example-d7'
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
final class GetStateValue extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a GetStateValue plugin.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $state_name = $this->configuration['state_name'];
    return $this->state->get($state_name);
  }

}
