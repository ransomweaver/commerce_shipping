<?php

namespace Drupal\commerce_shipping\Form;

use Drupal\commerce\Form\CommercePluginEntityFormBase;
use Drupal\commerce_shipping\ShippingMethodManager;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ShippingMethodForm extends CommercePluginEntityFormBase {

  /**
   * The shipping method plugin manager.
   *
   * @var \Drupal\commerce_shipping\ShippingMethodManager
   */
  protected $pluginManager;

  /**
   * Constructs a new ShippingMethodForm object.
   *
   * @param \Drupal\commerce_shipping\ShippingMethodManager $plugin_manager
   *   The shipping method plugin manager.
   */
  public function __construct(ShippingMethodManager $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.commerce_shipping_method')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\commerce_shipping\Entity\ShippingMethodInterface $shipping_method */
    $shipping_method = $this->entity;
    $plugins = array_map(function ($definition) {
      return $definition['label'];
    }, $this->pluginManager->getDefinitions());

    // Use the first available plugin as the default value.
    if (!$shipping_method->getPluginId()) {
      $plugin_ids = array_keys($plugins);
      $plugin = reset($plugin_ids);
      $shipping_method->setPluginId($plugin);
    }
    // The form state will have a plugin value if #ajax was used.
    $plugin = $form_state->getValue('plugin', $shipping_method->getPluginId());

    $wrapper_id = Html::getUniqueId('shipping-method-form');
    $form['#tree'] = TRUE;
    $form['#prefix'] = '<div id="' . $wrapper_id . '">';
    $form['#suffix'] = '</div>';

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $shipping_method->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $shipping_method->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_shipping\Entity\ShippingMethod::load',
      ],
    ];
    $form['plugin'] = [
      '#type' => 'radios',
      '#title' => $this->t('Plugin'),
      '#options' => $plugins,
      '#default_value' => $plugin,
      '#required' => TRUE,
      '#disabled' => !$shipping_method->isNew(),
      '#ajax' => [
        'callback' => '::ajaxRefresh',
        'wrapper' => $wrapper_id,
      ],
    ];
    $form['configuration'] = [
      '#parents' => ['configuration'],
    ];
    $form['configuration'] = $shipping_method->getPlugin()->buildConfigurationForm($form['configuration'], $form_state);
    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $shipping_method->status(),
    ];

    return $this->protectPluginIdElement($form);
  }

  /**
   * Ajax callback.
   */
  public static function ajaxRefresh(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    /** @var \Drupal\commerce_shipping\Entity\ShippingMethodInterface $shipping_method */
    $shipping_method = $this->entity;
    $shipping_method->getPlugin()->validateConfigurationForm($form['configuration'], $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\commerce_shipping\Entity\ShippingMethodInterface $shipping_method */
    $shipping_method = $this->entity;
    $shipping_method->getPlugin()->submitConfigurationForm($form['configuration'], $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();
    drupal_set_message($this->t('Saved the %label shipping method.', ['%label' => $this->entity->label()]));
    $form_state->setRedirect('entity.commerce_shipping_method.collection');
  }

}
