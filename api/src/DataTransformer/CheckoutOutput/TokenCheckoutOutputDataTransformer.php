<?php

namespace App\DataTransformer\CheckoutOutput;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Model\Token;

class TokenCheckoutOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @param object $object
     * @param string $to
     * @param array $context
     * @return Token
     */
    public function transform($object, string $to, array $context = []) : Token
    {
        $token = new Token();
        $token->type = $object->type;
        $token->token = $object->token;
        $token->expiresOn = $object->expires_on;
        $token->expiryMonth = $object->expiry_month ?? null;
        $token->expiryYear = $object->expiry_year ?? null;
        $token->last4 = $object->last4 ?? null;
        $token->bin = $object->bin ?? null;
        $token->scheme = $object->scheme ?? null;

        return $token;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $to === Token::class && isset($context['CheckoutAPI']) && $context['CheckoutAPI'] === true;
    }
}
