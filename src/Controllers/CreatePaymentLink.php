<?php
declare(strict_types=1);

namespace App\Controllers;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;
use App\Utils\Haravan;
use App\Utils\PayOSHandler;


class CreatePaymentLink
{
  public function __invoke(Request $request, Response $response, array $args)
  {
    $orderId = $args['orderId'];
    $redirectUri = null;
    $haravanOrder = null;

    $payOS = (new PayOSHandler())->PayOS();
    if (!is_numeric($orderId)) {
      $response->getBody()->write('INVALID DATA');
      return $response->withStatus(400);
    }

    try {
      $request = $request->withParsedBody(json_decode(file_get_contents('php://input'), true));
      $body = $request->getParsedBody();
      $redirectUri = $body["redirect_uri"];
      if (empty($redirectUri)) {
        $response->getBody()->write('INVALID DATA');
        return $response
          ->withStatus(400);
      }

      $haravanOrder = (new Haravan())->getOrderById((int) $orderId);
      if ($haravanOrder == null || !isset($haravanOrder["order"])) {
        $response->getBody()->write('NOT FOUND ORDER');
        return $response->withStatus(400);
      }
    } catch (Exception $e) {
      $statusCode = $e->getCode() ?: 500;
      $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
      return $response->withStatus($statusCode);
    }

    //get order from payOS. 
    $payosOrder = null;
    try {
      $payosOrder = $payOS->getPaymentLinkInformation((int) $orderId);
      $response->getBody()
        ->write(json_encode([
          'checkout_url' => $_ENV['CHECKOUT_URL_HOST'] . "/web/" . $payosOrder['id']
        ]));
      return $response;
    } catch (Exception $e) {
      // check case not exist orderCode
      error_log(var_export($e->getMessage(), true));
      if ($e->getCode() !== PAYOS_NOT_FOUND_ORDER_CODE) {
        return $response->withStatus(400);
      }
    }

    // create new paymentLink
    try {
      // create new 
      $data = [
        "orderCode" => (int) $orderId,
        "amount" => (int) $haravanOrder["order"]['total_price'],
        "description" => "Thanh toán đơn hàng",
        "returnUrl" => $redirectUri,
        "cancelUrl" => $redirectUri
      ];
      $paymentLink = $payOS->createPaymentLink($data);
      $response->getBody()
        ->write(json_encode([
          'checkout_url' => $paymentLink['checkoutUrl']
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