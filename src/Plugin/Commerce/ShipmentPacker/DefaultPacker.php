<?php

namespace Drupal\commerce_shipping\Plugin\Commerce\ShipmentPacker;

use Drupal\commerce_order\Entity\OrderInterface;

class DefaultPacker extends ShipmentPackerBase   {

  /**
   * @var array
   */
  protected $shipments = [];
  /**
   * {@inheritdoc}
   */
  public function pack(OrderInterface $order) {
  }

}