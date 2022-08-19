<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Config;
use App\Entity\Payment;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class RestrictedPaymentExtension implements RestrictedDataProviderInterface, QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    public function __construct(private Security $security) {}

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === Payment::class;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) : void {
        $this->restrict($queryBuilder);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ) : void {
        $this->restrict($queryBuilder);
    }

    private function restrict(QueryBuilder $queryBuilder) : void
    {
        /** @var User $owner */
        $owner = $this->security->getUser();
        if ($this->security->isGranted(Config::ACCOUNTANT) || $owner === null) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf("%s.owner = :owner", $rootAlias));
        $queryBuilder->setParameter('owner', $owner->getId());
    }
}
