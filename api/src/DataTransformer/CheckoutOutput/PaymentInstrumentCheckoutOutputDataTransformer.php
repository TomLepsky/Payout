<?php

namespace App\DataTransformer\CheckoutOutput;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Entity\PaymentInstrument;

class PaymentInstrumentCheckoutOutputDataTransformer implements DataTransformerInterface
{
    public function transform($object, string $to, array $context = []) : PaymentInstrument
    {
        return (new PaymentInstrument())
            ->setSource($object->id)
            ->setType($object->type)
            ->setBin($object->bin)
            ->setLast4($object->last4)
            ->setFingerprint($object->fingerprint)
            ->setExpiryMonth($object->expiry_month ?? null)
            ->setExpiryYear($object->expiry_year ?? null);
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $to === PaymentInstrument::class && isset($context['CheckoutAPI']) && $context['CheckoutAPI'] === true;
    }
}
