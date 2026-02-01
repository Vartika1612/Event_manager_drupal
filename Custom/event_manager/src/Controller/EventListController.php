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

  public function list() {
    $header = [
      'event_name' => $this->t('Event Name'),
      'category' => $this->t('Category'),
      'event_date' => $this->t('Event Date'),
      'operations' => $this->t('Operations'),
    ];

    $rows = [];

    $results = $this->database->select('event_config', 'e')
      ->fields('e')
      ->execute();

    foreach ($results as $row) {
      $rows[] = [
        'event_name' => $row->event_name,
        'category' => $row->category,
        'event_date' => $row->event_date,
        'operations' => [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'edit' => [
                'title' => $this->t('Edit'),
                'url' => \Drupal\Core\Url::fromRoute(
                  'event_registration.event_edit',
                  ['id' => $row->id]
                ),
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => \Drupal\Core\Url::fromRoute(
                  'event_registration.event_delete',
                  ['id' => $row->id]
                ),
              ],
            ],
          ],
        ],
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
