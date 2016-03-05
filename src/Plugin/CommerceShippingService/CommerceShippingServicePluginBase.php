<?php

/**
 * @file
 * Contains \Drupal\commerce_shipping\Plugin\CommerceShippingService\CommerceShippingServicePluginBase.
 */

namespace Drupal\commerce_shipping\Plugin\CommerceShippingService;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\PluginBase;
use Drupal\commerce_shipping\Plugin\CommerceShippingService\CommerceShippingServicePluginInterface;

/**
 * Defines a base shipping service implementation that most plugins will extend.
 */
abstract class CommerceShippingServicePluginBase extends PluginBase implements CommerceShippingServicePluginInterface {

  /**
   * Returns generic default configuration for block plugins.
   *
   * @return array
   *   An associative array with the default configuration.
   */
  protected function baseConfigurationDefaults() {
    return array(
      'id' => $this->getPluginId(),
      'provider' => $this->pluginDefinition['provider'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $definition = $this->getPluginDefinition();
    // Cast the admin description to a string since it is an object.
    // @see \Drupal\Core\StringTranslation\TranslatableMarkup
    return (string) $definition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $definition = $this->getPluginDefinition();
    // Cast the admin label to a string since it is an object.
    // @see \Drupal\Core\StringTranslation\TranslatableMarkup
    return (string) $definition['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->baseConfigurationDefaults(),
      $this->defaultConfiguration(),
      $configuration
    );
  }

}
