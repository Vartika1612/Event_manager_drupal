<?php

namespace Drupal\event_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class EventRegistrationListController extends ControllerBase {

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
   * Admin listing page.
   */
  public function list(Request $request) {
    $event_date = $request->query->get('event_date');
    $event_id = $request->query->get('event_id');

    // Event date options
    $dates = $this->database->select('event_config', 'e')
      ->fields('e', ['event_date'])
      ->distinct()
      ->execute()
      ->fetchCol();

    $date_options = ['' => '- Select -'];
    foreach ($dates as $date) {
      $date_options[$date] = $date;
    }

    // Event name options
    $event_options = ['' => '- Select -'];
    if ($event_date) {
      $events = $this->database->select('event_config', 'e')
        ->fields('e', ['id', 'event_name'])
        ->condition('event_date', $event_date)
        ->execute();

      foreach ($events as $event) {
        $event_options[$event->id] = $event->event_name;
      }
    }

    // Fetch registrations
    $query = $this->database->select('event_registration', 'r')
      ->fields('r', [
        'full_name',
        'email',
        'college_name',
        'department',
        'event_date',
        'created',
      ]);

    if ($event_date) {
      $query->condition('event_date', $event_date);
    }
    if ($event_id) {
      $query->condition('event_id', $event_id);
    }

    $results = $query->execute();

    $rows = [];
    foreach ($results as $row) {
      $rows[] = [
        $row->full_name,
        $row->email,
        $row->event_date,
        $row->college_name,
        $row->department,
        date('d-m-Y H:i', $row->created),
      ];
    }

    $build['filter'] = [
      '#type' => 'container',
    ];

    $build['filter']['event_date'] = [
      '#type' => 'select',
      '#title' => 'Event Date',
      '#options' => $date_options,
      '#default_value' => $event_date,
    ];

    $build['filter']['event_id'] = [
      '#type' => 'select',
      '#title' => 'Event Name',
      '#options' => $event_options,
      '#default_value' => $event_id,
    ];

    $build['filter']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Filter',
      '#attributes' => ['onclick' => 'this.form.submit();'],
    ];

    $build['summary'] = [
      '#markup' => '<p><strong>Total participants: ' . count($rows) . '</strong></p>',
    ];

    $build['table'] = [
      '#type' => 'table',
      '#header' => [
        'Name',
        'Email',
        'Event Date',
        'College',
        'Department',
        'Submission Date',
      ],
      '#rows' => $rows,
      '#empty' => 'No registrations found.',
    ];

    $build['export'] = [
      '#type' => 'link',
      '#title' => 'Export CSV',
      '#url' => \Drupal\Core\Url::fromRoute('event_manager.registration_export', [
        'event_date' => $event_date,
        'event_id' => $event_id,
      ]),
      '#attributes' => ['class' => ['button']],
    ];

    return $build;
  }

  /**
   * CSV export.
   */
  public function export(Request $request) {
    $event_date = $request->query->get('event_date');
    $event_id = $request->query->get('event_id');

    $query = $this->database->select('event_registration', 'r')
      ->fields('r');

    if ($event_date) {
      $query->condition('event_date', $event_date);
    }
    if ($event_id) {
      $query->condition('event_id', $event_id);
    }

    $results = $query->execute();

    $csv = "Name,Email,Event Date,College,Department,Submitted On\n";

    foreach ($results as $row) {
      $csv .= "{$row->full_name},{$row->email},{$row->event_date},{$row->college_name},{$row->department}," . date('Y-m-d H:i', $row->created) . "\n";
    }

    return new Response(
      $csv,
      200,
      [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="event_registrations.csv"',
      ]
    );
  }
}
