<?php

/**
 * @file
 * Contains \Drupal\dd8_tools\Plugin\Block\Dd_toolsSlideshow .
 */

namespace Drupal\dd8_tools\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;

//use Drupal\Core\Cache\Cache;
//use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\Annotation\EntityType;

//use Drupal\Core\Annotation\Translation;
//use Drupal\my_module\MyEntityInterface;

/**
 * Provides a 'Example: configurable text string' block.
 *
 * @Block(
 *   id = "slideshow",
 *   subject = @Translation("Slideshow"),
 *   admin_label = @Translation("DD 8 tools: Slideshow")
 * )
 */
class Dd_toolsSlideshow extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {

    $config = $this->getConfiguration();
    $nr_items = isset($config['slideshow_items']) ? $config['slideshow_items'] : 3;
//    $order = isset($config['slideshow_order']) ? $config['slideshow_order'] : 'created';

    // Fetches the slideshow nodes.
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'object')
 //     ->sort($order)
      ->range(0, $nr_items);
    $nids = $query->execute();
    $items = array();
    foreach ($nids as $nid) {
      $node = entity_load('node', $nid);
      $node_view = entity_view($node, 'hero_teaser');
      $items[] = array(
        '#markup' => drupal_render($node_view),
        '#wrapper_attributes' => array('class' => array('slide')),
      );
    }
    $build['show'] = array(
      '#theme' => 'item_list',
      '#items' => $items,
      '#attributes' => array('class' => array('slideshow', 'rslides')),
    );
    $build['#attributes']['class'][] = 'slideshow-block';
    $build['#attached']['library'][] = 'dd8_tools/slideshow';
    $build['#attached']['library'][] = 'dd8_tools/responsiveslides';


//    $build['asfasf']['#markup'] = 'efkjqekjfkjah aekjkjwevkj HSHAJHDJAH';

    return $build;

  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

    // Add a form field to the existing block configuration form.
    $form['slideshow_items'] = array(
      '#type' => 'select',
      '#title' => t('Number of items'),
      '#options' => array(
        3 => 3,
        5 => 5,
        7 => 7,
        9 => 9,
        11 => 11,
        13 => 13,
        15 => 15,
        17 => 17,
      ),
      '#description' => t('Number of items that will be shown in the slideshow.'),
      '#default_value' => isset($config['slideshow_items']) ? $config['slideshow_items'] : '',
    );
    $form['slideshow_order'] = array(
      '#type' => 'select',
      '#title' => t('Order by'),
      '#options' => array(
        'created ' => t('Creation date'),
        'changed' => t('Date of last change')
      ),
      '#description' => t('By which date the items will be ordered in the slideshow block'),
      '#default_value' => isset($config['slideshow_order']) ? $config['slideshow_order'] : '',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('slideshow_items', $form_state->getValue('slideshow_items'));
    $this->setConfigurationValue('slideshow_order', $form_state->getValue('slideshow_order'));
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $slideshow_items = $form_state->getValue('slideshow_items');

    if (!is_numeric($slideshow_items)) {
      $form_state->setErrorByName('slideshow_items', t('Needs to be an interger'));
    }
  }

  //
  // $nodes = \Drupal::entityManager()->getStorage('node')->loadMultiple($nids);
// Or a use the static loadMultiple method on the entity class:
//    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
//dd($nodes);
// And then you can view/build them all together:
//    $build['sdcsdcs'] = \Drupal::entityManager()->viewMultiple($nodes, 'teaser');


//    $render_controller = \Drupal::entityManager()->getViewBuilder($entity->getEntityTypeId());
//    $render_output = $render_controller->view($entity, $view_mode, $langcode);


//dsm('asadsa');
//    $nodes = \Drupal::entityManager()->getStorage('node')->loadMultiple($nids);
//// Or a use the static loadMultiple method on the entity class:
////    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
////dd($nodes);
//    // And then you can view/build them all together:
//    $build['sdvsdv']['#markup'] = '<span>' . $this->t('Powered by <a href=":poweredby">Drupal</a>', array(':poweredby' => 'https://www.drupal.org')) . '</span>';
//
//    $build['nodes'] = \Drupal::entityManager()->viewMultiple($nodes, 'hero_teaser');

