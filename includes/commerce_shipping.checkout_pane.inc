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

  // Sort the payment methods array by the enabling Rules' weight values.
  uasort($order->shipping_methods, 'drupal_sort_weight');

  // Generate an array of shipping method options for the checkout form.
  $options = array();

  foreach ($order->shipping_methods as $instance_id => $method_info) {
    // Ensure we've received a valid shipping method.
    if ($shipping_method = commerce_shipping_method_load($method_info['method_id'])) {
      $options[$instance_id] = $shipping_method['display_title'];
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

    // Add the shipping method specific form elements.
    $method_info = $order->shipping_methods[$pane_form['shipping_method']['#default_value']];
    $shipping_method = commerce_shipping_method_load($method_info['method_id']);
    $shipping_shipping->settings = $method_info['settings'];

    if ($callback = commerce_shipping_method_callback($shipping_method, 'submit_form')) {
      $pane_form['shipping_details'] = $callback($shipping_method, $pane_values, $checkout_pane, $order);
    }
    else {
      $pane_form['shipping_details'] = array();
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

  // Only attempt validation if there were shipping methods available.
  if ($pane_values['shipping_methods']) {
    // If the selected shipping method was changed...
    if ($pane_values['shipping_method'] != $pane_form['shipping_method']['#default_value']) {
      // And the newly selected method has a valid form callback...
      if ($shipping_method = commerce_shipping_method_instance_load($pane_values['shipping_method'])) {
        if (commerce_shipping_method_callback($shipping_method, 'submit_form')) {
          // Fail validation so the form is rebuilt to include the shipping method
          // specific form elements.
          return FALSE;
        }
      }
    }

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
  // Nothing to validate.
  return TRUE;
}

/**
 * shipping pane: submit callback.
 */
function commerce_shipping_pane_checkout_form_submit($form, &$form_state, $checkout_pane, $order) {
  // In case this order already have shipping line items, we need to remove them.
  $order_wrapper = entity_metadata_wrapper('commerce_order', $order);
  $line_items = array();
  foreach ($order_wrapper->commerce_line_items as $key => $line_item_wrapper) {
    if ($line_item_wrapper->type->value() != 'shipping') {
      $line_items[] = $line_item_wrapper->value();
      //$order_wrapper->commerce_line_items->offsetUnset($key);
    }
  }
  $order_wrapper->commerce_line_items = $line_items;

  $pane_form = $form[$checkout_pane['pane_id']];
  $pane_values = $form_state['values'][$checkout_pane['pane_id']];

  // Only process if there were shipping methods available.
  if ($pane_values['shipping_methods']) {
    $order->data['shipping_method'] = $pane_values['shipping_method'];

    // Let the shipping method calculate the shipping price.
    $shipping_method = commerce_shipping_method_instance_load($pane_values['shipping_method']);
    if ($callback = commerce_shipping_method_callback($shipping_method, 'calculate_shipping')) {
      // Setup variables for the calcualte_shipping function.
      $settings = array(
        'checkout_settings' => $form_state['values']['commerce_shipping']['shipping_details'],
        'rules_settings' => $shipping_method['settings'],
      );
      $default_currency_code = commerce_default_currency();
      if ($balance = commerce_payment_order_balance($order)) {
        $default_currency_code = $balance['currency_code'];
      }
      $shipping_line_items = $callback($settings, $order, $default_currency_code);
      // Loop through the result and create line items if possible.
      if (is_array($shipping_line_items)) {
        foreach ($shipping_line_items as $shipping_line_item) {
          $line_item = commerce_shipping_line_item_new($shipping_method);
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
          $line_item_wrapper->commerce_unit_price->data = commerce_price_component_add(
            $line_item_wrapper->commerce_unit_price->value(),
            'base_price',
            $line_item_wrapper->commerce_unit_price->value(),
            TRUE
          );
          $line_item_wrapper->save();
          $order_wrapper->commerce_line_items[] = $line_item_wrapper->value();
        }
      }
    }

    // Lastly we save the order.
    commerce_order_save($order);

    // This is not actually needed, but for flexibility allow shipping methods
    // to react on the form submission.
    if ($callback = commerce_shipping_method_callback($shipping_method, 'submit_form_submit')) {
      $callback($shipping_method, $pane_form['shipping_details'], $pane_values['shipping_details'], $order);
    }
  }
}

/**
 * Checkout pane callback: returns the cart contents pane's settings form.
 */
function commerce_shipping_pane_settings_form($checkout_pane) {
  $form = array();

  // Build an options array of Views available for the cart contents pane.
  $options = array();

   // Generate an option list from all user defined and module defined views.
   foreach (views_get_all_views() as $view_id => $view_value) {
    // Only include line item Views.
    if ($view_value->base_table == 'commerce_order') {
       foreach ($view_value->display as $display_id => $display_value) {
        $options[check_plain($view_id)][$view_id .'|'. $display_id] = check_plain($display_value->display_title);
      }
     }
   }

  $form['commerce_shipping_pane_review_view'] = array(
    '#type' => 'select',
    '#title' => t('Shipping review View'),
    '#description' => t('Specify the View to use in the shipping summery view.'),
    '#options' => $options,
    '#default_value' => variable_get('commerce_shipping_pane_review_view', 'commerce_shipping_summary|default'),
  );

  return $form;
}

/**
 * Checkout pane callback: returns the cart contents review data for the
 *   Review checkout pane.
 */
function commerce_shipping_pane_review($form, $form_state, $checkout_pane, $order) {
  // Extract the View and display keys from the cart contents pane setting.
  list($view_id, $display_id) = explode('|', variable_get('commerce_shipping_pane_review_view', 'commerce_shipping_summary|default'));

  return commerce_embed_view($view_id, $display_id, array($order->order_id));
}
