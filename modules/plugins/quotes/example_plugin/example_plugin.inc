<?php

$plugin = array(
  // Default ctools properties.
  'title' => t('Example plugin'),
  'description' => t('This plugin is used as an example and documentation of commerce_shipping features.'),
  'handler' => array(
    'class' => 'CommerceShippingExample',
    'parent' => 'quote_base'
  ),
  // Special Commerce Shipping properties.
  // Display title, used for many frontend displays. Default value is the
  // title property.
  'display_title' => t('Example shipping method'),
  // The shipping label for select form, default value is display_title.
  'shipping_label' => t('Example shipping method radio button'),
  // Boolean indicator indicating if a rule and action should be created.
  'create_rule' => TRUE,
  // Default settings as defined through the settings form on the rules action.
  'settings' => array(
    'shipping_price' => 42,
  ),
);
