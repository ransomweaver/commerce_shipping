<?php

namespace Drupal\commerce_shipping\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PackageTypeForm.
 *
 * @package Drupal\commerce_shipping\Form
 */
class PackageTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $commerce_package_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $commerce_package_type->label(),
      '#description' => $this->t("Label for the Package type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $commerce_package_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_shipping\Entity\PackageType::load',
      ],
      '#disabled' => !$commerce_package_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $commerce_package_type = $this->entity;
    $status = $commerce_package_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Package type.', [
          '%label' => $commerce_package_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Package type.', [
          '%label' => $commerce_package_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($commerce_package_type->urlInfo('collection'));
  }

}
