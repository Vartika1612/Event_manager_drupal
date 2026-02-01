<?php

namespace Drupal\event_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventListController extends ControllerBase {

  protected Connection $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * List all events.
   */
  public function list() {
    $header = [
      'event_name' => $this->t('Event Name'),
      'category' => $this->t('Category'),
      'event_date' => $this->t('Event Date'),
      'reg_start_date' => $this->t('Registration Start'),
      'reg_end_date' => $this->t('Registration End'),
    ];

    $rows = [];

    $query = $this->database->select('event_config', 'e')
      ->fields('e', [
        'event_name',
        'category',
        'event_date',
        'reg_start_date',
        'reg_end_date',
      ])
      ->orderBy('event_date', 'DESC');

    $result = $query->execute();

    foreach ($result as $record) {
      $rows[] = [
        'event_name' => $record->event_name,
        'category' => $record->category,
        'event_date' => $record->event_date,
        'reg_start_date' => $record->reg_start_date,
        'reg_end_date' => $record->reg_end_date,
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No events found.'),
    ];
  }

}
