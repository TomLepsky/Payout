<?php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Symfony\Component\PropertyInfo\Type;

class FulltextFilter extends AbstractContextAwareFilter
{
    public const FILTER_PROPERTY = 'search';

    protected function filterProperty(
        string $property,
               $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
    ) : void {
        if ($property !== self::FILTER_PROPERTY || empty($this->properties)) {
            return;
        }
        $alias = $queryBuilder->getRootAliases()[0];
        $placeholder = $queryNameGenerator->generateParameterName($property);
        $value = implode(" ", array_map(function($item) : string {return "+$item*";}, preg_split("/[\s\t\r\n]+/", $value, 0, PREG_SPLIT_NO_EMPTY)));

        $nested = false;
        $properties = array_keys($this->properties);
        foreach ($properties as $property) {
            if ($this->isPropertyNested($property, $resourceClass)) {
                $nested = true;
            } else {
                if ($nested) {
                    throw new LogicException("Fulltext filter properties should be either all nested or not");
                }
            }
        }

        if ($nested) {
            [$alias] = $this->addJoinsForNestedProperty($properties[0], $alias, $queryBuilder, $queryNameGenerator, $resourceClass);
            $mapper = function ($item) use ($alias) : string {return preg_replace('/^[\w]+(?=\.)/', $alias, $item);};
        } else {
            $mapper = function ($item) use ($alias) : string {return "$alias.$item";};
        }

        $columns = implode(", ", array_map($mapper, $properties));

        $queryBuilder
            ->andWhere(sprintf('MATCH (%s) AGAINST (:%s IN BOOLEAN MODE) > 0', $columns, $placeholder))
            ->setParameter($placeholder, $value);
    }

    public function getDescription(string $resourceClass): array
    {
        $description['search'] = [
            'property' => 'search',
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
            'swagger' => [
                'description' => '1-3 key words separated by space.',
                'name' => 'Fulltext filter',
            ]
        ];

        return $description;
    }
}
