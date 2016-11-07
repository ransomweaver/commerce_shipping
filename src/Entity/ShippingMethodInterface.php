<?php

namespace Drupal\commerce_shipping\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;

/**
 * Defines the interface for shipping method configuration entities.
 *
 * Stores configuration for shipping method plugins.
 */
interface ShippingMethodInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {

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
   * Gets the shipping method plugin.
   *
   * @return \Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodInterface
   *   The shipping method plugin.
   */
  public function getPlugin();

  /**
   * Gets the shipping method plugin ID.
   *
   * @return string
   *   The shipping method plugin ID.
   */
  public function getPluginId();

  /**
   * Sets the shipping method plugin ID.
   *
   * @param string $plugin_id
   *   The shipping method plugin ID.
   *
   * @return $this
   */
  public function setPluginId($plugin_id);

}
