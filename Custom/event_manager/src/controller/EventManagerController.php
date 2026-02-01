<?php

namespace Drupal\event_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

class EventManagerController extends ControllerBase {

  public function eventConfigPage() {

    $build = [];

    // 1️⃣ Attach Event Config Form
    $build['form'] = \Drupal::formBuilder()
      ->getForm('Drupal\event_manager\Form\EventConfigForm');

    // 2️⃣ Fetch saved events from DB
    $connection = \Drupal::database();
    $result = $connection->select('event_config', 'e')
      ->fields('e', [
        'id',
        'event_name',
        'category',
        'event_date',
      ])
      ->execute()
      ->fetchAll();

    // 3️⃣ Build table rows with Edit/Delete actions
   $rows = [];
foreach ($result as $event) {

  $rows[] = [
    $event->id,
    $event->event_name,
    $event->category,
    $event->event_date,
    [
      'data' => [
        '#type' => 'operations',
        '#links' => [
          'edit' => [
            'title' => $this->t('Edit'),
            'url' => Url::fromRoute('event_manager.edit', ['id' => $event->id]),
          ],
          'delete' => [
            'title' => $this->t('Delete'),
            'url' => Url::fromRoute('event_manager.delete', ['id' => $event->id]),
          ],
        ],
      ],
    ],
  ];
}


    // 4️⃣ Event list table
    $build['event_list'] = [
      '#type' => 'table',
      '#header' => [
        'ID',
        'Event Name',
        'Category',
        'Event Date',
        'Actions',
      ],
      '#rows' => $rows,
      '#empty' => 'No events found.',
    ];

    return $build;
  }

}

