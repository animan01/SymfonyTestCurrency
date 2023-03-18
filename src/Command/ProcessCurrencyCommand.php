<?php

namespace App\Command;

use App\Service\ExchangerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ProcessCurrencyCommand extends Command
{
  protected static $defaultName = 'app:process-currency';

  private ExchangerService $exchangerService;

  public function __construct(ExchangerService $exchangerService)
  {
    parent::__construct();
    $this->exchangerService = $exchangerService;
  }

  protected function configure(): void
  {
    $this->setDescription('Process currency data from a file.')
      ->addArgument('file_path', InputArgument::REQUIRED, 'The path of the file to be processed.');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $filePath = $input->getArgument('file_path');
    $results = $this->exchangerService->processCurrencyDataFromFile($filePath);
    if (empty($results)) {
      $output->writeln('Something went wrong.');
      return Command::FAILURE;
    }
    foreach ($results as $value) {
      $output->writeln($value);
    }

    return Command::SUCCESS;
  }
}
