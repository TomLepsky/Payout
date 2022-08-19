<?php

namespace App\DataTransformer\CheckoutOutput;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Entity\Payment;
use Exception;

class PaymentCheckoutOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @param object $object
     * @param string $to
     * @param array $context
     * @return Payment
     * @throws Exception
     */
    public function transform($object, string $to, array $context = []) : Payment
    {
        return (new Payment())
            ->setPaymentId($object->id)
            ->setAmount($object->amount ?? null)
            ->setCurrency($object->currency ?? null)
            ->setStatus($object->status ?? null)
            ->setCode($object->response_code ?? null)
            ->setSummary($object->response_summary ?? null);
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $to === Payment::class && isset($context['CheckoutAPI']) && $context['CheckoutAPI'] === true;
    }
}
