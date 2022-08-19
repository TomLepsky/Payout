<?php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

class SubsidiaryAmountFilter extends RangeFilter
{
    public const AMOUNT_PROPERTY = 'amount';

    protected function filterProperty(
        string $property,
        $values,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) : void {
        if ($property !== self::AMOUNT_PROPERTY && !is_array($values)) {
            return;
        }

        foreach ($values as &$value) {
            $value = (string) ((int) $value * 100);
        }
       parent::filterProperty($property, $values, $queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
    }
}