//

}
//
///**
//   * Overrides \Drupal\Core\Block\BlockBase::defaultConfiguration().
//   */
//  public function defaultConfiguration() {
//    return array(
//      'label' => t("Slideshow"),
//      'content' => t(''),
//      'cache' => array(
//        'max_age' => 3600,
//        'contexts' => array(
//          'cache_context.user.roles',
//        ),
//      ),
//    );
//  }
//
////    $form['slideshow_order'] = array(
////      '#type' => 'select',
////      '#title' => t('Order'),
////      '#options' => array('ASC ' => t('Ascending'), 'DESC' => t('Descending')),
////      '#description' => t('In what order the items will be shown in the slideshow block'),
////      '#default_value' => $config['slideshow_order'],
////    );
////    $form['slideshow_order_prop'] = array(
////      '#type' => 'select',
////      '#title' => t('Order by'),
////      '#options' => array(
////        'created ' => t('Creation date'),
////        'changed' => t('Date of last change')
////      ),
////      '#description' => t('By which date the items will be ordered in the slideshow block'),
////      '#default_value' => $config['slideshow_order_prop'],
////    );
////
////    return $form;
//  }
//
//
//  /**
//   * {@inheritdoc}
//   */
//  public function blockSubmit($form, FormStateInterface $form_state) {
////    $this->configuration['slideshow_items'] = $form_state->getValue('slideshow_items');
//  }
//
//
//  /**
//   * Implements \Drupal\Core\Block\BlockBase::blockBuild().
//   */
//  public function build() {
////    $slideshow_items = $this->configuration['slideshow_items'];
////
////
////    $query = \Drupal::entityQuery('node')
////      ->condition('status', 1)
////      ->condition('type', 'object');
//////      ->condition('changed', REQUEST_TIME, '<')
//////      ->condition('title', 'cat', 'CONTAINS')
//////      ->condition('field_tags.entity.name', 'cats');
////
////    $nids = $query->execute();
////dd('hola');
////    dd($nids);
////dsm('asadsa');
////    $nodes = \Drupal::entityManager()->getStorage('node')->loadMultiple($nids);
////// Or a use the static loadMultiple method on the entity class:
////    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
////
////// And then you can view/build them all together:
////    $build = \Drupal::entityManager()->viewMultiple($nodes, 'teaser');
////
//////
//////    $nodes = entity_load_multiple('node', $nids);
//////
//////    foreach ($nodes as $node) {
//////      $entity_view = entity_view($node, 'teaser');
//////      $items[] = array(
//////        'data' => drupal_render($entity_view),
//////        'class' => array('slide')
//////      );
//////    }
//////
//////    $view_builder = \Drupal::entityManager()->getViewBuilder('node');
//////
//////    $full_output = $view_builder->view($entity);
//////
//////    $rss_output = $view_builder->view($entity, 'rss');
//////
//////    $build = array();
//////
//////
//////    $build['list'] = [
//////      '#theme' => 'item_list',
//////      '#items' => $items,
//////    ];
//////
////
//////    $build = array(
//////      '#markup' => theme('item_list', array(
//////        'items' => $items,
//////        'title' => NULL,
//////        'type' => 'ul',
//////        'attributes' => array('class' => array('rslides')),
//////      ))
//////    );
////dd($build);
////
//////    $build['container']['#markup'] = '<div id="flickr_images">sdfsdf </div>';
////
////    $build['#attached']['library'][] = 'dd8_tools/slideshow';
////    $build['#attached']['library'][] = 'dd8_tools/responsiveslides';
////    $build['#attached']['drupalSettings']['dd8_tools']['slideshow']['slideshow_items'] = $slideshow_items;
////
////    return $build;
//  }
//
//}
