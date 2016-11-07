<?php

namespace Drupal\Tests\commerce_shipping\Functional;

use Drupal\commerce_shipping\Entity\ShippingMethod;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;

/**
 * Tests the shipping method UI.
 *
 * @group commerce_shipping
 */
class ShippingMethodTest extends CommerceBrowserTestBase {

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
      'administer commerce_shipping_method',
    ], parent::getAdministratorPermissions());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $currency_importer = \Drupal::service('commerce_price.currency_importer');
    $currency_importer->import('EUR');
  }

  /**
   * Tests creating a shipping method.
   */
  public function testShippingMethodCreation() {
    $this->drupalGet('admin/commerce/config/shipping-methods');
    $this->getSession()->getPage()->clickLink('Add shipping method');
    $this->assertSession()->addressEquals('admin/commerce/config/shipping-methods/add');

    $edit = [
      'label' => 'Example',
      'status' => '1',
      'plugin' => 'flat_rate',
      'configuration[amount][number]' => '20.00',
      'configuration[amount][currency_code]' => 'USD',
      // Setting the 'id' can fail if focus switches to another field.
      // This is a bug in the machine name JS that can be reproduced manually.
      'id' => 'example',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->addressEquals('admin/commerce/config/shipping-methods');
    $this->assertSession()->responseContains('Example');

    $shipping_method = ShippingMethod::load('example');
    $this->assertEquals('example', $shipping_method->id());
    $this->assertEquals('Example', $shipping_method->label());
    $this->assertEquals('flat_rate', $shipping_method->getPluginId());
    $this->assertEquals(TRUE, $shipping_method->status());
    $shipping_method_plugin = $shipping_method->getPlugin();
    $configuration = $shipping_method_plugin->getConfiguration();
    $this->assertEquals(['number' => '20.00', 'currency_code' => 'USD'], $configuration['amount']);
  }

  /**
   * Tests editing a shipping method.
   */
  public function testShippingMethodEditing() {
    $values = [
      'id' => 'edit_example',
      'label' => 'Edit example',
      'plugin' => 'flat_rate',
      'configuration' => [
        'amount' => [
          'number' => '20.00',
          'currency_code' => 'USD',
        ],
      ],
      'status' => TRUE,
    ];
    $shipping_method = $this->createEntity('commerce_shipping_method', $values);

    $this->drupalGet('admin/commerce/config/shipping-methods/manage/' . $shipping_method->id());
    $edit = [
      'configuration[amount][number]' => '3.00',
      'configuration[amount][currency_code]' => 'USD',
    ];
    $this->submitForm($edit, 'Save');

    \Drupal::entityTypeManager()->getStorage('commerce_shipping_method')->resetCache();
    $shipping_method = ShippingMethod::load('edit_example');
    $this->assertEquals('edit_example', $shipping_method->id());
    $this->assertEquals('Edit example', $shipping_method->label());
    $this->assertEquals('flat_rate', $shipping_method->getPluginId());
    $this->assertEquals(TRUE, $shipping_method->status());
    $shipping_method_plugin = $shipping_method->getPlugin();
    $configuration = $shipping_method_plugin->getConfiguration();
    $this->assertEquals(['number' => '3.00', 'currency_code' => 'USD'], $configuration['amount']);
  }

  /**
   * Tests deleting a shipping method.
   */
  public function testShippingMethodDeletion() {
    $shipping_method = $this->createEntity('commerce_shipping_method', [
      'id' => 'for_deletion',
      'label' => 'For deletion',
      'plugin' => 'flat_rate',
      'configuration' => [
        'amount' => [
          'number' => '20.00',
          'currency_code' => 'USD',
        ],
      ],
    ]);
    $this->drupalGet('admin/commerce/config/shipping-methods/manage/' . $shipping_method->id() . '/delete');
    $this->submitForm([], 'Delete');
    $this->assertSession()->addressEquals('admin/commerce/config/shipping-methods');

    $shipping_method_exists = (bool) ShippingMethod::load('for_deletion');
    $this->assertFalse($shipping_method_exists, 'The shipping method has been deleted from the database.');
  }

}
