<?php

namespace App\Client;

use App\Exception\ActivityNotFoundException;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ResaWodClient
{
  public function __construct(
    private HttpClientInterface $client,
    private ContainerBagInterface $params
  ) {}

  public function getSessionId(): string
  {
    $response = $this->client->request(
      'GET',
      'https://sport.nubapp.com/web/ajax/getApplication.php?id_application='. $this->params->get('app.application_id'),
    );

    return (preg_split('/=|;/', $response->getHeaders()['set-cookie'][0]))[1];
  }

  public function login(string $sessionId): void
  {
    $this->client->request(
      'POST',
      'https://sport.nubapp.com/web/ajax/users/checkUser.php',
      [
        'body' => [
          'username' => $this->params->get('app.username'),
          'password' => $this->params->get('app.password'),
        ],
        'headers' => [
          'cookie' => 'applicationId=' . $this->params->get('app.application_id') . '; PHPSESSID-FRONT=' . $sessionId
        ]
      ]
    );
  }
  
  public function getActivity(string $day, string $startTime, string $sessionId): stdClass
  {
    $startDay = strtotime('next ' . $day);
    $endDay = strtotime('+1 day', $startDay);

    $response = $this->client->request(
      'GET',
      'https://sport.nubapp.com/web/ajax/activities/getActivitiesCalendar.php?id_category_activity=451&start=' . $startDay . '&end=' . $endDay,
      [
        'headers' => [
          'cookie' => 'applicationId=' . $this->params->get('app.application_id') . '; PHPSESSID-FRONT=' . $sessionId
        ]
      ]
    );

    foreach(json_decode($response->getContent()) as $activity) {
      if ($activity->start_time === $startTime) {
        return $activity;
      }
    }

    throw new ActivityNotFoundException();
  }

  public function book(string $activityId, string $sessionId): void
  {
    $this->client->request(
      'POST',
      'https://sport.nubapp.com/web/ajax/bookings/bookBookings.php',
      [
        'body' => [
          'items' => [
            'activities' => [
              0 => [
                'id_activity_calendar' => $activityId,
                'unit_price' => '0',
                'n_guests' => '0',
                'id_resource' => 'false' 
              ]
            ]
          ],
          'discount_code' => 'false',
          'form' => '',
          'formIntoNotes' => '',
        ],
        'headers' => [
          'cookie' => 'applicationId='. $this->params->get('app.application_id') . '; PHPSESSID-FRONT=' . $sessionId
        ]
      ]
    );
  }
}