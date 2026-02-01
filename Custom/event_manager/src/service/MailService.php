namespace Drupal\event_manager\Service;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class MailService {

  public function __construct(
    private MailManagerInterface $mailManager,
    private ConfigFactoryInterface $configFactory
  ) {}

  public function send($to, array $params) {
    return $this->mailManager->mail(
      'event_manager',
      'registration',
      $to,
      'en',
      $params
    );
  }
}
