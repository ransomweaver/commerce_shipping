<?php

/**
 * @file
 * Rules integration for shipping.
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implements hook_rules_event_info().
 */
function commerce_shipping_rules_event_info() {
  // So that we can use the entity_rules_events_variables() helper function.
  module_load_include('inc', 'entity', 'entity.rules');

  $events = array();

  $events['commerce_shipping_collect_rates'] = array(
    'label' => t('Collecting shipping rates for an order'),
    'group' => t('Commerce Shipping'),
    'variables' => entity_rules_events_variables('commerce_order', t('Order', array(), array('context' => 'a drupal commerce order'))),
    'access callback' => 'commerce_order_rules_access',
  );

  // Include the Line Item module's Rules integration to reuse its access callback.
  module_load_include('inc', 'commerce_line_item', 'commerce_line_item.rules');

  $events['commerce_shipping_calculate_rate'] = array(
    'label' => t('Calculating a shipping rate'),
    'group' => t('Commerce Shipping'),
    'variables' => entity_rules_events_variables('commerce_line_item', t('Shipping line item')),
    'access callback' => 'commerce_line_item_rules_access',
  );

  return $events;
}

/**
 * Implements hook_rules_action_info().
 */
function commerce_shipping_rules_action_info() {
  $actions = array();

  if (count(commerce_shipping_services()) > 0) {
    $actions['commerce_shipping_service_rate_order'] = array(
      'label' => t('Add a rate for a shipping service to an order'),
      'parameter' => array(
        'shipping_service_name' => array(
          'type' => 'text',
          'label' => t('Shipping service'),
          'options list' => 'commerce_shipping_service_options_list',
        ),
        'commerce_order' => array(
          'type' => 'commerce_order',
          'label' => t('Order'),
        ),
      ),
      'group' => t('Commerce Shipping'),
    );
  }

  if (count(commerce_shipping_methods()) > 0) {
    $actions['commerce_shipping_method_collect_rates'] = array(
      'label' => t('Collect rates for a shipping method'),
      'parameter' => array(
        'shipping_method_name' => array(
          'type' => 'text',
          'label' => t('Shipping method'),
          'options list' => 'commerce_shipping_method_options_list',
        ),
        'commerce_order' => array(
          'type' => 'commerce_order',
          'label' => t('Order'),
        ),
      ),
      'group' => t('Commerce Shipping'),
    );
  }

  $actions['commerce_shipping_delete_shipping_line_items'] = array(
    'label' => t("Delete an order's shipping line items"),
    'parameter' => array(
      'commerce_order' => array(
        'type' => 'commerce_order',
        'label' => t('Order'),
      ),
    ),
    'group' => t('Commerce Shipping'),
  );

  return $actions;
}

/**
 * @}
 */
