<?php
// $Id$

/**
 * @file
 * Callback functions for the shipping module's checkout panes.
 */


/**
 * shipping pane: form callback.
 */
function commerce_shipping_pane_checkout_form($form, &$form_state, $checkout_pane, $order) {
  // TODO, before we active shipping methods etc, we should check that there
  // actually are shippable products. Right not that is not possible because
  // physical products aren't defined yet.

  $pane_form = array();

  // Invoke the shipping methods event that will populate the order with
  // an array of method IDs for available shipping methods.
  $order->shipping_methods = array();
  rules_invoke_all('commerce_shipping_methods', $order);

  // Sort the shipping methods array by the enabling Rules' weight values.
  uasort($order->shipping_methods, 'drupal_sort_weight');

  // Generate an array of shipping method options for the checkout form.
  $options = array();

  foreach ($order->shipping_methods as $instance_id => $method_info) {
    // Ensure we've received a valid shipping method.
    if ($shipping_method = commerce_shipping_method_load($method_info['method_id'])) {
      if ($callback = commerce_shipping_method_callback($shipping_method, 'form_label')) {
        $options[$instance_id] = $callback($shipping_method, $method_instance, $order);
      }
      else {
        $method_instance = commerce_shipping_method_instance_load($instance_id);
        $options[$instance_id] = !empty($method_instance['shipping_label']) ?
          $method_instance['shipping_label'] : $shipping_method['display_title'];
      }
    }
    elseif ($plugin = shipping_plugin_get_plugin('quotes', str_replace('plugin-', '', $method_info['method_id']))) {
      $class = new $plugin['handler']['class'](array(), $order);
      if ($class->form_label()) {
        $options[$instance_id] = $class->form_label();
      }
      else {
        $method_instance = commerce_shipping_method_instance_load($instance_id);
        $plugin_title = isset($plugin['display_title']) ? $plugin['display_title'] : $plugin['title'];
        $options[$instance_id] = !empty($method_instance['shipping_label']) ?
          $method_instance['shipping_label'] : $plugin_title;
      }
    }
  }

  // If no shipping methods were found, return the empty form.
  if (empty($options)) {
    return $pane_form;
  }

  // Store the shipping methods in the form for validation purposes.
  $pane_form['shipping_methods'] = array(
    '#type' => 'value',
    '#value' => $order->shipping_methods,
  );

  // If at least one shipping option is available...
  if (!empty($options)) {
    // Add a radio select widget to specify the shipping method.
    $pane_form['shipping_method'] = array(
      '#type' => 'radios',
      '#options' => $options,
      '#ajax' => array(
        'callback' => 'commerce_shipping_pane_checkout_form_details_refresh',
        'wrapper' => 'shipping-details',
      ),
    );

    // Find the default shipping method using either the preselected value stored
    // in the order / checkout pane or the first available method.
    $pane_values = !empty($form_state['values']) ? $form_state['values'][$checkout_pane['pane_id']] : array();

    if (isset($pane_values['shipping_method']) && isset($options[$pane_values['shipping_method']])) {
      $default_value = $pane_values['shipping_method'];
    }
    elseif (isset($order->data['shipping_method']) && isset($options[$order->data['shipping_method']])) {
      $default_value = $order->data['shipping_method'];
    }
    else {
      reset($options);
      $default_value = key($options);
    }

    // Set the default value for the shipping method radios.
    $pane_form['shipping_method']['#default_value'] = $default_value;
    $pane_form['shipping_details'] = array();

    // Add the shipping method specific form elements.
    $method_info = $order->shipping_methods[$pane_form['shipping_method']['#default_value']];
    $shipping_method = commerce_shipping_method_load($method_info['method_id']);
    if ($shipping_method) {
      if ($callback = commerce_shipping_method_callback($shipping_method, 'submit_form')) {
        $pane_form['shipping_details'] = $callback($shipping_method, $pane_values, $checkout_pane, $order);
      }
    }
    else {
      $plugin = shipping_plugin_get_plugin('quotes', str_replace('plugin-', '', $method_info['method_id']));
      $settings = isset($method_instance['settings']) ? $method_instance['settings'] : array();
      $class = new $plugin['handler']['class']($settings, $order);
      $pane_form['shipping_details'] = $class->submit_form($pane_values, $checkout_pane);
      $pane_form['commerce_shipping_plugin'] = array(
        '#type' => 'value',
        '#value' => $class
      );
    }

    $pane_form['shipping_details']['#prefix'] = '<div id="shipping-details">';
    $pane_form['shipping_details']['#suffix'] = '</div>';
  }
  return $pane_form;
}

/**
 * Returns the shipping details element for display via AJAX.
 */
function commerce_shipping_pane_checkout_form_details_refresh($form, $form_state) {
  return $form['commerce_shipping']['shipping_details'];
}

/**
 * shipping pane: validation callback.
 */
