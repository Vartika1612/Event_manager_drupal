<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

class EventDeleteForm extends ConfirmFormBase {

  protected Connection $database;
  protected int $eventId;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('database'));
  }

  public function getFormId() {
    return 'event_delete_form';
  }

  public function getQuestion() {
    return $this->t('Are you sure you want to delete this event?');
  }

  public function getCancelUrl() {
    return new Url('event_registration.event_list');
  }

  public function getConfirmText() {
    return $this->t('Delete');
  }

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $this->eventId = $id;
    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->database->delete('event_config')
      ->condition('id', $this->eventId)
      ->execute();

    $this->messenger()->addStatus($this->t('Event deleted successfully.'));
    $form_state->setRedirect('event_registration.event_list');
  }
}
