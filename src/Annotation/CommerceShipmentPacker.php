<?php

namespace Drupal\commerce_shipping\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the shipment packer plugin annotation object.
 *
 * Plugin namespace: Plugin\Commerce\ShipmentPacker
 *
 * @see plugin_api
 *
 * @Annotation
 */
class CommerceShipmentPacker extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The shipment packer label.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The supported shipment packer services.
   *
   * An array of labels keyed by ID.
   *
   * @var array
   */
  public $services = [];
}