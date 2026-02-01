namespace Drupal\event_manager\Service;

use Drupal\Core\Database\Connection;

class EventService {

  public function __construct(private Connection $db) {}

  public function getCategories() {
    return $this->db->select('event_config', 'e')
      ->fields('e', ['category'])
      ->distinct()
      ->execute()
      ->fetchCol();
  }

  public function getDates($category) {
    return $this->db->select('event_config', 'e')
      ->fields('e', ['event_date'])
      ->condition('category', $category)
      ->distinct()
      ->execute()
      ->fetchCol();
  }

  public function getEvents($category, $date) {
    return $this->db->select('event_config', 'e')
      ->fields('e')
      ->condition('category', $category)
      ->condition('event_date', $date)
      ->execute()
      ->fetchAll();
  }

  public function isDuplicate($email, $event_id) {
    return $this->db->select('event_registration', 'r')
      ->condition('email', $email)
      ->condition('event_id', $event_id)
      ->countQuery()
      ->execute()
      ->fetchField() > 0;
  }
}
