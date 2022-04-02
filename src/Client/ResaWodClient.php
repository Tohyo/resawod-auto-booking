<?php

namespace App\Client;

use App\Exception\ActivityNotFound;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ResaWodClient
{
  public function __construct(
    private HttpClientInterface $client,
    private ContainerBagInterface $params
  ) {}
  
  public function getActivity($day, $startTime)
  {
    $startDay = strtotime('next ' . $day);
    $endDay = strtotime('+1 day', $startDay);

    $response = $this->client->request(
      'GET',
      'https://sport.nubapp.com/web/ajax/activities/getActivitiesCalendar.php?id_category_activity=451&start=' . $startDay . '&end=' . $endDay,
      [
        'headers' => [
          'cookie' => 'applicationId=' . $this->params->get('app.application_id') . '; PHPSESSID-FRONT=' . $this->params->get('app.session_id')
        ]
      ]
    );

    foreach(json_decode($response->getContent()) as $activity) {
      if ($activity->start_time === $startTime) {
        return $activity;
      }
    }

    throw new ActivityNotFound();
  }

  public function book($activityId)
  {
    return $this->client->request(
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
          'cookie' => 'applicationId='. $this->params->get('app.application_id') . '; PHPSESSID-FRONT=' . $this->params->get('app.session_id') . '; PHPSESSID-BACK=' . $this->params->get('app.back_id')
        ]
      ]
    );
  }
}