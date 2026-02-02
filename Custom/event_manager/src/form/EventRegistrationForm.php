<?php

namespace Drupal\event_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventRegistrationForm extends FormBase {

  protected Connection $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function getFormId() {
    return 'event_manager_event_registration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    /* ---------- User details ---------- */

    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
    ];

    $form['college_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('College Name'),
      '#required' => TRUE,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#required' => TRUE,
    ];

    /* ---------- Category ---------- */

    $categories = $this->database->select('event_config', 'e')
      ->fields('e', ['category'])
      ->distinct()
      ->execute()
      ->fetchCol();

    $options = ['' => $this->t('- Select -')];
    foreach ($categories as $category) {
      $options[$category] = $category;
    }

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#options' => $options,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateEventDates',
        'wrapper' => 'event-date-wrapper',
      ],
    ];

    /* ---------- Event Date ---------- */

    $form['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => $this->getEventDates($form_state->getValue('category')),
      '#empty_option' => $this->t('- Select -'),
      '#required' => TRUE,
      '#prefix' => '<div id="event-date-wrapper">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::updateEventNames',
        'wrapper' => 'event-name-wrapper',
      ],
    ];

    /* ---------- Event Name ---------- */

    $form['event_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#options' => $this->getEventNames(
        $form_state->getValue('category'),
        $form_state->getValue('event_date')
      ),
      '#empty_option' => $this->t('- Select -'),
      '#required' => TRUE,
      '#prefix' => '<div id="event-name-wrapper">',
      '#suffix' => '</div>',
    ];

    /* ---------- Submit ---------- */

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    ];

    return $form;
  }

  /* ---------- AJAX callbacks ---------- */

  public function updateEventDates(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return $form['event_date'];
  }

  public function updateEventNames(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return $form['event_id'];
  }

  /* ---------- Helpers ---------- */

  private function getEventDates($category) {
    if (empty($category)) {
      return [];
    }

    $today = date('Y-m-d');

    return $this->database->select('event_config', 'e')
      ->fields('e', ['event_date'])
      ->condition('category', $category)
      ->condition('reg_start_date', $today, '<=')
      ->condition('reg_end_date', $today, '>=')
      ->distinct()
      ->execute()
      ->fetchAllKeyed(0, 0);
  }

  private function getEventNames($category, $event_date) {
    if (empty($category) || empty($event_date)) {
      return [];
    }

    $today = date('Y-m-d');

    $query = $this->database->select('event_config', 'e')
      ->fields('e', ['id', 'event_name'])
      ->condition('category', $category)
      ->condition('event_date', $event_date)
      ->condition('reg_start_date', $today, '<=')
      ->condition('reg_end_date', $today, '>=')
      ->execute();

    $options = [];
    foreach ($query as $row) {
      $options[$row->id] = $row->event_name;
    }
    return $options;
  }

  /* ---------- Validation ---------- */

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $exists = $this->database->select('event_registration', 'r')
      ->fields('r', ['id'])
      ->condition('email', $form_state->getValue('email'))
      ->condition('event_id', $form_state->getValue('event_id'))
      ->execute()
      ->fetchField();

    if ($exists) {
      $form_state->setErrorByName('email',
        $this->t('You have already registered for this event.')
      );
    }
  }

  /* ---------- Submit ---------- */

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->database->insert('event_registration')
      ->fields([
        'full_name' => $form_state->getValue('full_name'),
        'email' => $form_state->getValue('email'),
        'college_name' => $form_state->getValue('college_name'),
        'department' => $form_state->getValue('department'),
        'category' => $form_state->getValue('category'),
        'event_date' => $form_state->getValue('event_date'),
        'event_id' => $form_state->getValue('event_id'),
        'created' => time(),
      ])
      ->execute();

    $this->messenger()->addStatus(
      $this->t('You have successfully registered for the event.')
    );
  }
}