function commerce_shipping_pane_checkout_form_validate($form, &$form_state, $checkout_pane, $order) {
  $pane_form = $form[$checkout_pane['pane_id']];
  $pane_values = $form_state['values'][$checkout_pane['pane_id']];
  if (!isset($pane_values['shipping_details'])) {
    $pane_values['shipping_details'] = array();
  }
  if (isset($pane_form['commerce_shipping_plugin'])) {
    $class = $pane_form['commerce_shipping_plugin']['#value'];
  }

  // Only attempt validation if there were shipping methods available.
  if ($pane_values['shipping_methods']) {
    // If the selected shipping method was changed...
    if ($pane_values['shipping_method'] != $pane_form['shipping_method']['#default_value']) {
      // And the newly selected method has a valid form callback...
      if (isset($class)) {
        // Always rebuild for plugins.
        return FALSE;
      }
      elseif ($shipping_method = commerce_shipping_method_instance_load($pane_values['shipping_method'])) {
        if (commerce_shipping_method_callback($shipping_method, 'submit_form')) {
          // Fail validation so the form is rebuilt to include the shipping method
          // specific form elements.
          return FALSE;
        }
      }
    }

    if (isset($class)) {
      $result = $class->submit_form_validate($pane_form['shipping_details'], $pane_values['shipping_details'], array($checkout_pane['pane_id'], 'shipping_details'), $order);
      return $result === FALSE ? FALSE : TRUE;
    }
    else {
      // Delegate validation to the shipping method callback.
      $shipping_method = commerce_shipping_method_instance_load($pane_values['shipping_method']);

      if ($callback = commerce_shipping_method_callback($shipping_method, 'submit_form_validate')) {
        $result = $callback($shipping_method, $pane_form['shipping_details'], $pane_values['shipping_details'], $order, array($checkout_pane['pane_id'], 'shipping_details'));

        // To prevent shipping method validation routines from having to return TRUE
        // explicitly, only return FALSE if it was specifically returned.  Otherwise
        // default to TRUE.
        return $result === FALSE ? FALSE : TRUE;
      }
    }
  }
  // Nothing to validate.
  return TRUE;
}

/**
 * shipping pane: submit callback.
 */
function commerce_shipping_pane_checkout_form_submit($form, &$form_state, $checkout_pane, $order) {
  $order_wrapper = entity_metadata_wrapper('commerce_order', $order);

  // In case this order already have shipping line items, we need to remove them.
  commerce_shipping_clear_order($order);

  $pane_form = $form[$checkout_pane['pane_id']];
  $pane_values = $form_state['values'][$checkout_pane['pane_id']];
  if (isset($pane_form['commerce_shipping_plugin'])) {
    $class = $pane_form['commerce_shipping_plugin']['#value'];
    $rule_ids = explode('|', $pane_values['shipping_method']);
    $method_id = $rule_ids[0];
  }

  // Only process if there were shipping methods available.
  if ($pane_values['shipping_methods']) {
    $order->data['shipping_method'] = $pane_values['shipping_method'];

    $default_currency_code = commerce_default_currency();
    if ($balance = commerce_payment_order_balance($order)) {
      $default_currency_code = $balance['currency_code'];
    }
    $form_values = isset($form_state['values']['commerce_shipping']['shipping_details']) ? $form_state['values']['commerce_shipping']['shipping_details'] : array();

    // Let the shipping method calculate the shipping price.
    if (isset($class)) {
      $shipping_method = shipping_plugin_get_plugin('quotes', str_replace('plugin-', '', $method_id));
      $shipping_line_items = $class->calculate_quote($default_currency_code, $form_values, $order);
    }
    else {
      $shipping_method = commerce_shipping_method_instance_load($pane_values['shipping_method']);
      if ($callback = commerce_shipping_method_callback($shipping_method, 'calculate_shipping')) {
        // Setup variables for the calcualte_shipping function.
        $settings = array(
          'checkout_settings' => $form_values,
          'rules_settings' => $shipping_method['settings'],
        );
        $shipping_line_items = $callback($settings, $order, $default_currency_code);
      }
    }

    // Loop through the result and create line items if possible.
    if (isset($shipping_line_items) && is_array($shipping_line_items)) {
      foreach ($shipping_line_items as $shipping_line_item) {
        $line_item = commerce_shipping_line_item_new($shipping_method);
        $line_item->order_id = $order->order_id;
        $line_item_wrapper = entity_metadata_wrapper('commerce_line_item', $line_item);
        if (is_numeric($shipping_line_item)) {
          $line_item_wrapper->commerce_unit_price->amount = $shipping_line_item;
          $line_item_wrapper->commerce_unit_price->currency_code = $default_currency_code;
        }
        elseif (is_array($shipping_line_item) && isset($shipping_line_item['amount'])) {
          $line_item_wrapper->commerce_unit_price->amount = $shipping_line_item['amount'];
          $line_item_wrapper->commerce_unit_price->currency_code = isset($shipping_line_item['currence_code']) ? $shipping_line_item['currence_code'] : $default_currency_code;
          if (isset($shipping_line_item['label'])) {
            $line_item_wrapper->line_item_label = $shipping_line_item['label'];
          }
          if (isset($shipping_line_item['quantity'])) {
            $line_item_wrapper->quantity = $shipping_line_item['quantity'];
          }
        }

        // TODO: Remember to bug Ryan about this until resolved.
        // Manually add the price component. We should use commerce_price_component_add()
        // But it also adds a base_price component which will mess up totals.
        // (See http://drupal.org/node/1192200)
        $price = $line_item_wrapper->commerce_unit_price->value();
        $price['data']['components'][] = array(
          'name' => 'quote',
          'price' => $price,
          'included' => TRUE,
        );
        $line_item_wrapper->commerce_unit_price = $price;

        $line_item_wrapper->save();
        $order_wrapper->commerce_line_items[] = $line_item_wrapper->value();
      }
    }

    // Lastly we save the order.
    commerce_order_save($order);

    // This is not actually needed, but for flexibility allow shipping methods
    // to react on the form submission.
    if (isset($class)) {
      $class->shipping_items_created($pane_form['shipping_details'], $pane_values['shipping_details'], $order);
    }
    elseif ($callback = commerce_shipping_method_callback($shipping_method, 'submit_form_submit')) {
      $callback($shipping_method, $pane_form['shipping_details'], $pane_values['shipping_details'], $order);
    }
  }
}
