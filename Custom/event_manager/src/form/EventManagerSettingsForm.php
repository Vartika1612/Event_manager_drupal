<?php

namespace Drupal\event_manager\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class EventManagerSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['event_manager.settings'];
  }

  public function getFormId() {
    return 'event_manager_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('event_manager.settings');

    $form['admin_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Admin notification email'),
      '#default_value' => $config->get('admin_email'),
      '#required' => TRUE,
    ];

    $form['notify_admin'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable admin notification'),
      '#default_value' => $config->get('notify_admin'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('event_manager.settings')
      ->set('admin_email', $form_state->getValue('admin_email'))
      ->set('notify_admin', $form_state->getValue('notify_admin'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
