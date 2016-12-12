<?php

namespace Drupal\commerce_shipping\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Package type entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_package_type",
 *   label = @Translation("Package type"),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_shipping\PackageTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_shipping\Form\PackageTypeForm",
 *       "edit" = "Drupal\commerce_shipping\Form\PackageTypeForm",
 *       "delete" = "Drupal\commerce_shipping\Form\PackageTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_shipping\PackageTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_package_type",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/commerce_package_type/{commerce_package_type}",
 *     "add-form" = "/admin/commerce/commerce_package_type/add",
 *     "edit-form" = "/admin/commerce/commerce_package_type/{commerce_package_type}/edit",
 *     "delete-form" = "/admin/commerce/commerce_package_type/{commerce_package_type}/delete",
 *     "collection" = "/admin/commerce/commerce_package_type"
 *   }
 * )
 */
class PackageType extends ConfigEntityBase implements PackageTypeInterface {

  /**
   * The Package type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Package type label.
   *
   * @var string
   */
  protected $label;

}
