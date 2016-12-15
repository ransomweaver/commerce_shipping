<?php

namespace Drupal\commerce_shipping\Plugin\Commerce\ShipmentPacker;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

interface ShipmentPackerInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface   {

  /**
   * Packs the order into an array of shipments.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return \Drupal\commerce_shipping\Entity\ShipmentInterface[]
   *   An array of shipments.
   */
  public function pack(OrderInterface $order);
}