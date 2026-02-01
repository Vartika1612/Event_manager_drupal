<?php

namespace Drupal\event_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventConfigForm extends FormBase {

  protected ?int $eventId = NULL;
  protected Connection $db;

  public function __construct(Connection $db) {
    $this->db = $db;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function getFormId() {
    return 'event_config_form';
  }

  /**
   * Build form (handles both Add & Edit).
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {

    $event = NULL;
    $this->eventId = $id;

    // Load event data if editing
    if ($id) {
      $event = $this->db->select('event_config', 'e')
        ->fields('e')
        ->condition('id', $id)
        ->execute()
        ->fetchObject();
    }

    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#required' => TRUE,
      '#default_value' => $event ? $event->event_name : '',
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#options' => [
        'Online Workshop' => 'Online Workshop',
        'Hackathon' => 'Hackathon',
        'Conference' => 'Conference',
        'One-day Workshop' => 'One-day Workshop',
      ],
      '#required' => TRUE,
      '#default_value' => $event ? $event->category : '',
    ];

    $form['event_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Date'),
      '#required' => TRUE,
      '#default_value' => $event ? $event->event_date : '',
    ];

    $form['reg_start'] = [
      '#type' => 'date',
      '#title' => $this->t('Registration Start Date'),
      '#required' => TRUE,
      '#default_value' => $event ? $event->reg_start : '',
    ];

    $form['reg_end'] = [
      '#type' => 'date',
      '#title' => $this->t('Registration End Date'),
      '#required' => TRUE,
      '#default_value' => $event ? $event->reg_end : '',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $id ? $this->t('Update Event') : $this->t('Save Event'),
    ];

    return $form;
  }

  /**
   * Submit handler (Insert OR Update).
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    if ($this->eventId) {
      // UPDATE existing event
      $this->db->update('event_config')
        ->fields([
          'event_name' => $form_state->getValue('event_name'),
          'category'   => $form_state->getValue('category'),
          'event_date' => $form_state->getValue('event_date'),
          'reg_start'  => $form_state->getValue('reg_start'),
          'reg_end'    => $form_state->getValue('reg_end'),
        ])
        ->condition('id', $this->eventId)
        ->execute();

      $this->messenger()->addStatus($this->t('Event updated successfully.'));
    }
    else {
      // INSERT new event
      $this->db->insert('event_config')
        ->fields([
          'event_name' => $form_state->getValue('event_name'),
          'category'   => $form_state->getValue('category'),
          'event_date' => $form_state->getValue('event_date'),
          'reg_start'  => $form_state->getValue('reg_start'),
          'reg_end'    => $form_state->getValue('reg_end'),
        ])
        ->execute();

      $this->messenger()->addStatus($this->t('Event saved successfully.'));
    }

    // Redirect back to admin event page
    $form_state->setRedirect('event_manager.config');
  }

}
