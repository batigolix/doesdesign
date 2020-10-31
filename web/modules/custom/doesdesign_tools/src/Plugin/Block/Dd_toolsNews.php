<?php

/**
 * @file
 * Contains \Drupal\dd8_tools\Plugin\Block\Dd_toolsNews .
 */

namespace Drupal\dd8_tools\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a 'Example: configurable text string' block.
 *
 * @Block(
 *   id = "News",
 *   subject = @Translation("News"),
 *   admin_label = @Translation("DD tools: News")
 * )
 */
class Dd_toolsNews extends BlockBase {

  /**
   * Overrides \Drupal\Core\Block\BlockBase::defaultConfiguration().
   */
  public function defaultConfiguration() {
    return array(
      'label' => t("DD tools: News"),
      'content' => t('Default news content'),
      'cache' => array(
        'max_age' => 3600,
        'contexts' => array(
          'cache_context.user.roles',
        ),
      ),
    );
  }

  /**
   * Overrides \Drupal\Core\Block\BlockBase::blockForm().
   */
  public function blockForm($form, FormStateInterface $form_state) {

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();
    $nr_items = isset($config['nr_items']) ? $config['nr_items'] : 3;
    $form['nr_items'] = array(
      '#type' => 'select',
      '#options' => array(2 => 2, 5 => 5, 10 => 10),
      '#description' => t('This number of items will be shown in the news block'),
      '#title' => t('Number of items'),
      '#default_value' => $nr_items,
    );
    return $form;
  }

  /**
   * Overrides \Drupal\Core\Block\BlockBase::blockSubmit().
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('nr_items', $form_state->getValue('nr_items'));
  }

  /**
   * Implements \Drupal\Core\Block\BlockBase::blockBuild().
   */
  public function build() {

    $config = $this->getConfiguration();
    $nr_items = isset($config['nr_items']) ? $config['nr_items'] : 3;

    // Fetches the slideshow nodes.
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'article')
      ->range(0, $nr_items);
    $nids = $query->execute();

    $items = array();
    foreach ($nids as $nid) {
      $node = \Drupal::service('entity_type.manager')->getStorage('node')->load($nid);

// @todo figure out fetchign values from node object
//kint($node);
//      $node_view = entity_view($node, 'hero_teaser');
//      kint($node_view);

      $items[] = array(
//        '#markup' => drupal_render($node_view),
        '#markup' => 'sfdsdf',
        '#wrapper_attributes' => array('class' => array('slide')),
      );
    }

    $elements = array();
//    if ($node_title_list = node_title_list($result)) {
//      $elements['forum_list'] = $node_title_list;
////      $elements['forum_more'] = array(
////        '#type' => 'more_link',
////        '#url' => Url::fromRoute('forum.index'),
////        '#attributes' => array('title' => $this->t('Read the latest forum topics.')),
////      );
//    }
    $build = array();
//    $build['elements']['#markup'] = $elements;
    $build['items']['#markup'] = $nr_items;
    $build['elements']['#markup'] = 'placeholder';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $nr_items = $form_state->getValue('nr_items');

    if (!is_numeric($nr_items)) {
      $form_state->setErrorByName('nr_items', t('Needs to be an interger'));
    }
  }

}
