<?php
use Slim\Factory\AppFactory;
use App\Controllers;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/constants.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, false, false);
$container = $app->getContainer();


$afterMiddleware = function ($request, $handler) {
  $response = $handler->handle($request);
  return $response->withHeader('Content-Type', 'application/json')
    ->withHeader('Access-Control-Allow-Origin', '*');
};

$app->get('/get-status-order/{orderId}', Controllers\GetStatusOrder::class)->add($afterMiddleware);

$app->post('/create-payment-link/{orderId}', Controllers\CreatePaymentLink::class)->add($afterMiddleware);
$app->post('/webhook-transaction', Controllers\WebhookTransaction::class)->add($afterMiddleware);


$app->run();
