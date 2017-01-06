<?php

namespace Drupal\commerce_shipping;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of package types.
 */
class PackageTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Package type');
    $header['dimensions'] = $this->t('Dimensions');
    $header['weight'] = $this->t('Weight');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_shipping\Entity\PackageTypeInterface $entity */
    $row['label'] = $entity->label();

    // @todo Use the physical number formatter here.
    $dimensions = $entity->getDimensions();
    $dimension_list = [
      $dimensions['length'],
      $dimensions['width'],
      $dimensions['height'],
    ];
    $row['dimensions'] = implode(' × ', $dimension_list) . ' ' . $dimensions['unit'];

    $weight = $entity->getWeight();
    $row['weight'] = $weight['number'] . ' ' . $weight['unit'];

    return $row + parent::buildRow($entity);
  }

}
