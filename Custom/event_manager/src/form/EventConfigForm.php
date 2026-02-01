<?php

namespace Drupal\event_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Event Configuration Form (Create Event).
 */
class EventConfigForm extends FormBase {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * Constructs the form.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#required' => TRUE,
      '#options' => [
        '' => $this->t('- Select -'),
        'Online Workshop' => $this->t('Online Workshop'),
        'Hackathon' => $this->t('Hackathon'),
        'Conference' => $this->t('Conference'),
        'One-day Workshop' => $this->t('One-day Workshop'),
      ],
    ];

    $form['event_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Date'),
      '#required' => TRUE,
    ];

    $form['reg_start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Registration Start Date'),
      '#required' => TRUE,
    ];

    $form['reg_end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Registration End Date'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Event'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $event_name = $form_state->getValue('event_name');

    // Prevent special characters in Event Name.
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $event_name)) {
      $form_state->setErrorByName(
        'event_name',
        $this->t('Special characters are not allowed in Event Name.')
      );
    }

    $event_date = strtotime($form_state->getValue('event_date'));
    $start_date = strtotime($form_state->getValue('reg_start_date'));
    $end_date = strtotime($form_state->getValue('reg_end_date'));

    // Registration start date must be before end date.
    if ($start_date > $end_date) {
      $form_state->setErrorByName(
        'reg_end_date',
        $this->t('Registration end date must be after start date.')
      );
    }

    // Event date must be after registration end date.
    if ($event_date < $end_date) {
      $form_state->setErrorByName(
        'event_date',
        $this->t('Event date must be after registration end date.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->database->insert('event_config')
      ->fields([
        'event_name' => $form_state->getValue('event_name'),
        'category' => $form_state->getValue('category'),
        'event_date' => $form_state->getValue('event_date'),
        'reg_start_date' => $form_state->getValue('reg_start_date'),
        'reg_end_date' => $form_state->getValue('reg_end_date'),

      ])
      ->execute();

    $this->messenger()->addStatus(
      $this->t('Event configuration saved successfully.')
    );

    $form_state->setRedirect('<current>');
  }

}
