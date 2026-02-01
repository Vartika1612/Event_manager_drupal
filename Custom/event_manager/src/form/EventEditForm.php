<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventEditForm extends FormBase {

  protected Connection $database;
  protected int $eventId;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('database'));
  }

  public function getFormId() {
    return 'event_edit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {

    $this->eventId = $id;

    $event = $this->database->select('event_config', 'e')
      ->fields('e')
      ->condition('id', $id)
      ->execute()
      ->fetchObject();

    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#default_value' => $event->event_name,
      '#required' => TRUE,
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#default_value' => $event->category,
      '#required' => TRUE,
      '#options' => [
        'Online Workshop' => 'Online Workshop',
        'Hackathon' => 'Hackathon',
        'Conference' => 'Conference',
        'One-day Workshop' => 'One-day Workshop',
      ],
    ];

    $form['event_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Date'),
      '#default_value' => $event->event_date,
      '#required' => TRUE,
    ];

    $form['reg_start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Registration Start Date'),
      '#default_value' => $event->reg_start_date,
      '#required' => TRUE,
    ];

    $form['reg_end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Registration End Date'),
      '#default_value' => $event->reg_end_date,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update Event'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->database->update('event_config')
      ->fields([
        'event_name' => $form_state->getValue('event_name'),
        'category' => $form_state->getValue('category'),
        'event_date' => $form_state->getValue('event_date'),
        'reg_start_date' => $form_state->getValue('reg_start_date'),
        'reg_end_date' => $form_state->getValue('reg_end_date'),
      ])
      ->condition('id', $this->eventId)
      ->execute();

    $this->messenger()->addStatus($this->t('Event updated successfully.'));
  }
}
