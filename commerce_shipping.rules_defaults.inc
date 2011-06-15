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

  // Add a default rule for each available shipping method.
  // TODO, this should be depricated at some point.
  foreach (commerce_shipping_methods() as $method_id => $shipping_method) {
    $rule = rules_reaction_rule();

    $rule->label = $shipping_method['title'];
    $rule->active = FALSE;

    $rule
      ->event('commerce_shipping_methods')
      ->action('commerce_shipping_enable_' . $method_id, array(
        'commerce_order:select' => 'site:current-cart-order',
        'shipping_method' => $method_id,
      ));

    $rules['commerce_shipping_' . $method_id] = $rule;
  }

  // Add a default rule fore each available plugin.
  foreach (shipping_plugin_get_plugins('quotes') as $key => $plugin) {
    // Don't create rule if plugin don't want it.
    if (!isset($plugin['create_rule']) || $plugin['create_rule'] !== FALSE) {
      $rule = rules_reaction_rule();

      $rule->label = $plugin['title'];
      $rule->active = FALSE;

      $rule
        ->event('commerce_shipping_methods')
        ->action('commerce_shipping_enable_plugin-' . $key, array(
          'commerce_order:select' => 'site:current-cart-order',
          'shipping_method' => 'plugin-' . $key,
        ));

      $rules['commerce_shipping_plugin_' . $key] = $rule;
    }
  }

  return $rules;
}
