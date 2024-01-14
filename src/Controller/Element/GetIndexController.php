<?php

namespace App\Controller\Element;

use App\Security\AuthProvider;
use App\Service\CollectionService;
use App\Service\EtagService;
use Laudis\Neo4j\Databags\Statement;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Syndesi\CypherEntityManager\Type\EntityManager as CypherEntityManager;

class GetIndexController extends AbstractController
{
    public function __construct(
        private CypherEntityManager $cypherEntityManager,
        private AuthProvider $authProvider,
        private CollectionService $collectionService,
        private EtagService $etagService,
    ) {
    }

    #[Route(
        '/',
        name: 'get-index',
        methods: ['GET']
    )]
    public function getIndex(): Response
    {
        $userUuid = $this->authProvider->getUserUuid();
        $cypherClient = $this->cypherEntityManager->getClient();
        $res = $cypherClient->runStatement(Statement::create(
            "MATCH (user:User {id: \$userId})\n".
            "MATCH (user)-[:OWNS|IS_IN_GROUP|HAS_READ_ACCESS]->(element)\n".
            "RETURN element.id\n".
            "ORDER BY element.id\n".
            "SKIP \$skip\n".
            'LIMIT $limit',
            [
                'userId' => $userUuid->toString(),
                'skip' => ($this->collectionService->getCurrentPage() - 1) * $this->collectionService->getPageSize(),
                'limit' => $this->collectionService->getPageSize(),
            ]
        ));
        $nodeUuids = [];
        foreach ($res as $resultSet) {
            $nodeUuids[] = UuidV4::fromString($resultSet->get('element.id'));
        }

        $response = $this->collectionService->buildCollectionFromUuids($nodeUuids, [], count($nodeUuids));

        $etag = $this->etagService->getIndexCollectionEtag($userUuid);
        if ($etag) {
            $response->headers->set('Etag', $etag);
        }

        return $response;
    }
}
