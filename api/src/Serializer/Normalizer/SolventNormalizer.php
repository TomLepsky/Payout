<?php

namespace App\Serializer\Normalizer;

use App\Model\Solvent;
use App\Service\Payment\PaymentService;
use ArrayObject;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class SolventNormalizer implements NormalizerAwareInterface, ContextAwareNormalizerInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'PAYMENT_DENORMALIZER_ALREADY_CALLED';

    public function __construct(private PaymentService $paymentService) {}

    public function supportsNormalization($data, string $format = null, array $context = []) : bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Solvent;
    }

    public function normalize($object, string $format = null, array $context = []) : string|array|ArrayObject|bool|int|null|float
    {
        $context[self::ALREADY_CALLED] = true;
        $solvent = PaymentService::subsidiaryCoinConverter($object, PaymentService::FROM_SUBSIDIARY);
        return $this->normalizer->normalize($solvent, $format, $context);
    }
}
