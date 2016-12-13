<?php

namespace Drupal\commerce_shipping\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\physical\Plugin\Field\FieldType\DimensionItem;
use Drupal\physical\Plugin\Field\FieldType\MeasurementItem;

/**
 * Defines the interface for package type configuration entities.
 *
 * Stores configuration for package type plugins.
 */
interface PackageTypeInterface extends ConfigEntityInterface {

  /**
   * Gets the package type position (not physical) weight.
   *
   * @return string
   *   The package type position (not physical) weight.
   */
  public function getWeight();

  /**
   * Sets the package type position (not physical) weight.
   *
   * @param int $weight
   *   The package type position (not physical) weight.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Gets the package type physical dimensions.
   *
   * @return \Drupal\physical\Plugin\Field\FieldType\DimensionItem
   *  The object storing the physical dimensions of the package type.
   */
  public function getPhysicalDimensions();

  /**
   * Sets the package type physical dimensions.
   *
   * @param \Drupal\physical\Plugin\Field\FieldType\DimensionItem $physical_dimensions
   *   The physical dimensions of the package type.
   *
   * @return $this
   */
  public function setPhysicalDimensions(DimensionItem $physical_dimensions);

  /**
   * Gets the package type physical weight measurement.
   *
   * @return \Drupal\physical\Plugin\Field\FieldType\MeasurementItem
   *   The object storing the physical weight measurement of the package type.
   */
  public function getPhysicalWeight();

  /**
   * Sets the package type physical weight measurements.
   *
   * @param \Drupal\physical\Plugin\Field\FieldType\MeasurementItem $physical_weight
   *   The physical weight measurements of the package type.
   *
   * @return $this
   */
  public function setPhysicalWeight(MeasurementItem $physical_weight);
}
