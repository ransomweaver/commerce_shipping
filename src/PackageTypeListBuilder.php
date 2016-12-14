<?php

namespace Drupal\commerce_shipping;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Package type entities.
 */
class PackageTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Package Type');
    $header['id'] = $this->t('Machine Name');
    $header['physical_dimensions'] = $this->t('Physical Dimensions');
    $header['physical_weight'] = $this->t('Physical Weight');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_shipping\Entity\PackageTypeInterface $entity */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();

    $physical_dimensions = NULL;
    if (!empty($entity->getPhysicalDimensions())) {
      $dimensions = $entity->getPhysicalDimensions();
      $unit = $entity->getPhysicalDimensions()['unit'];
      $physical_dimensions = 'L ' . $dimensions['length'] . $unit .
        ' x W ' . $dimensions['width'] . $unit .
        ' x H ' . $dimensions['height'] . $unit;
    }
    $row['physical_dimensions'] = $physical_dimensions;

    $physical_weight = NULL;
    if (!empty($entity->getPhysicalWeight())) {
      $weight = $entity->getPhysicalWeight();
      $physical_weight = $weight['number'] . $weight['unit'];
    }
    $row['physical_weight'] = $physical_weight;

    return $row + parent::buildRow($entity);
  }

}
