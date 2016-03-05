<?php

/**
 * @file
 * Contains \Drupal\commerce_shipping\Controller\CommerceShippingController.
 */

namespace Drupal\commerce_shipping\Controller;

use Drupal\commerce_shipping\Plugin\CommerceShippingServicePluginManager;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller routines for admin commerce_shipping routes.
 */
class CommerceShippingController extends ControllerBase {

  /**
   * The commerce shipping service plugin manager.
   *
   * @var \Drupal\commerce_shipping\Plugin\CommerceShippingServicePluginManager
   */
  protected $pluginManager;

  /**
   * Constructs a CommerceShippingController object.
   *
   * @param \Drupal\commerce_shipping\Plugin\CommerceShippingServicePluginManager $pluginManager
   *   The shipping service plugin manager.
   */
  public function __construct(CommerceShippingServicePluginManager $pluginManager) {
    $this->pluginManager = $pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.commerce_shipping_service')
    );
  }

  /**
   * Display the admin overview page.
   */
  public function adminOverviewPage() {
    $header = [
      $this->t('Name'),
      $this->t('Description'),
    ];
    $rows = [];
    $plugins = $this->pluginManager->getDefinitions();
    foreach ($plugins as $id => $plugin_definition) {
      $plugin = $this->pluginManager->createInstance($id);
      $rows[] = [
        $plugin->label(),
        $plugin->getDescription(),
      ];
    }
    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

}