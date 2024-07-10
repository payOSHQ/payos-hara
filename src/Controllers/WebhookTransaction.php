<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Utils\PayOSHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Utils\Haravan;
use Exception;

class WebhookTransaction
{
  public function __invoke(Request $request, Response $response, array $args): Response
  {
    $haravan = new Haravan();
    $payOS = (new PayOSHandler())->PayOS();

    try {
      $contentType = $request->getHeaderLine('Content-Type');
      if (!strstr($contentType, 'application/json')) {
        throw new Exception('Content is not in JSON');
      }
      $contents = json_decode(file_get_contents('php://input'), true);
      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Parse JSON failed');
      }

      $request = $request->withParsedBody($contents);
      $body = $request->getParsedBody();
      $payOS->verifyPaymentWebhookData($body);
      // check demo data when confirm hook
      if ($body['data']['accountNumber'] === '12345678' && $body['data']['reference'] === 'TF230204212323') {
        return $response;
      }
      $data = $body['data'];
      $orderCode = $body['data']['orderCode'];
      $haravanOrder = $haravan->getOrderById($orderCode);
      if (!$haravanOrder || !isset($haravanOrder['order'])) {
        throw new Exception('Not found order');
      }
      if ($haravanOrder['order']['financial_status'] === HARAVAN_ORDER_PAID_MESSAGE) {
        return $response;
      }
      $haravan->confirmOrder($orderCode);
      // update note
      $tranNote = ' ^^^^^^ Số dư tài khoản vừa tăng ' . $data['amount'] . 'VND vào ' . $data['transactionDateTime'] . ' Mô tả ' . $data['description'] . ' Mã tham chiếu ' . $data['reference'] . ' Số tài khoản ' . $data['accountNumber'];
      $haravan->updateNoteOrder($orderCode, $haravanOrder['order']['note'] . $tranNote);

      return $response;
    } catch (Exception $e) {
      $response->getBody()->write(json_encode(['error' => $e->getMessage(), 'code' => $e->getCode()]));
      return $response->withStatus(400);
    }
  }
}
