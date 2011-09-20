<?php

/**
 * Defines a shipping method.
 *
 * @return
 * An associative array of shipping method info arrays keyed by machine-name.
 * Each info array must contain values for the following keys:
 * - title: the human readable title of the shipping method
 * Values for the following keys are optional:
 * - display_title: the title used for the method on the front end; defaults to
 *   the title
 * - description: a basic description of the service
 * - active: boolean indicating whether or not the default Rule for collecting
 *   rates for this shipping method should be defined enabled; defaults to FALSE
 * When loaded, a shipping service info array will also contain a module key
 * whose value is the name of the module that defined the service.
 */
function commerce_shipping_method_info() {
  // No example.
}

/**
 * Defines a shipping service.
 *
 * @return
 * An associative array of shipping service info arrays keyed by machine-name.
 * Each info array must contain values for the following keys:
 * - title: the human readable title of the shipping service
 * - shipping_method: the machine-name of the shipping method the service is for
 * - rate_callback: the name of the function used to generate the price array
 *   for the base rate of the shipping service; rate callbacks are passed two
 *   parameters, the shipping service info array and an order
 * Values for the following keys are optional:
 * - display_title: the title used for the service on the front end; defaults to
 *   the title
 * - description: a basic description of the service
 * - rules_component: boolean indicating whether or not a default Rules
 *   component should be created to be used for enabling this service on a given
 *   order; defaults to TRUE
 * - price_component: the name of the price component used in the unit price of
 *   shipping line items for the service; defaults to the pattern
 *   'shipping|[shipping-service-name]'
 * When loaded, a shipping service info array will also contain a module key
 * whose value is the name of the module that defined the service.
 */
function commerce_shipping_service_info() {
  // No example.
}
