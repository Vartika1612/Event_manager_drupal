<?php

namespace Drupal\event_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\event_manager\Service\EventService;
use Drupal\event_manager\Service\MailService;
use Drupal\Core\Database\Connection;

class EventRegistrationForm extends FormBase {

  protected EventService $eventService;
  protected MailService $mailService;
  protected Connection $db;

  public function __construct(
    EventService $eventService,
    MailService $mailService,
    Connection $db
  ) {
    $this->eventService = $eventService;
    $this->mailService = $mailService;
    $this->db = $db;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_manager.event_service'),
      $container->get('event_manager.mail_service'),
      $container->get('database')
    );
  }

  public function getFormId() {
    return 'event_registration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $categories = array_combine(
      $this->eventService->getCategories(),
      $this->eventService->getCategories()
    );

    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => 'Full Name',
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => 'Email Address',
      '#required' => TRUE,
    ];

    $form['college'] = [
      '#type' => 'textfield',
      '#title' => 'College Name',
      '#required' => TRUE,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => 'Department',
      '#required' => TRUE,
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => 'Category',
      '#options' => ['' => '- Select -'] + $categories,
      '#ajax' => [
        'callback' => '::updateDates',
        'wrapper' => 'date-wrapper',
      ],
      '#required' => TRUE,
    ];

    $form['event_date_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'date-wrapper'],
    ];

    $selected_category = $form_state->getValue('category');
    $dates = [];

    if ($selected_category) {
      $date_values = $this->eventService->getDates($selected_category);
      foreach ($date_values as $date) {
        $dates[$date] = $date;
      }
    }

    $form['event_date_wrapper']['event_date'] = [
      '#type' => 'select',
      '#title' => 'Event Date',
      '#options' => ['' => '- Select -'] + $dates,
      '#ajax' => [
        'callback' => '::updateEvents',
        'wrapper' => 'event-wrapper',
      ],
      '#required' => TRUE,
    ];

    $form['event_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-wrapper'],
    ];

    $events = [];
    $selected_date = $form_state->getValue('event_date');

    if ($selected_category && $selected_date) {
      $event_rows = $this->eventService->getEvents($selected_category, $selected_date);
      foreach ($event_rows as $event) {
        $events[$event->id] = $event->event_name;
      }
    }

    $form['event_wrapper']['event_id'] = [
      '#type' => 'select',
      '#title' => 'Event Name',
      '#options' => ['' => '- Select -'] + $events,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Register',
    ];

    return $form;
  }

  public function updateDates(array &$form, FormStateInterface $form_state) {
    return $form['event_date_wrapper'];
  }

  public function updateEvents(array &$form, FormStateInterface $form_state) {
    return $form['event_wrapper'];
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

    $text_fields = ['full_name', 'college', 'department'];

    foreach ($text_fields as $field) {
      if (!preg_match('/^[a-zA-Z\s]+$/', $form_state->getValue($field))) {
        $form_state->setErrorByName($field, 'Special characters are not allowed.');
      }
    }

    if (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', 'Invalid email format.');
    }

    if ($this->eventService->isDuplicate(
      $form_state->getValue('email'),
      $form_state->getValue('event_id')
    )) {
      $form_state->setErrorByName('email', 'You are already registered for this event.');
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->db->insert('event_registration')->fields([
      'full_name' => $form_state->getValue('full_name'),
      'email' => $form_state->getValue('email'),
      'college' => $form_state->getValue('college'),
      'department' => $form_state->getValue('department'),
      'event_id' => $form_state->getValue('event_id'),
      'created' => time(),
    ])->execute();

    $params['body'] = "Thank you {$form_state->getValue('full_name')} for registering.";

    $this->mailService->send($form_state->getValue('email'), $params);

    $this->messenger()->addMessage('Registration successful.');
  }
}
