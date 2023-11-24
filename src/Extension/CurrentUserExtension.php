<?php

namespace App\Extension;

use Doctrine\ORM\QueryBuilder;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\Operation;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;

class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass,array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder);
    }

    private function addWhere(QueryBuilder $queryBuilder): void
    {
        $user = $this->userRepository->findOneBy([
            'username' => $this->security->getUser()->getUserIdentifier()
        ]);

        if ($user === null) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->innerJoin(sprintf('%s.createdBy', $rootAlias), 'u');
        $queryBuilder->andWhere(sprintf('%s.username = :current_user', 'u' ));
        $queryBuilder->setParameter('current_user', $user->getUserIdentifier());
    }
}
