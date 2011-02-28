<?php
// $Id$

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
  $items = array(
    'commerce_shipping_methods' => array(
      'label' => t('Select available shipping methods for an order'),
      'group' => t('Commerce - shipping'),
      'variables' => commerce_order_rules_event_variables(t('Order', array(), array('context' => 'a drupal commerce order'))),
      'access callback' => 'commerce_order_rules_access',
    ),
  );

  return $items;
}

/**
 * Implements hook_rules_action_info().
 */
function commerce_shipping_rules_action_info() {
  $actions = array();

  // Add an action for each available payment method.
  foreach (commerce_shipping_methods() as $shipping_method) {
    $actions['commerce_shipping_enable_' . $shipping_method['method_id']] = array(
      'label' => t('Enable shipping method: @method', array('@method' => $shipping_method['title'])),
      'parameter' => array(
        'commerce_order' => array('type' => 'commerce_order', 'label' => t('Order')),
        'shipping_method' => array(
          'type' => 'commerce_shipping_settings',
          'restriction' => 'input',
          'label' => t('Shipping settings'),
          'shipping_method' => $shipping_method['method_id'],
        ),
      ),
      'group' => t('Commerce - shipping'),
      'base' => $shipping_method['base'],
      'callbacks' => array(
        'execute' => 'commerce_shipping_enable_method',
      ),
    );
  }
  return $actions;
}

/**
 * Generic execution callback for the shipping method.
 */
function commerce_shipping_enable_method($order, $shipping_method, $action_settings, $rule_state, $action, $callback_type) {
  // Find the Rule that contains this action.
  $rule = $action->parentElement();

  while ($rule->getPluginName() != 'reaction rule') {
    $rule = $rule->parentElement();
  }

  // Initialize variables for the payment method ID and settings.
  if (is_array($shipping_method)) {
    $method_id = $shipping_method['method_id'];
    $settings = !empty($shipping_method['settings']) ? $shipping_method['settings'] : array();
  }
  else {
    $method_id = $shipping_method;
    $settings = array();
  }
  // Create a unique key for the instance of the payment method represented by
  // this action.
  $instance_id = commerce_shipping_method_instance_id($method_id, $rule);

  // Set the shipping method to the order along with its settings and context.

  $order->shipping_methods[$instance_id] = array(
    'method_id' => $method_id,
    'settings' => $settings,
    'rule_name' => $rule->name,
    'label' => $rule->label,
  );
}

/**
 * Implements hook_rules_data_info().
 */
function commerce_shipping_rules_data_info() {
  $data['commerce_shipping_settings'] = array(
    'label' => t('Shipping settings'),
    'ui class' => 'RulesDataUIShippingSettings',
  );
  return $data;
}

/**
 * Adds a payment method settings form to the enabling action.
 */
class RulesDataUIShippingSettings extends RulesDataUI implements RulesDataDirectInputFormInterface {
  public static function getDefaultMode() {
    return 'input';
  }

  public static function inputForm($name, $info, $settings, RulesPlugin $element) {
    // If the specified payment method exists...
    if ($shipping_method = commerce_shipping_method_load($info['shipping_method'])) {
      $form[$name]['method_id'] = array('#type' => 'value', '#value' => $info['shipping_method']);

      // If the payment method has a settings callback...
      if ($callback = commerce_shipping_method_callback($shipping_method, 'settings_form')) {
        // Prepare an array of payment method settings defaults.
        $method_settings = !empty($settings[$name]) ? $settings[$name]['settings'] : array();

        // Add the settings form elements to the action form.
        $form[$name]['settings'] = $callback($method_settings);
      }
      else {
        // Otherwise add an appropriate message.
        $form[$name]['settings']['no_settings']['#markup'] = t('No settings for this shipping method.');
      }
    }
    else {
      $form[$name]['invalid']['#markup'] = t('Invalid shipping method.');
    }
    return $form;
  }

  public static function render($value) {
    return array();
  }
}

/**
 * @}
 */
