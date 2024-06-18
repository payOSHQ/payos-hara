<?php
declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;
use App\Utils\Haravan;

class GetStatusOrder
{
  public function __invoke(Request $request, Response $response, array $args)
  {
    $orderId = $args['orderId'];
    $haravan = new Haravan();
    if (!is_numeric($orderId)) {
      $response->getBody()->write('INVALID DATA');
      return $response
        ->withStatus(400);
    }

    try {
      $order = $haravan->getOrderById($orderId);
      if (!$order || !isset($order['order'])) {
        $response->getBody()->write('NOT FOUND ORDER');
        return $response
          ->withStatus(400);
      }

      $response->getBody()->write(json_encode([
        'financial_status' => $order['order']['financial_status']
      ]));
      return $response;
    } catch (Exception $e) {
      $statusCode = $e->getCode() ?: 500;
      $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
      return $response
        ->withStatus($statusCode);
    }
  }
}