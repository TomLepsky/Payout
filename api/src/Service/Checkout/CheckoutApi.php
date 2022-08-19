<?php

namespace App\Service\Checkout;

use App\Service\Checkout\Action\Action;
use LogicException;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CheckoutApi
{
    private const REMOTE_SERVER_MARK = '(Checkout.com)';

    public const TYPE_TO_SUMMARY = [
        'card_verified' => 'Card Verified',
        'card_verification_declined' => 'Declined',
        'payment_approved' => 'Authorized',
        'payment_canceled' => 'Declined',
        'payment_capture_declined' => 'Declined',
        'payment_capture_pending' => 'Pending',
        'payment_captured' => 'Captured',
        'payment_chargeback' => null,
        'payment_declined' => 'Declined',
        'payment_expired' => null,
        'payment_paid' => 'Paid',
        'payment_pending' => 'Pending',
        'payment_refund_pending' => 'Pending',
        'payment_refund_declined' => 'Declined',
        'payment_retrieval' => null,
        'payment_void_declined' => 'Declined',
        'payment_voided' => null,
    ];

    private ?ResponseInterface $response = null;

    private ?int $statusCode = null;
    private ?string $content = null;

    private ?Action $currentAction = null;

    public function __construct(
        private string $publicKey,
        private string $privateKey,
        private string $apiUrl,
        private CheckoutResponseHandler $responseHandler,
        private HttpClientInterface $client,
        private LoggerInterface $logger
    ) {}

    public function execute(Action $action) : self
    {
        $this->currentAction = $action;
        $payload = $action->preparePayload();
        $this->logger->info("request " . self::REMOTE_SERVER_MARK . " # " . $action->getUri() . " # " . json_encode($payload));
        $this->response = $this->request(
            $action->getMethod(),
            $action->getUri(),
            $action->getMode() === Action::PUBLIC_KEY_MODE ? $this->publicKey : $this->privateKey,
            $payload);
        $this->statusCode = $this->response->getStatusCode();
        $this->content = $this->response->getContent(false);

        $this->logger->info("response " . self::REMOTE_SERVER_MARK . " # status $this->statusCode # " . $action->getUri() . " # " . $this->content);

        return $this;
    }

    public function handleResponseErrors() : self
    {
        if ($this->response === null) {
            throw new LogicException("Make request before!");
        }

        if ($this->statusCode < 400) {
            return $this;
        }

        if ($this->statusCode === 401) {
            throw new AuthenticationException("Unauthorized");
        }

        if ($this->statusCode === 403) {
            throw new AuthenticationException("Forbidden");
        }

        if ($this->statusCode === 422) {
            throw new UnprocessableEntityHttpException($this->getErrors());
        }

        if ($this->statusCode === 429) {
            throw new BadRequestException($this->getErrors());
        }

        if ($this->statusCode === 502) {
            throw new HttpException(502, "Bad Gateway");
        }

        throw new HttpException(500, $this->getErrors());
    }

    public function getModelFromResponse() : object
    {
        /** @var StdClass $data */
        $data = json_decode($this->content);
        $context = ['CheckoutAPI' => true];
        if ($this->currentAction === null) {
            throw new LogicException("The action isn't set.");
        }

        return $this->responseHandler->getModelFromResponse($data, $this->currentAction->getModelClass(), $context);
    }

    public function getResponse() : ?ResponseInterface
    {
        return $this->response;
    }

    public function isEverythingFine() : bool
    {
        if ($this->statusCode !== null && $this->statusCode < 400) {
            // Yes, everything is fine! d(*_*)b
            return true;
        }
        return false;
    }

    private function request(string $method, string $url, string $key, ?array $payload) : ResponseInterface
    {
        $body = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "$key"
            ],
        ];
        if ($payload !== null) {
            $body['json'] = $payload;
        }
        return $this->client
            ->request(
                $method,
                $this->apiUrl . $url,
                $body
            );
    }

    private function getErrors() : string
    {
        $errors = json_decode($this->content);
        $message = self::REMOTE_SERVER_MARK;
        if (isset($errors->error_type)) {
            $message .= " $errors->error_type";
            if (isset($errors->error_codes)) {
                $message .= ": " . implode(', ', $errors->error_codes);
            }
        } else {
            $message .= 'error occurred. ' . $this->content;
        }
        return $message;
    }
}
