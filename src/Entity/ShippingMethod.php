<?php

namespace Drupal\commerce_shipping\Entity;

use Drupal\commerce_shipping\ShippingMethodPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the shipping method entity class.
 *
 * @ConfigEntityType(
 *   id = "commerce_shipping_method",
 *   label = @Translation("Shipping method"),
 *   label_singular = @Translation("shipping method"),
 *   label_plural = @Translation("shipping methods"),
 *   label_count = @PluralTranslation(
 *     singular = "@count shipping method",
 *     plural = "@count shipping methods",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_shipping\ShippingMethodListBuilder",
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *     "form" = {
 *       "add" = "Drupal\commerce_shipping\Form\ShippingMethodForm",
 *       "edit" = "Drupal\commerce_shipping\Form\ShippingMethodForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer commerce_shipping_method",
 *   config_prefix = "commerce_shipping_method",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "weight" = "weight",
 *     "status" = "status",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "weight",
 *     "status",
 *     "plugin",
 *     "configuration",
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/config/shipping-methods/add",
 *     "edit-form" = "/admin/commerce/config/shipping-methods/manage/{commerce_shipping_method}",
 *     "delete-form" = "/admin/commerce/config/shipping-methods/manage/{commerce_shipping_method}/delete",
 *     "collection" =  "/admin/commerce/config/shipping-methods"
 *   }
 * )
 */
class ShippingMethod extends ConfigEntityBase implements ShippingMethodInterface {

  /**
   * The shipping method ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The shipping method label.
   *
   * @var string
   */
  protected $label;

  /**
   * The shipping method weight.
   *
   * @var int
   */
  protected $weight;

  /**
   * The plugin ID.
   *
   * @var string
   */
  protected $plugin;

  /**
   * The plugin configuration.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * The plugin collection that holds the shipping method plugin.
   *
   * @var \Drupal\commerce_shipping\ShippingMethodPluginCollection
   */
  protected $pluginCollection;

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $weight;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getPluginCollection()->get($this->plugin);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginId($plugin_id) {
    $this->plugin = $plugin_id;
    $this->pluginCollection = NULL;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'configuration' => $this->getPluginCollection(),
    ];
  }

  /**
   * Gets the plugin collection that holds the shipping method plugin.
   *
   * Ensures the plugin collection is initialized before returning it.
   *
   * @return \Drupal\commerce_shipping\ShippingMethodPluginCollection
   *   The plugin collection.
   */
  protected function getPluginCollection() {
    if (!$this->pluginCollection) {
      $plugin_manager = \Drupal::service('plugin.manager.commerce_shipping_method');
      $this->pluginCollection = new ShippingMethodPluginCollection($plugin_manager, $this->plugin, $this->configuration, $this->id);
    }
    return $this->pluginCollection;
  }

}
