<?php

namespace App\Command;

use App\Client\ResaWodClient;
use App\Exception\ActivityNotFound;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResaWodCommand extends Command
{
  public function __construct(
    private ResaWodClient $resaWodClient
  ) {
    parent::__construct();
  }

  protected static $defaultName = 'app:resa-wod';

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $bookingConfiguration = [
      'wednesday' => [
        'day' => 'monday',
        'time' => '07:00',
      ],
      'thursday' => [
        'day' => 'tuesday',
        'time' => '12:30',
      ],
      'friday' => [
        'day' => 'wednesday',
        'time' => '12:30',
      ],
      'saturday' => [
        'day' => 'friday',
        'time' => '12:30',
      ],
      'monday' => [
        'day' => 'thursday',
        'time' => '07:00',
      ],
      'tuesday' => [
        'day' => 'saturday',
        'time' => '12:00',
      ]
    ];

    $currentDay = strtolower(date('l'));

    if (isset($bookingConfiguration[$currentDay])) {
      $toBook = $bookingConfiguration[$currentDay];
      try {
        $activity = $this->resaWodClient->getActivity($toBook['day'], $toBook['time']);
      } catch (ActivityNotFound $e) {
        return Command::FAILURE;
      }
  
      $this->resaWodClient->book($activity->id_activity_calendar);
    }
    
    return Command::SUCCESS;
  }
}