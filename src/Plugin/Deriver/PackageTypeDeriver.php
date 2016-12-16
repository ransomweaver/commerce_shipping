<?php

namespace Drupal\commerce_shipping\Plugin\Deriver;

use Drupal\commerce_shipping\PackageTypeManagerInterface;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a deriver for package types.
 */
class PackageTypeDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The package type manager.
   *
   * @var \Drupal\commerce_shipping\PackageTypeManager
   */
  protected $packageTypeManager;

  /**
   * Constructs a PackageTypeDeriver instance.
   *
   * @param \Drupal\Core\Config\Entity\Query\QueryFactory $query_factory
   *   The query factory.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   *
   * @param \Drupal\commerce_shipping\PackageTypeManagerInterface $package_type_manager
   *   The package type manager.
   */
  public function __construct(QueryFactory $query_factory, EntityManagerInterface $entity_manager, PackageTypeManagerInterface $package_type_manager) {
    $this->queryFactory = $query_factory;
    $this->entityManager = $entity_manager;
    $this->packageTypeManager = $package_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity.manager'),
      $container->get('plugin.manager.commerce_package_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // get all custom package types which should be rediscovered.
    $entity_ids = $this->queryFactory->get('commerce_package_type')
      ->condition('rediscover', TRUE)
      ->execute();
    $plugin_definitions = [];
    $package_type_entities = $this->entityManager->getStorage('commerce_package_type')->loadMultiple($entity_ids);
    /** @var \Drupal\commerce_shipping\Entity\PackageTypeInterface $package_type */
    foreach ($package_type_entities as $package_type) {
      $plugin_definitions[$package_type->uuid()] = $package_type->getPluginDefinition();
    }

    return $plugin_definitions;
  }
}