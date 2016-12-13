<?php

namespace Drupal\commerce_shipping\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\physical\Plugin\Field\FieldType\DimensionItem;
use Drupal\physical\Plugin\Field\FieldType\MeasurementItem;

/**
 * Defines the package type entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_package_type",
 *   label = @Translation("Package type"),
 *   label_singular = @Translation("package type"),
 *   label_plural = @Translation("package types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count shipping method",
 *     plural = "@count shipping methods",
 *   ),
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
 *   admin_permission = "administer commerce_package_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "weight" = "weight",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "weight",
 *     "physical_dimensions",
 *     "physical_weight",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/commerce_package_type/{commerce_package_type}",
 *     "add-form" = "/admin/commerce/config/commerce-package-type/add",
 *     "edit-form" = "/admin/commerce/config/commerce-package-type/manage/{commerce_package_type}",
 *     "delete-form" = "/admin/commerce/config/commerce-package-type/manage/{commerce_package_type}/delete",
 *     "collection" = "/admin/commerce/config/commerce-package-types"
 *   }
 * )
 */
class PackageType extends ConfigEntityBase implements PackageTypeInterface {

  /**
   * The package type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The package type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The package type weight (not physical)
   *
   * @var int
   */
  protected $weight;

  /**
   * The package type physical dimensions.
   *
   * @var \Drupal\physical\Plugin\Field\FieldType\DimensionItem
   */
  protected $physical_dimensions;

  /**
   * The package type physical weight.
   *
   * @var \Drupal\physical\Plugin\Field\FieldType\MeasurementItem
   */
  protected $physical_weight;

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPhysicalDimensions() {
    return $this->physical_dimensions;
  }

  /**
   * {@inheritdoc}
   */
  public function setPhysicalDimensions(DimensionItem $physical_dimensions) {
    $this->physical_dimensions = $physical_dimensions;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPhysicalWeight() {
    return $this->physical_weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setPhysicalWeight(MeasurementItem $physical_weight) {
    $this->physical_weight = $physical_weight;
    return $this;
  }
}
