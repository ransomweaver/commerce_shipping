<?php

/**
 * @file
 * Contains \Drupal\commerce_shipping_example\Plugin\CommerceShippingService\CommerceShippingServiceExample.
 */

namespace Drupal\commerce_shipping_example\Plugin\CommerceShippingService;

use Drupal\commerce_shipping\Plugin\CommerceShippingService\CommerceShippingServicePluginBase;

/**
 * Provides the translations for cms foundation.
 *
 * @CommerceShippingService(
 *   id = "commerce_shipping_example",
 *   name = @Translation("Example service"),
 *   description = @Translation("This service only use is for testing and code documentation."),
 * )
 */
class CommerceShippingServiceExample extends CommerceShippingServicePluginBase {}
