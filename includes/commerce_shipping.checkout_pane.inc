<?php

/**
 * @file
 * Callback functions for the shipping module's checkout panes.
 */


/**
 * Checkout pane callback: builds a shipping quote selection form.
 */
function commerce_shipping_pane_checkout_form($form, &$form_state, $checkout_pane, $order) {
  $pane_form = array();

  // TODO: integrate with Commerce Physical Product to ensure an order contains
  // physical products before attempting to quote shipping rates.

  // Collect shipping rates for the order.
  commerce_shipping_collect_rates($order);

  // Generate an array of shipping service rate options.
  $options = commerce_shipping_service_rate_options($order);

  // If at least one shipping option is available...
  if (!empty($options)) {
    // Store the shipping methods in the form for validation purposes.
    $pane_form['shipping_rates'] = array(
      '#type' => 'value',
      '#value' => $order->shipping_rates,
    );

    // Add a radios element to let the customer select a shipping service.
    $pane_form['shipping_service'] = array(
      '#type' => 'radios',
      '#options' => $options,
      '#ajax' => array(
        'callback' => 'commerce_shipping_pane_service_details_refresh',
        'wrapper' => 'commerce-shipping-service-details',
      ),
    );

    // Find the default shipping method using either the pre-selected value
    // stored as a line item on the order or the first available method.
    $pane_values = !empty($form_state['values']) ? $form_state['values'][$checkout_pane['pane_id']] : array();

    // First check for a line item.
    $order_wrapper = entity_metadata_wrapper('commerce_order', $order);

    foreach ($order_wrapper->commerce_line_items as $delta => $line_item_wrapper) {
      if ($line_item_wrapper->type->value() == 'shipping') {
        $default_value = $line_item_wrapper->commerce_shipping_service->value();
        break;
      }
    }

    if (empty($default_value)) {
      if (!empty($pane_values['shipping_service']) && !empty($options[$pane_values['shipping_service']])) {
        $default_value = $pane_values['shipping_service'];
      }
      else {
        $default_value = key($options);
      }
    }

    // Set the default value for the shipping method radios.
    $pane_form['shipping_service']['#default_value'] = $default_value;

    // TODO: collect additional options for the shipping service.
    $pane_form['shipping_details'] = array();

    $shipping_service = commerce_shipping_service_load($default_value);

    if (!empty($shipping_service['details_callback'])) {
      $pane_form['shipping_details'] = $shipping_service['details_callback']($pane_values, $checkout_pane);

      $pane_form['shipping_details']['#prefix'] = '<div id="commerce-shipping-service-details">';
      $pane_form['shipping_details']['#suffix'] = '</div>';
    }
  }

  return $pane_form;
}

/**
 * Returns the shipping details element for display via AJAX.
 */
function commerce_shipping_pane_service_details_refresh($form, $form_state) {
  return $form['commerce_shipping']['shipping_details'];
}

/**
 * Checkout pane callback: validate the shipping service selection and details.
 */
function commerce_shipping_pane_checkout_form_validate($form, &$form_state, $checkout_pane, $order) {
  $pane_form = $form[$checkout_pane['pane_id']];
  $pane_values = $form_state['values'][$checkout_pane['pane_id']];
  $class = $pane_form['commerce_shipping_plugin']['#value'];
  if (!isset($pane_values['shipping_details'])) {
    $pane_values['shipping_details'] = array();
  }

  // Only attempt validation if there were shipping methods available.
  if ($pane_values['shipping_methods']) {
    // If the selected shipping method was changed, we always need to rebuild
    // to update the plugin class.
    if ($pane_values['shipping_method'] != $pane_form['shipping_method']['#default_value']) {
      return FALSE;
    }

    // Run the validation that the plugin class inplements.
    $result = $class->submit_form_validate($pane_form['shipping_details'], $pane_values['shipping_details'], array($checkout_pane['pane_id'], 'shipping_details'), $order);
    return $result === FALSE ? FALSE : TRUE;
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
  $class = $pane_form['commerce_shipping_plugin']['#value'];
  $rule_ids = explode('|', $pane_values['shipping_method']);
  $method_id = $rule_ids[0];

  // Only process if there were shipping methods available.
  if ($pane_values['shipping_methods']) {
    $order->data['shipping_method'] = $pane_values['shipping_method'];

    $default_currency_code = commerce_default_currency();
    if ($balance = commerce_payment_order_balance($order)) {
      $default_currency_code = $balance['currency_code'];
    }
    $form_values = isset($form_state['values']['commerce_shipping']['shipping_details']) ? $form_state['values']['commerce_shipping']['shipping_details'] : array();

    // Let the shipping method calculate the shipping price.
    $plugin = commerce_shipping_plugin_get_plugin('quotes', $method_id);
    $shipping_line_items = $class->calculate_quote($default_currency_code, $form_values, $order, $form, $form_state);

    // Loop through the result and create line items if possible.
    if (is_array($shipping_line_items)) {
      foreach ($shipping_line_items as $shipping_line_item) {
        $line_item = commerce_shipping_line_item_new($plugin);
        $line_item->order_id = $order->order_id;
        $line_item_wrapper = entity_metadata_wrapper('commerce_line_item', $line_item);
        if (is_numeric($shipping_line_item)) {
          $price = array(
            'amount' => $shipping_line_item,
          );
        }
        elseif (!empty($shipping_line_item['amount'])) {
          $price = array(
            'amount' => $shipping_line_item['amount'],
            'currency_code' => !empty($shipping_line_item['currency_code']) ? $shipping_line_item['currency_code'] : $default_currency_code,
          );
        }
        elseif (!empty($shipping_line_item['price'])) {
            $price = $shipping_line_item['price'];
        }
        if (isset($shipping_line_item['label'])) {
          $line_item_wrapper->line_item_label = $shipping_line_item['label'];
        }
        if (isset($shipping_line_item['quantity'])) {
          $line_item_wrapper->quantity = $shipping_line_item['quantity'];
        }

        // Require that the price is set.
        if (isset($price)) {
          // Add component if needed
          if (empty($price['data']['components'])) {
            $price_component = 'quote';
            if (!empty($plugin['price_component'])) {
              $price_component = 'quote_' . $plugin['name'];
            }
            $price['data'] = commerce_price_component_add(
              $price,
              $price_component,
              $price,
              TRUE,
              FALSE
            );
          }
          // Make sure the currency code is set.
          if (empty($price['currency_code'])) {
            $price['currency_code'] = $default_currency_code;
          }
          $line_item_wrapper->commerce_unit_price = $price;
          rules_invoke_all('commerce_shipping_calculate', $line_item);

          $line_item_wrapper->save();
          $order_wrapper->commerce_line_items[] = $line_item_wrapper->value();
        }
      }
    }

    // Lastly we save the order.
    commerce_order_save($order);

    // This is not actually needed, but for flexibility allow shipping methods
    // to react on the form submission.
    $class->shipping_items_created($pane_form['shipping_details'], $pane_values['shipping_details'], $order);
  }
}
