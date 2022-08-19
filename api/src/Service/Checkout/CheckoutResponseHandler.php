<?php

namespace App\Service\Checkout;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use LogicException;
use stdClass;

class CheckoutResponseHandler
{
    public function __construct(private iterable $dataTransformers) {}

    public function getModelFromResponse(StdClass $data, string $toClass, array $context = []) : object
    {
        foreach ($this->dataTransformers as /** @var DataTransformerInterface $dataTransformer */ $dataTransformer) {
            if ($dataTransformer->supportsTransformation($data, $toClass, $context)) {
                return $dataTransformer->transform($data, $toClass, $context);
            }
        }

        throw new LogicException("There are no suitable transformer for $toClass");
    }
}
