<?php

namespace Drupal\doesdesign_tools\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Example: configurable text string' block.
 *
 * @Block(
 *   id = "dd_tools_contact_block",
 *   subject = @Translation("Contact"),
 *   admin_label = @Translation("DD 8 tools: Contact"),
 * )
 */
final class DdToolsContact extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * Constructs a new DdToolsContact block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   The module extension list.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleExtensionList $module_extension_list) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleExtensionList = $module_extension_list;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('extension.list.module'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $socials = [
      [
        'name' => 'Facebook',
        'img' => 'facebook-logo.png',
        'cta' => $this->t('Visit Doesdesign at Facebook'),
        'url' => 'https://www.facebook.com/Doesdesign.nl',
        'class' => 'facebook',
      ],
      [
        'name' => 'Linkedin',
        'img' => 'linkedin-logo.png',
        'cta' => $this->t('Visit Birigit at Linkedin'),
        'url' => 'http://nl.linkedin.com/in/birgitdoesborg',
        'class' => 'linkedin',
      ],
      [
        'name' => 'Twitter',
        'img' => 'twitter-logo.png',
        'cta' => $this->t('Tweet Birigit'),
        'url' => 'http://twitter.com/#!/Doesdesign_nl',
        'class' => 'twitter',
      ],
      [
        'name' => 'YouTube',
        'cta' => $this->t('Visit YouTube page'),
        'img' => 'youtube-logo.png',
        'url' => 'http://www.youtube.com/user/metalartcreations',
        'class' => 'youtube',
      ],
    ];
    $img_path = $this->moduleExtensionList->getPath('doesdesign_tools') . '/images/';
    $build = [];
    $items = [];
    foreach ($socials as $social) {
      $img = [
        '#theme' => 'image',
        '#uri' => $img_path . $social['img'],
        '#title' => $social['cta'],
        '#alt' => $social['name'],
      ];
      $url = Url::fromUri($social['url']);
      $url->setOptions([
        'attributes' => [
          'title' => $social['cta'],
          'class' => [$social['class']],
        ],
      ]);
      $items[] = Link::fromTextAndUrl($img, $url);
    }
    $build['doestxt'] = [
      '#prefix' => '<div class="doestxt">',
      '#suffix' => '</div>',
    ];

    $url = Url::fromUserInput('/about');
    $build['doestxt']['about']['#markup'] = '<div class="about"><strong>' . Link::fromTextAndUrl('Birgit Doesborg', $url)->toString() . '</strong>, Goud- en zilversmid.</div>';
    $url = Url::fromUserInput('/contact');
    $build['doestxt']['contact_link']['#markup'] = '<div class="contact"><strong>' . Link::fromTextAndUrl('Contact', $url)->toString() . '</strong></div>';
    $build['doestxt']['social'] = [
      '#theme' => 'item_list',
      '#items' => $items,
      '#attributes' => ['class' => ['socialist']],
    ];

    $build['#attributes']['class'][] = 'dd-tools-contact-block';

    return $build;
  }

}
