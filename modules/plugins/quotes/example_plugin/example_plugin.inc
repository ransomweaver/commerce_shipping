<?php

$plugin = array(
  'title' => t('Example plugin'),
  'description' => t('This plugin is used as an example and documentation of commerce_shipping features.'),
  'handler' => array(
    'class' => 'CommerceShippingExample',
    'parent' => 'quote_base'
  ),
);
