<?php

namespace App\Controller\Checkout;

use App\Entity\Payment;
use App\Service\Checkout\CheckoutApi;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebHookController extends AbstractController
{
    public function __construct(private string $privateKey, private LoggerInterface $logger) {}

    #[Route(path: '/webhook/update-status', methods: ['POST'])]
    public function updatePaymentStatus(Request $request) : Response
    {
        /** @var StdClass $content */
        $content = json_decode($request->getContent());
        $this->logger->info("webhook # " . $request->getUri() . " # " . $request->getContent());
        if ($content === null) {
            throw new BadRequestException("There is no request body.");
        }

        if (!$request->headers->has('CKO-Signature')) {
            $this->logger->info("webhook # " . $request->getUri() . " # " . "Signature header not provided.");
            throw new BadRequestException("Error");
        }

        $headerHash = $request->headers->get('CKO-Signature');
        $hash = hash_hmac('sha256', $request->getContent(), $this->privateKey);

        if (strcmp($headerHash, $hash) !== 0) {
            $this->logger->info("webhook # " . $request->getUri() . " # " . "The signatures don't match.");
            throw new BadRequestException("Error");
        }

        $manager = $this->getDoctrine()->getManager();
        /** @var ?Payment $payment */
        $payment = $manager->getRepository(Payment::class)->findOneBy(['paymentId' => $content->data->id]);

        if ($payment === null) {
            $this->logger->info("webhook # " . $request->getUri() . " # " . "Not Found payment with: {$content->data->id}");
            throw $this->createNotFoundException("Error");
        }

        $status = in_array($content->type, array_keys(CheckoutApi::TYPE_TO_SUMMARY)) ?
            CheckoutApi::TYPE_TO_SUMMARY[$content->type] :
            null;
        if ($status !== null) {
            $payment->setStatus($status);
        }
        $payment->setSummary($content->data->response_summary ?? null);

        $manager->flush();
        return $this->json('^_^', 202);
    }
}
