<?php
namespace App\Utils;

class Util
{
  /**
   * Extract HTTP status code from response headers
   * 
   * @param array $headers
   * @return int
   */
  public static function getHttpCodeFromHeaders(array $headers): int
  {
    foreach ($headers as $header) {
      if (preg_match('#HTTP/\d+\.\d+\s+(\d+)#', $header, $matches)) {
        return (int) $matches[1];
      }
    }
    return 0; // Default to 0 if no status code found
  }
  public static function convertPhoneNumber($phoneNumber): string
  {
    if (substr($phoneNumber, 0, 3) == '+84') {
      $phoneNumber = '0' . substr($phoneNumber, 3);
    }
    return str_replace('+', '', $phoneNumber);
  }
  public static function convertOrderName($orderName): string
  {
    return ($_ENV['PREFIX_ORDER'] ?? '') . preg_replace('/[^A-Za-z0-9]/', '', $orderName);
  }
}
