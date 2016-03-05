<?php

/**
 * @file
 * Contains \Drupal\commerce_shipping\Annotation\CommerceShippingService.
 */

namespace Drupal\commerce_shipping\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Plugin annotation object for shipping services.
 *
 * @Annotation
 */
class CommerceShippingService extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The plugin name used in the UI.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $name = '';

  /**
   * The plugin  used in the UI.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description = '';

}
