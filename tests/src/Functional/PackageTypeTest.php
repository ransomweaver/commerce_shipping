<?php

namespace Drupal\Tests\commerce_shipping\Functional;

use Drupal\commerce_shipping\Entity\PackageType;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;

/**
 * Tests the package type UI.
 *
 * @group commerce_shipping
 */
class PackageTypeTest extends CommerceBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_shipping',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_package_type',
    ], parent::getAdministratorPermissions());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Tests creating a package type.
   */
  public function testPackageTypeCreation() {
    $this->drupalGet('admin/commerce/config/package-types');
    $this->getSession()->getPage()->clickLink('Add package type');
    $this->assertSession()->addressEquals('admin/commerce/config/package-types/add');

    $edit = [
      'label' => 'Example',
      'physical_dimensions[length]' => '20',
      'physical_dimensions[width]' => '10',
      'physical_dimensions[height]' => '10',
      'physical_dimensions[unit]' => 'in',
      'physical_weight[number]' => '10',
      'physical_weight[unit]' => 'oz',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->addressEquals('admin/commerce/config/package-types');
    $this->assertSession()->responseContains('Example');

    $package_type = PackageType::load('example');
    $this->assertEquals('example', $package_type->id());
    $this->assertEquals('Example', $package_type->label());
    $this->assertEquals('20', $package_type->getPhysicalDimensions()['length']);
    $this->assertEquals('10', $package_type->getPhysicalDimensions()['width']);
    $this->assertEquals('10', $package_type->getPhysicalDimensions()['height']);
    $this->assertEquals('in', $package_type->getPhysicalDimensions()['unit']);
    $this->assertEquals('10', $package_type->getPhysicalWeight()['number']);
    $this->assertEquals('oz', $package_type->getPhysicalWeight()['unit']);
  }

  /**
   * Testing editing a package type.
   */
  public function testPackageTypeEditing() {
    $values = [
      'id' => 'edit_example',
      'label' => 'Edit example',
      'physical_dimensions[length]' => '15',
      'physical_dimensions[width]' => '15',
      'physical_dimensions[height]' => '15',
      'physical_dimensions[unit]' => 'm',
    ];
    $package_type = $this->createEntity('commerce_package_type', $values);

    $this->drupalGet('admin/commerce/config/package-types/manage/' . $package_type->id());
    $edit = [
      'physical_dimensions[length]' => '20',
      'physical_weight[number]' => '2',
      'physical_weight[unit]' => 'lb',
    ];
    $this->submitForm($edit, 'Save');

    \Drupal::entityTypeManager()->getStorage('commerce_package_type')->resetCache();
    $package_type = PackageType::load('edit_example');
    $this->assertEquals('edit_example', $package_type->id());
    $this->assertEquals('Edit example', $package_type->label());
    $this->assertEquals('20', $package_type->getPhysicalDimensions()['length']);
    $this->assertEquals('15', $package_type->getPhysicalDimensions()['width']);
    $this->assertEquals('15', $package_type->getPhysicalDimensions()['height']);
    $this->assertEquals('m', $package_type->getPhysicalDimensions()['unit']);
    $this->assertEquals('2', $package_type->getPhysicalWeight()['number']);
    $this->assertEquals('lb', $package_type->getPhysicalWeight()['unit']);
  }

  /**
   * Tests deleting a package type.
   */
  public function testPackageTypeDeletion() {
    $package_type = $this->createEntity('commerce_package_type', [
      'id' => 'for_deletion',
      'label' => 'For deletion',
      'physical_dimensions[length]' => '20',
      'physical_dimensions[width]' => '20',
      'physical_dimensions[height]' => '20',
      'physical_dimensions[unit]' => 'in',
      'physical_weight[number]' => '5',
      'physical_weight[unit]' => 'lb'
    ]);
    $this->drupalGet('admin/commerce/config/package-types/manage/' . $package_type->id() . '/delete');
    $this->submitForm([], 'Delete');
    $this->assertSession()->addressEquals('admin/commerce/config/package-types');

    $package_type_exists = (bool) PackageType::load('for_deletion');
    $this->assertFalse($package_type_exists, 'The package type has been deleted from the database.');
  }
}