<?php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\PaymentInstrument;
use Doctrine\ORM\QueryBuilder;

final class FakeFulltextFilter extends AbstractFilter
{
    private array $availableArgs = [
        'search',
    ];

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) : void {
        if (!in_array($property, $this->availableArgs) || is_array($value)) {
            return;
        }
        $alias = $queryBuilder->getRootAliases()[0];
        $firstName = $queryNameGenerator->generateParameterName('firstName');
        $lastName = $queryNameGenerator->generateParameterName('lastName');
        $last4 = $queryNameGenerator->generateParameterName('last4');
        $paymentId = $queryNameGenerator->generateParameterName('paymentId');
        $whereClause = "
            (p.firstName = :$firstName OR
            p.lastName = :$lastName OR
            p.last4 = :$last4 OR
            $alias.paymentId = :$paymentId) AND
            p.hide = false";
        $queryBuilder
            ->innerJoin(PaymentInstrument::class, 'p', 'WITH', "$alias.instrument = p.id")
            ->andWhere($whereClause)
            ->setParameter($firstName, $value)
            ->setParameter($lastName, $value)
            ->setParameter($last4, $value)
            ->setParameter($paymentId, $value);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'search' => [
                'property' => null,
                'type' => 'string',
                'requires' => false,
            ]
        ];
    }
}
