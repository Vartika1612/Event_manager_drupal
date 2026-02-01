<?php

namespace Drupal\event_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for listing events.
 */
class EventListController extends ControllerBase {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * Constructor.
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
   * Displays the list of events.
   */
  public function list() {

    $header = [
      'event_name' => $this->t('Event Name'),
      'category' => $this->t('Category'),
      'event_date' => $this->t('Event Date'),
      'reg_start_date' => $this->t('Registration Start Date'),
      'reg_end_date' => $this->t('Registration End Date'),
      'operations' => $this->t('Operations'),
    ];

    $rows = [];

    $query = $this->database->select('event_config', 'e')
      ->fields('e', [
        'id',
        'event_name',
        'category',
        'event_date',
        'reg_start_date',
        'reg_end_date',
      ])
      ->orderBy('event_date', 'DESC');

    $results = $query->execute();

    foreach ($results as $event) {
      $rows[] = [
        'event_name' => $event->event_name,
        'category' => $event->category,
        'event_date' => $event->event_date,
        'reg_start_date' => $event->reg_start_date,
        'reg_end_date' => $event->reg_end_date,
        'operations' => [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'edit' => [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute(
                  'event_manager.event_edit',
                  ['id' => $event->id]
                ),
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute(
                  'event_manager.event_delete',
                  ['id' => $event->id]
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
