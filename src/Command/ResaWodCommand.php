<?php

namespace App\Command;

use App\Client\ResaWodClient;
use App\Exception\ActivityNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:resa-wod')]
class ResaWodCommand extends Command
{
  public function __construct(
    private ResaWodClient $resaWodClient
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $bookingConfiguration = [
      'wednesday' => [
        'day' => 'monday',
        'time' => '07:00',
      ],
      'thursday' => [
        'day' => 'tuesday',
        'time' => '07:00',
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

    if (!isset($bookingConfiguration[$currentDay])) {
      return Command::SUCCESS;
    }

    $toBook = $bookingConfiguration[$currentDay];

    try {
      $this->resaWodClient->book($toBook['day'], $toBook['time']);
    } catch (ActivityNotFoundException $e) {
      return Command::FAILURE;
    }

    return Command::SUCCESS;
  }
}