<?php

namespace Drupal\commerce_shipping\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\physical\Plugin\Field\FieldType\DimensionItem;
use Drupal\physical\Plugin\Field\FieldType\MeasurementItem;

/**
 * Defines the package type entity class.
 *
 * @ConfigEntityType(
 *   id = "commerce_package_type",
 *   label = @Translation("Package type"),
 *   label_singular = @Translation("package type"),
 *   label_plural = @Translation("package types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count package type",
 *     plural = "@count package types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_shipping\PackageTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_shipping\Form\PackageTypeForm",
 *       "edit" = "Drupal\commerce_shipping\Form\PackageTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
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
 *     "add-form" = "/admin/commerce/config/package-types/add",
 *     "edit-form" = "/admin/commerce/config/package-types/manage/{commerce_package_type}",
 *     "delete-form" = "/admin/commerce/config/package-types/manage/{commerce_package_type}/delete",
 *     "collection" = "/admin/commerce/config/package-types"
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
