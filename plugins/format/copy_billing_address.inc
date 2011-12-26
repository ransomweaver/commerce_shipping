<?php

/**
 * @file
 * Addressfield format for copying address from billing to shipping.
 */

$plugin = array(
  'title' => t('Copy billing address (required to be activated on billing profile along with the profile you want to copy the address on to. In order to work properly, the two addresses need to be on the same page and the user needs to have JavaScript enabled.).'),
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
        '#default_value' => isset($_SESSION['commerce_shipping_copy_billing_address']) ? $_SESSION['commerce_shipping_copy_billing_address'] : 0,
        '#ajax' => array(
          'callback' => 'commerce_shipping_copy_billing_address_ajax',
          'wrapper' => 'copy-billing-address',
        ),
        '#attached' => array(
          'css' => array(drupal_get_path('module', 'commerce_shipping') . '/theme/commerce_shipping.base.css'),
        ),
        '#weight' => 100,
      );
    }
    else {
      $format['#prefix'] = '<div id="copy-billing-address">';
      $required = isset($_SESSION['commerce_shipping_copy_billing_address']) ? !$_SESSION['commerce_shipping_copy_billing_address'] : TRUE;
      commerce_shipping_copy_billing_address_make_unrequired($format, $required, TRUE);
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
function commerce_shipping_copy_billing_address_make_unrequired(&$form, $required = FALSE, $store_status_changes = FALSE) {
  if (is_array($form)) {
    if ($store_status_changes && isset($form['#required'])) {
      $form['#copy_billing_address'] = TRUE;
      $form['#required'] = $required;
    }
    if (!$store_status_changes && !empty($form['#copy_billing_address'])) {
      $form['#required'] = $required;
    }
    if (!empty($form['#prefix']) && strpos($form['#prefix'], 'copy-billing-address') !== FALSE && !$required) {
      $form['#prefix'] = '<div id="copy-billing-address" class="hidden">';
    }
    foreach ($form as &$child) {
      // Recursion.
      if (is_array($child)) {
        commerce_shipping_copy_billing_address_make_unrequired($child, $required, $store_status_changes);
      }
    }
  }
}

/**
 * AJAX callback, toggle require states and add a class to the wrapping div.
 */
function commerce_shipping_copy_billing_address_ajax(&$form, &$form_state) {
  if (!empty($form['customer_profile_shipping']['commerce_customer_address'])) {
    // Need to save state in SESSION, to keep track of it.
    $_SESSION['commerce_shipping_copy_billing_address'] = $form_state['triggering_element']['#value'];
    if (!$form_state['triggering_element']['#value']) {
      commerce_shipping_copy_billing_address_make_unrequired($form['customer_profile_shipping']['commerce_customer_address'], TRUE);
    }
    else {
      commerce_shipping_copy_billing_address_make_unrequired($form['customer_profile_shipping']['commerce_customer_address']);
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
  if ($_SESSION['commerce_shipping_copy_billing_address']) {
    if (!empty($form_state['values']['customer_profile_shipping']['commerce_customer_address']['und'][0]) &&
        is_array($form_state['values']['customer_profile_shipping']['commerce_customer_address']['und'][0]) &&
        !empty($form_state['values']['customer_profile_billing']['commerce_customer_address']['und'][0]) &&
        is_array($form_state['values']['customer_profile_billing']['commerce_customer_address']['und'][0])
    ) {
      foreach($form_state['values']['customer_profile_shipping']['commerce_customer_address']['und'][0] as $key => $value) {
        $form_state['values']['customer_profile_shipping']['commerce_customer_address']['und'][0][$key] = $form_state['values']['customer_profile_billing']['commerce_customer_address']['und'][0][$key];
      }
    }
  }
}

