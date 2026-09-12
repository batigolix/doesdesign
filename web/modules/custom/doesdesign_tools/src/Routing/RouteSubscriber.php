<?php

declare(strict_types=1);

namespace Drupal\doesdesign_tools\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Alters existing Drupal routes to restrict access.
 *
 * Gates the appearance overview and theme action routes so that only accounts
 * holding 'administer permissions' can access them. The webmaster role has
 * 'administer themes' but not 'administer permissions', so this effectively
 * limits those routes to full administrators while leaving the theme settings
 * route (system.theme_settings_theme) accessible to webmasters.
 *
 * AI generated.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Restricts appearance management routes to full administrators.
   *
   * Changes the _permission requirement on the themes overview and action
   * routes from 'administer themes' to 'administer permissions'. The theme
   * settings route (system.theme_settings_theme) is intentionally excluded
   * so that the webmaster can still reach /admin/appearance/settings/shindo.
   */
  protected function alterRoutes(RouteCollection $collection): void {
    // Routes that must be restricted to full administrators only.
    $restricted = [
      'system.themes_page',
      'system.theme_install',
      'system.theme_uninstall',
      'system.theme_set_default',
      'system.theme_settings',
    ];

    foreach ($restricted as $route_name) {
      $route = $collection->get($route_name);
      if ($route) {
        $route->setRequirement('_permission', 'administer permissions');
      }
    }
  }

}
