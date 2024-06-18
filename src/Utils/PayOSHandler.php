<?php 
namespace App\Utils;

use PayOS\PayOS;

class PayOSHandler 
{
  public function PayOS()
  {
    return new PayOS($_ENV['PAYOS_CLIENT_ID'], $_ENV['PAYOS_API_KEY'], $_ENV['PAYOS_CHECKSUM_KEY']);
  }
}
