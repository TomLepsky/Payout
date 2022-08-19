<?php

namespace App\Filter;

use ApiPlatform\Core\Api\IdentifiersExtractorInterface;
use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class PaymentSearchFilter extends AbstractContextAwareFilter
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        ?RequestStack $requestStack,
        protected IriConverterInterface $iriConverter,
        protected ?PropertyAccessorInterface $propertyAccessor = null,
        ?LoggerInterface $logger = null,
        ?array $properties = null,
        protected ?IdentifiersExtractorInterface $identifiersExtractor = null,
        ?NameConverterInterface $nameConverter = null
    ) {
        parent::__construct($managerRegistry, $requestStack, $logger, $properties, $nameConverter);
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property !== FulltextFilter::FILTER_PROPERTY) {
            return;
        }

        if (str_contains($value, '_')) {
            $this->properties = ['paymentId' => ''];
            (new SearchFilter($this->managerRegistry, $this->requestStack, $this->iriConverter, $this->propertyAccessor, $this->logger, $this->properties, $this->identifiersExtractor, $this->nameConverter))
                ->apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, ['filters' => ['paymentId' => $value]]);
        } else {
            (new FulltextFilter($this->managerRegistry, $this->requestStack, $this->logger, $this->properties, $this->nameConverter))
                ->apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, ['filters' => ['search' => $value]]);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [];
    }
}
