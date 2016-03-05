<?php

/**
 * @file
 * Contains \Drupal\commerce_shipping\Plugin\CommerceShippingService\CommerceShippingServicePluginInterface.
 */

namespace Drupal\commerce_shipping\Plugin\CommerceShippingService;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the required interface for all WebTranslateIt plugins.
 */
interface CommerceShippingServicePluginInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

  /**
   * Returns the user-facing shipping service label.
   *
   * @return string
   *   The shipping service label.
   */
  public function label();

  /**
   * Returns the user-facing shipping service description.
   *
   * @return string
   *   The shipping service description.
   */
  public function getDescription();

}
