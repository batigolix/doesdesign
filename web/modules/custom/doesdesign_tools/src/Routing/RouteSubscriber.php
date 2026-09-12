<?php

declare(strict_types=1);

namespace Drupal\doesdesign_tools\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Alters existing Drupal routes to restrict access.
 *
 * Gates the appearance overview, theme action routes, and webform plugin
 * report routes so that only accounts holding 'administer permissions' can
 * access them. The webmaster role has 'administer themes' and 'administer
 * webform' but not 'administer permissions', so this effectively limits those
 * routes to full administrators while leaving other webmaster-facing routes
 * untouched.
 *
 * AI generated.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Restricts appearance and webform plugin report routes to full admins.
   *
   * Changes the _permission requirement on the themes overview, theme action
   * routes, and webform plugin report routes to 'administer permissions'. The
   * theme settings route (system.theme_settings_theme) is intentionally
   * excluded so that the webmaster can still reach
   * /admin/appearance/settings/shindo. The webform plugin report routes list
   * internal plugin providers and are developer/debug info only.
   */
  protected function alterRoutes(RouteCollection $collection): void {
    // Routes that must be restricted to full administrators only.
    $restricted = [
      // Appearance management.
      'system.themes_page',
      'system.theme_install',
      'system.theme_uninstall',
      'system.theme_set_default',
      'system.theme_settings',
      // Webform plugin reports — developer/debug info, not for webmasters.
      'webform.reports_plugins.elements',
      'webform.reports_plugins.elements.test',
      'webform.reports_plugins.exporters',
      'webform.reports_plugins.handlers',
      'webform.reports_plugins.variants',
    ];

    foreach ($restricted as $route_name) {
      $route = $collection->get($route_name);
      if ($route) {
        $route->setRequirement('_permission', 'administer permissions');
      }
    }
  }

}
