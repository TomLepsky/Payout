<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\DTO\PaymentCollectionOutput;
use App\Entity\Payment;

class PaymentCollectionOutputDataTransformer implements DataTransformerInterface
{

    public function transform($object, string $to, array $context = []) : PaymentCollectionOutput
    {
        /** @var Payment $object */
        $output = new PaymentCollectionOutput();
        $output->id = $object->getId();
        $output->paymentId = $object->getPaymentId();
        $output->amount = $object->getAmount() / 100;
        $output->currency = $object->getCurrency();
        $output->code = $object->getCode();
        $output->status = $object->getStatus();
        $output->createdAt = $object->getCreatedAt();
        $output->updatedAt = $object->getUpdatedAt();
        $output->summary = $object->getSummary();
        $output->firstName = $object->getFirstName();
        $output->lastName = $object->getLastName();
        $output->bin = $object->getBin();
        $output->last4 = $object->getLast4();
        $output->notes = $object->getNotes();
        $output->owner = $object->getOwner();

        return $output;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $to === PaymentCollectionOutput::class && $data instanceof Payment;
    }
}
