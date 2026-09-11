<?php

namespace Drupal\doesdesign_tools;

use Drupal\Core\Entity\EntityInterface;
use Drupal\menu_ui\MenuListBuilder as CoreMenuListBuilder;

/**
 * Filters the menu overview list for accounts without full menu access.
 *
 * AI generated. Drupal's config entity query does not apply per-entity access
 * checks, so the parent MenuListBuilder renders all menus regardless of entity
 * access. This subclass overrides buildRow() to skip menus that are
 * access-forbidden for the current user, mirroring the vocabulary approach in
 * doesdesign_tools_form_taxonomy_overview_vocabularies_alter().
 */
class MenuListBuilder extends CoreMenuListBuilder {

  /**
   * {@inheritdoc}
   *
   * Returns an empty array (skipping the row) when the current account is not
   * permitted to view the menu entity, so hidden menus are absent from the
   * table.
   *
   * @return array<string, mixed>
   *   A render array for the row, or an empty array to suppress the row.
   */
  public function buildRow(EntityInterface $entity) {
    // Delegate to hook_ENTITY_TYPE_access() via entity::access().
    if (!$entity->access('view')) {
      return [];
    }
    return parent::buildRow($entity);
  }

}
