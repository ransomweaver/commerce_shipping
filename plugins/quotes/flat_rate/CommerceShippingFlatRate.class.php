<?php

class CommerceShippingFlatRate extends CommerceShippingQuote {
  public function settings_form(&$form, $rules_settings) {
    $form['shipping_price'] = array(
      '#type' => 'textfield',
      '#title' => t('Shipping rate'),
      '#description' => t('Configure what the rate should be.'),
      '#default_value' => is_array($rules_settings) && isset($rules_settings['shipping_price']) ? $rules_settings['shipping_price'] : 42,
      '#element_validate' => array('rules_ui_element_decimal_validate'),
    );

    $form['rate_type'] = array(
      '#type' => 'select',
      '#title' => t('Rate type'),
      '#description' => t('Select what should be counted when calculating the shipping quote.'),
      '#default_value' => is_array($rules_settings) && isset($rules_settings['rate_type']) ? $rules_settings['rate_type'] : 'product',
      '#options' => array(
        'product' => t('Product'),
        'line_item' => t('Product types (line items)'),
      ),
    );

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Line item label'),
      '#default_value' => is_array($rules_settings) && isset($rules_settings['label']) ? $rules_settings['label'] : t('Flat rate shipping'),
    );
  }

  public function calculate_quote($currency_code, $form_values = array(), $order = NULL) {
    if (empty($order)) {
      $order = $this->order;
    }
    $settings = $this->settings;
    $order_wrapper = entity_metadata_wrapper('commerce_order', $order);

    $quantity = 0;
    foreach ($order_wrapper->commerce_line_items as $line_item_wrapper) {
      if ($line_item_wrapper->type->value() == 'product') {
        if ($settings['rate_type'] == 'product') {
          $quantity += $line_item_wrapper->quantity->value();
        }
        elseif ($settings['rate_type'] == 'line_item') {
          $quantity += 1;
        }
      }
    }
    $shipping_line_items = array();
    $shipping_line_items[] = array(
      'amount' => commerce_currency_decimal_to_amount($settings['shipping_price'], $currency_code),
      'currency_code' => $currency_code,
      'label' => $settings['label'],
      'quantity' => $quantity,
    );
    return $shipping_line_items;
  }
}