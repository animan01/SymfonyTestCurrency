<?php

namespace App\Service;

use GuzzleHttp\Client;
use Symfony\Component\Config\Definition\Exception\Exception;

class ExchangerService {

  const EXCHANGER_COUNTRY_EU = [
    'AT',
    'BE',
    'BG',
    'CY',
    'CZ',
    'DE',
    'DK',
    'EE',
    'ES',
    'FI',
    'FR',
    'GR',
    'HR',
    'HU',
    'IE',
    'IT',
    'LT',
    'LU',
    'LV',
    'MT',
    'NL',
    'PO',
    'PT',
    'RO',
    'SE',
    'SI',
    'SK',
  ];

  /**
   * Checks if a given country is part of the European Union.
   *
   * @param string $country
   *   The country code to check.
   * @return bool
   *   Returns TRUE if the country is part of the EU, and FALSE otherwise.
   */
  public function isEu(string $country): bool {
    return in_array($country, self::EXCHANGER_COUNTRY_EU);
  }

  /**
   * Processes currency data from a file.
   *
   * @param string $file_path
   *  The path to the input file.
   *
   * @return array|false
   *  The processed currency data, or FALSE if there was an error.
   */
  public function processCurrencyDataFromFile(string $file_path) {
    // Check if file_path is empty or file does not exist
    if (empty($file_path)) {
      return FALSE;
    }
    if (!file_exists($file_path)) {
      return FALSE;
    }

    $file_data = $this->parseFile($file_path);
    if (empty($file_data)) {
      return FALSE;
    }

    $results = [];
    foreach ($file_data as $value) {
      // Call the binlist API to get the country code.
      $client = new Client();
      try {
        $response = $client->get('https://lookup.binlist.net/' . $value['bin']);
        $binlist_results = $response->getBody()->getContents();
        $binlist_data = json_decode($binlist_results);
      } catch (Exception $e) {
        // If there is an error, skip this entry and continue with the next one.
        continue;
      }

      // Calculate the coefficient based on the country code.
      $coefficient = $this->isEu($binlist_data->country->alpha2) ? 0.01 : 0.02;

      // Call the exchangeratesapi API to get the exchange rate
      // todo: something wrong with API, need Access Key.
      $response = $client->get('https://api.exchangeratesapi.io/latest');
      $exchange_data = json_decode('{"rates": {"USD": 1.2,"GBP": 0.9,"JPY": 130.5,"EUR": 1}}', TRUE);
      $rate = $exchange_data['rates'][$value['currency']];

      // Calculate the fixed amount based on the exchange rate and currency.
      if ($value['currency'] === 'EUR' or $rate == 0) {
        $amount_fixed = $value['amount'];
      } else {
        $amount_fixed = $value['amount'] / $rate;
      }

      // Add the formatted result to the list of results.
      $results[] = number_format($amount_fixed * $coefficient, 2, '.', '');
    }

    return $results;
  }


  /**
   * Parses a file containing JSON data, where each line is a separate JSON
   * object.
   *
   * @param string $file_path
   *   The path to the file to parse.
   *
   * @return array|false
   *   An array of parsed JSON objects, or FALSE if the file is empty or could
   *   not be read.
   */
  public function parseFile($file_path) {
    $file_content = file_get_contents($file_path);

    if (empty($file_content)) {
      return FALSE;
    }

    // Split the file content into separate lines.
    $lines = explode("\n", $file_content);
    $json_data = [];
    foreach ($lines as $line) {
      // Skip empty lines.
      if (empty($line)) {
        continue;
      }
      $json_data[] = json_decode($line, TRUE);
    }
    return $json_data;
  }

}
