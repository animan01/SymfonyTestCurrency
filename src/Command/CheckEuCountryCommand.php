<?php
namespace App\Command;

use App\Service\ExchangerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckEuCountryCommand extends Command
{
  protected static $defaultName = 'app:check-eu-country';

  private $exchangerService;

  public function __construct(ExchangerService $exchangerService)
  {
    $this->exchangerService = $exchangerService;

    parent::__construct();
  }

  protected function configure()
  {
    $this
      ->setDescription('Check if a country code is in the EU')
      ->addArgument('country', InputArgument::REQUIRED, 'The country code to check (e.g. AT)');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $country = strtoupper($input->getArgument('country'));

    if ($this->exchangerService->isEu($country)) {
      $output->writeln(sprintf('Country %s is in the EU', $country));
    } else {
      $output->writeln(sprintf('Country %s is not in the EU', $country));
    }

    return Command::SUCCESS;
  }
}
