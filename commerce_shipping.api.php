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
 *   rates for this shipping method should be defined enabled; defaults to TRUE
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
 * - callbacks: an array of callback function names with the following keys:
 *   - rate: the function used to generate the price array for the base rate of
 *     the shipping service; rate callbacks are passed two parameters,
 *     ($shipping_service, $order)
 * Values for the following keys are optional:
 * - display_title: the title used for the service on the front end; defaults to
 *   the title
 * - description: a basic description of the service
 * - rules_component: boolean indicating whether or not a default Rules
 *   component should be created to be used for enabling this service on a given
 *   order; defaults to TRUE
 * - price_component: the name of the price component used in the unit price of
 *   shipping line items for the service; defaults to the name of the shipping
 *   service, which is important for data selection in Rules that may need to
 *   use a variable to specify a price component... accordingly, beware of name
 *   conflicts in your service names with other price components
 * - callbacks: additional callback function names may be specified for the
 *   service with the following keys:
 *   - details_form: the function used to generate a details form array for the
 *     shipping service to collect additional information related to the service
 *     on the checkout / admin forms; details form callbacks are passed five
 *     parameters, ($pane_form, $pane_values, $checkout_pane, $order, $shipping_service)
 *   - details_form_validate: the function used to validate input on the service
 *     details form; details form validate callbacks are passed five parameters,
 *     ($details_form, $details_values, $shipping_service, $order, $form_parents)
 *   - details_form_submit: the function used to perform any additional
 *     processing required on a shipping line item in light of the details form;
 *     details form submit callbacks are passed three parameters,
 *     ($details_form, $details_values, $line_item)
 * When loaded, a shipping service info array will also contain a module key
 * whose value is the name of the module that defined the service.
 */
function commerce_shipping_service_info() {
  // No example.
}
