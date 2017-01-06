<?php

namespace Drupal\commerce_shipping\Entity;

use Drupal\commerce_store\Entity\EntityStoresInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the interface for shipping methods.
 *
 * Stores configuration for shipping method plugins.
 * Implemented as a content entity type to allow each store to have its own
 * shipping methods.
 */
interface ShippingMethodInterface extends ContentEntityInterface, EntityStoresInterface {

  /**
   * Gets the shipping method plugin.
   *
   * @return \Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodInterface
   *   The shipping method plugin.
   */
  public function getPlugin();

  /**
   * Gets the shipping method name.
   *
   * @return string
   *   The shipping method name.
   */
  public function getName();

  /**
   * Sets the shipping method name.
   *
   * @param string $name
   *   The shipping method name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the shipping method weight.
   *
   * @return string
   *   The shipping method weight.
   */
  public function getWeight();

  /**
   * Sets the shipping method weight.
   *
   * @param int $weight
   *   The shipping method weight.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Gets whether the shipping method is enabled.
   *
   * @return bool
   *   TRUE if the shipping method is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets whether the shipping method is enabled.
   *
   * @param bool $enabled
   *   Whether the shipping method is enabled.
   *
   * @return $this
   */
  public function setEnabled($enabled);

}
