<?php

namespace App\Tests\Service;

use App\Service\ExchangerService;
use PHPUnit\Framework\TestCase;

class ExchangerServiceTest extends TestCase
{
  private $exchangerService;

  protected function setUp(): void
  {
    $this->exchangerService = new ExchangerService();
  }

  public function testIsEuReturnsTrueForEuCountries()
  {
    $this->assertTrue($this->exchangerService->isEu('AT'));
    $this->assertTrue($this->exchangerService->isEu('BE'));
    $this->assertTrue($this->exchangerService->isEu('BG'));
  }

  public function testIsEuReturnsFalseForNonEuCountries()
  {
    $this->assertFalse($this->exchangerService->isEu('US'));
    $this->assertFalse($this->exchangerService->isEu('JP'));
    $this->assertFalse($this->exchangerService->isEu('CN'));
  }

  public function testProcessCurrencyWithEmptyFilePathReturnsFalse()
  {
    $result = $this->exchangerService->processCurrencyDataFromFile('');
    $this->assertFalse($result);
  }

  public function testProcessCurrencyWithFileDoesNotExist()
  {
    $result = $this->exchangerService->processCurrencyDataFromFile('/tmp/public/input.txt');
    $this->assertFalse($result);
  }

  public function testParseFile()
  {
    // Create a temporary file with sample data.
    $temp_file = tempnam(sys_get_temp_dir(), 'test_parse_file');
    $data = [
      '{"bin":"45717360","amount":"100.00","currency":"EUR"}',
      '{"bin":"516793","amount":"50.00","currency":"USD"}',
      '{"bin":"45417360","amount":"10000.00","currency":"JPY"}',
      '{"bin":"41417360","amount":"130.00","currency":"USD"}',
      '{"bin":"4745030","amount":"2000.00","currency":"GBP"}',
    ];
    file_put_contents($temp_file, implode("\n", $data));

    $result = $this->exchangerService->parseFile($temp_file);

    // Assert that the output is correct.
    $this->assertEquals([
      [
        'bin' => '45717360',
        'amount' => '100.00',
        'currency' => 'EUR',
      ],
      [
        'bin' => '516793',
        'amount' => '50.00',
        'currency' => 'USD',
      ],
      [
        'bin' => '45417360',
        'amount' => '10000.00',
        'currency' => 'JPY',
      ],
      [
        'bin' => '41417360',
        'amount' => '130.00',
        'currency' => 'USD',
      ],
      [
        'bin' => '4745030',
        'amount' => '2000.00',
        'currency' => 'GBP',
      ],
    ], $result);

    // Remove the temporary file.
    unlink($temp_file);
  }

  public function testProcessCurrencyReturnsExpectedResult()
  {
    // Create temporary test file with input data.
    $file_path = tempnam(sys_get_temp_dir(), 'test_');
    $input_data = '{"bin":"45717360","amount":"100.00","currency":"EUR"}' . "\n"
      . '{"bin":"516793","amount":"50.00","currency":"USD"}' . "\n"
      . '{"bin":"45417360","amount":"10000.00","currency":"JPY"}' . "\n";
    file_put_contents($file_path, $input_data);

    // Call the function being tested.
    $result = $this->exchangerService->processCurrencyDataFromFile($file_path);

    // Check the expected output
    $expected_output = ['1.00', '0.42', '1.53'];
    $this->assertEquals($expected_output, $result);

    // Delete the temporary test file.
    unlink($file_path);
  }

}
