<?php

class CommerceShippingFlatRate extends CommerceShippingQuote {
  public function settings_form(&$form, $rules_settings) {
    $currencies = commerce_currencies(TRUE);

    $form['shipping_price'] = array(
      '#type' => 'textfield',
      '#title' => t('Shipping rate'),
      '#description' => t('Configure what the rate should be.'),
      '#default_value' => is_array($rules_settings) && isset($rules_settings['shipping_price']) ? $rules_settings['shipping_price'] : 42,
      '#element_validate' => array('rules_ui_element_decimal_validate'),
    );

    if (count($currencies) > 1) {
      $form['shipping_price']['#title'] = t('Default rate');
      $form['shipping_price']['#description'] = t("Note multi currency doesn't work well with commerce yet, due some bad price handlling in commerce.");
      $form['shipping_rates'] = array(
        '#type' => 'fieldset',
        '#title' => t('Shipping rates'),
        '#collapsed' => TRUE,
      );

      foreach ($currencies as $currency_code => $currency) {
        $form['shipping_rates'][$currency_code] = array(
          '#type' => 'textfield',
          '#title' => t('Shipping rate (@code)', array('@code' => $currency_code)),
          '#description' => t('Configure what the rate should be.'),
          '#default_value' => is_array($rules_settings) && isset($rules_settings['shipping_rates'][$currency_code]) ? $rules_settings['shipping_rates'][$currency_code] : 42,
          '#element_validate' => array('rules_ui_element_decimal_validate'),
        );
      }
    }

    $form['rate_type'] = array(
      '#type' => 'select',
      '#title' => t('Rate type'),
      '#description' => t('Select what should be counted when calculating the shipping quote.'),
      '#default_value' => is_array($rules_settings) && isset($rules_settings['rate_type']) ? $rules_settings['rate_type'] : 'product',
      '#options' => array(
        'product' => t('Product'),
        'line_item' => t('Product types (line items)'),
        'order' => t('Order'),
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
    if (isset($settings['shipping_rates'][$currency_code]) && $settings['shipping_rates'][$currency_code]) {
      $amount = $settings['shipping_rates'][$currency_code];
    }
    else {
      $amount = $settings['shipping_price'];
    }

    $quantity = 0;
    foreach ($order_wrapper->commerce_line_items as $line_item_wrapper) {
      if ($line_item_wrapper->type->value() == 'product') {
        if ($settings['rate_type'] == 'product') {
          $quantity += $line_item_wrapper->quantity->value();
        }
        elseif ($settings['rate_type'] == 'line_item') {
          $quantity += 1;
        }
        elseif ($settings['rate_type'] == 'order') {
          $quantity = 1;
        }
      }
    }
    $shipping_line_items = array();
    $shipping_line_items[] = array(
      'amount' => commerce_currency_decimal_to_amount($amount, $currency_code),
      'currency_code' => $currency_code,
      'label' => $settings['label'],
      'quantity' => $quantity,
    );
    return $shipping_line_items;
  }
}