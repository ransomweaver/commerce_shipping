<?php
// $Id$

/**
 * @file
 * Default rules configuration for Payment.
 */

/**
 * Implements hook_default_rules_configuration().
 */
function commerce_shipping_default_rules_configuration() {
  $rules = array();

  // Add a default rule for each available payment method.
  foreach (commerce_shipping_methods() as $method_id => $shipping_method) {
    $rule = rules_reaction_rule();

    $rule->label = $shipping_method['title'];
    $rule->active = FALSE;

    $rule
      ->event('commerce_shipping_methods')
      ->action('commerce_shipping_enable_' . $method_id, array(
        'commerce_order:select' => 'order',
        'shipping_method' => $method_id,
      ));

    $rules['commerce_shipping_' . $method_id] = $rule;
  }

  return $rules;
}
