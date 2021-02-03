<?php

namespace Drupal\dd8_tools\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

// Use Drupal\my_module\MyEntityInterface;.

/**
 * Provides a 'Example: configurable text string' block.
 *
 * @Block(
 *   id = "dd_tools_contact_block",
 *   subject = @Translation("Contact"),
 *   admin_label = @Translation("DD 8 tools: Contact"),
 * )
 */
class Dd_toolsContact extends BlockBase {

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
    $img_path = drupal_get_path('theme', 'shakudo') . '/images/';
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
    $doespic = [
      '#prefix' => '<div class="doespic">',
      '#suffix' => '</div>',
      '#theme' => 'image',
      '#uri' => $img_path . 'does.jpg',
      '#width' => '75',
      '#height' => '100',
      '#attributes' => [
    // 'class' => array(
    //          'doespic'
    //        ),
        'title' => 'Birgit Doesborg',
        'alt' => 'Foto Birgit Doesborg',
      ],
    ];
    // $build['doespic'] = $doespic;
    $build['doestxt'] = [
      '#prefix' => '<div class="doestxt">',
      '#suffix' => '</div>',
    ];

    $url = Url::fromUserInput('/about');
    $build['doestxt']['about']['#markup'] = '<div class="about"><strong>' . Link::fromTextAndUrl('Birgit Doesborg', $url) . '</strong>, Goud- en zilversmid.</div>';
    $url = Url::fromUserInput('/contact');
    $build['doestxt']['contact_link']['#markup'] = '<div class="contact"><strong>' . Link::fromTextAndUrl('Contact', $url) . '</strong></div>';
    $build['doestxt']['social'] = [
      '#theme' => 'item_list',
      '#items' => $items,
      '#attributes' => ['class' => ['socialist']],
    ];

    $build['#attributes']['class'][] = 'dd-tools-contact-block';

    return $build;
  }

}
