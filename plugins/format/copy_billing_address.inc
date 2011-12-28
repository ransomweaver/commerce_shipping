<?php

/**
 * @file
 * Addressfield format for copying address from billing to shipping.
 */

$plugin = array(
  'title' => t('Copy billing address (required to be activated on billing profile along with the profile you want to copy the address on to. In order to work properly, the two addresses need to be on the same page.).'),
  'format callback' => 'commerce_shipping_copy_billing_address',
  'type' => 'address',
  'weight' => 100,
);

/**
 * Format callback for the addressfield address plugin.
 */
function commerce_shipping_copy_billing_address(&$format, $address, $context = array()) {
  if (!empty($context['field']['field_name']) && $context['field']['field_name'] == 'commerce_customer_address' && $context['mode'] == 'form') {
    if ($context['instance']['bundle'] == 'billing') {
      $format['copy_billing_address'] = array(
        '#type' => 'checkbox',
        '#title' => t('Use billing address as shipping address'),
        '#ajax' => array(
          'callback' => 'commerce_shipping_copy_billing_address_ajax',
          'wrapper' => 'copy-billing-address',
        ),
        '#weight' => 100,
        '#attached' => array(
          'css' => array(drupal_get_path('module', 'commerce_shipping') . '/commerce_shipping.base.css'),
        ),
      );
    }
    else {
      $format['#prefix'] = '<div id="copy-billing-address">';
      commerce_shipping_copy_billing_address_mark_required($format, TRUE, TRUE);
      if (!empty($format['#validate'])) {
        $format['#element_validate'] = array('commerce_shipping_copy_billing_address_validate') + $format['#element_validate'];
      }
      else {
        $format['#element_validate'] = array('commerce_shipping_copy_billing_address_validate');
      }
    }
  }
}

/**
 * Utility function to set required state and save which form elements that
 * eplicitly has a required state set.
 */
function commerce_shipping_copy_billing_address_mark_required(&$form, $required = FALSE, $store_status_changes = FALSE) {
  if (is_array($form)) {
    if ($store_status_changes && !empty($form['#required'])) {
      $form['#copy_billing_address'] = TRUE;
      // On initial build, we set required to FALSE and add it pre render to
      // mark it as required.
      $form['#required'] = FALSE;
      $form['#element_validate'][] = 'commerce_shipping_copy_billing_address_validate_field_required';
      if ($required) {
        $form['#pre_render'][] = 'commerce_shipping_copy_billing_address_pre_render_required';
      }
    }
    if (!empty($form['#prefix']) && strpos($form['#prefix'], 'copy-billing-address') !== FALSE && !$required) {
      $form['#prefix'] = '<div id="copy-billing-address" class="hidden">';
    }
    elseif (!empty($form['#prefix']) && strpos($form['#prefix'], 'copy-billing-address') !== FALSE && $required) {
      $form['#prefix'] = '<div id="copy-billing-address">';
    }
    foreach ($form as &$child) {
      // Recursion.
      if (is_array($child)) {
        commerce_shipping_copy_billing_address_mark_required($child, $required, $store_status_changes);
      }
    }
  }
}

/**
 * AJAX callback, toggle require states and add a class to the wrapping div.
 */
function commerce_shipping_copy_billing_address_ajax(&$form, &$form_state) {
  if (!empty($form['customer_profile_shipping']['commerce_customer_address'])) {
    if (!$form_state['triggering_element']['#value']) {
      commerce_shipping_copy_billing_address_mark_required($form['customer_profile_shipping']['commerce_customer_address'], TRUE);
    }
    else {
      commerce_shipping_copy_billing_address_mark_required($form['customer_profile_shipping']['commerce_customer_address']);
    }
    return $form['customer_profile_shipping']['commerce_customer_address'];
  }
  return;
}

/**
 * Element validate for the shipping address
 * If needed, copy the address from the billing address.
 */
function commerce_shipping_copy_billing_address_validate(&$form, &$form_state) {
  // Check to see if billing and shipping values are present as espected.
  // If not we abort.
  if (!empty($form_state['values']['customer_profile_shipping']['commerce_customer_address']['und'][0]) &&
      is_array($form_state['values']['customer_profile_shipping']['commerce_customer_address']['und'][0]) &&
      !empty($form_state['values']['customer_profile_billing']['commerce_customer_address']['und'][0]) &&
      is_array($form_state['values']['customer_profile_billing']['commerce_customer_address']['und'][0])
  ) {
    // Get the form_state values
    $shipping_values = &$form_state['values']['customer_profile_shipping']['commerce_customer_address']['und'][0];
    $billing_values = $form_state['values']['customer_profile_billing']['commerce_customer_address']['und'][0];
    // Check if we need to copy the billing address.
    if ($billing_values['copy_billing_address']) {
      // Copy the billing address.
      foreach($shipping_values as $key => $value) {
        if (isset($billing_values[$key])) {
          $shipping_values[$key] = $billing_values[$key];
        }
      }
    }
  }
}

/**
 * Element validate for the shipping address required fields.
 * Will mark required fields with normal required error.
 */
function commerce_shipping_copy_billing_address_validate_field_required(&$form, &$form_state) {
  // Validate if copy not selected
  if (
    isset($form_state['values']['customer_profile_billing']['commerce_customer_address']['und'][0]['copy_billing_address']) &&
    empty($form_state['values']['customer_profile_billing']['commerce_customer_address']['und'][0]['copy_billing_address'])
  ) {
    $values = $form_state['values'];
    // Drill down the form_state array and get the actual submitted value.
    foreach ($form['#parents'] as $parent) {
      if (isset($values[$parent])) {
        $values = $values[$parent];
      }
    }
    // Only raise error if we got a non array value and it's empty.
    if (!is_array($values) && empty($values)) {
      if (isset($form['#title'])) {
        form_error($form, t('!name field is required.', array('!name' => $form['#title'])));
      }
      else {
        form_error($form);
      }
    }
  }
}

/**
 * Pre render for form elements. Make non required form elements look required.
 */
function commerce_shipping_copy_billing_address_pre_render_required($element) {
  // This will make the form fields look as though they were required. As they
  // are hidden when not required, this is not a problem.
  $element['#required'] = TRUE;
  return $element;
}
