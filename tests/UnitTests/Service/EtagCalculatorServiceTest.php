<?php

namespace App\tests\UnitTests\Service;

use App\Service\EtagCalculatorService;
use Beste\Psr\Log\TestLogger;
use EmberNexusBundle\Service\EmberNexusConfiguration;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Databags\Statement;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\DateTimeZoneId;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Ramsey\Uuid\Uuid;
use stdClass;
use Syndesi\CypherEntityManager\Type\EntityManager as CypherEntityManager;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class EtagCalculatorServiceTest extends TestCase
{
    use ProphecyTrait;

    public function testCalculateElementEtagForNodeWhichExists(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'node.updated' => new DateTimeZoneId(1705772003, 646811000, 'UTC'),
                    'relation.updated' => null,
                ]),
            ]
        );
        /**
         * @var ?Statement $statement
         */
        $statement = null;

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::that(
            function ($internalStatement) use (&$statement) {
                $statement = $internalStatement;

                return true;
            }
        ))->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateElementEtag($uuid);

        // assert result
        $this->assertSame('Rh8DXSRXuja', (string) $etag);

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertSame(
            "OPTIONAL MATCH (node {id: \$elementUuid})\n".
            "OPTIONAL MATCH ()-[relation {id: \$elementUuid}]->()\n".
            'RETURN node.updated, relation.updated',
            $statement->getText()
        );

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for element.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculated Etag for element.'));
    }

    public function testCalculateElementEtagForRelationWhichExists(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'node.updated' => null,
                    'relation.updated' => new DateTimeZoneId(1705772003, 646811000, 'UTC'),
                ]),
            ]
        );
        /**
         * @var ?Statement $statement
         */
        $statement = null;

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::that(
            function ($internalStatement) use (&$statement) {
                $statement = $internalStatement;

                return true;
            }
        ))->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateElementEtag($uuid);

        // assert result
        $this->assertSame('Rh8DXSRXuja', (string) $etag);

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertSame(
            "OPTIONAL MATCH (node {id: \$elementUuid})\n".
            "OPTIONAL MATCH ()-[relation {id: \$elementUuid}]->()\n".
            'RETURN node.updated, relation.updated',
            $statement->getText()
        );

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for element.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculated Etag for element.'));
    }

    public function testCalculateElementEtagForElementWhichDoesNotExist(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'node.updated' => null,
                    'relation.updated' => null,
                ]),
            ]
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateElementEtag($uuid);
        $this->assertNull($etag);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for element.'));
    }

    public function testCalculateElementEtagWithEdgecaseWhereDifferentObjectIsReturned(): void
    {
        if (array_key_exists('LEAK', $_ENV)) {
            $this->markTestSkipped();
        }
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'node.updated' => new stdClass(),
                    'relation.updated' => null,
                ]),
            ]
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        $this->expectExceptionMessage('Unable to get DateTime from stdClass.');

        // run service method
        $etagCalculatorService->calculateElementEtag($uuid);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for element.'));
    }

    public function testCalculateChildrenCollectionEtagWithExistingElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([
                        new CypherList([
                            '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                            new DateTimeZoneId(1705772003, 646811000, 'UTC'),
                        ]),
                        new CypherList([
                            '2c42deee-ad24-4f04-bb37-7c31fd5b3345',
                            new DateTimeZoneId(1705772003, 646811000, 'UTC'),
                        ]),
                    ]),
                    'childrenCount' => 1,
                ]),
            ]
        );
        /**
         * @var ?Statement $statement
         */
        $statement = null;

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::that(
            function ($internalStatement) use (&$statement) {
                $statement = $internalStatement;

                return true;
            }
        ))->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateChildrenCollectionEtag($uuid);

        // assert result
        $this->assertSame('EKHX4b5HhHX', (string) $etag);

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertSame(
            "MATCH (parent {id: \$parentUuid})\n".
            "MATCH (parent)-[:OWNS]->(children)\n".
            "MATCH (parent)-[relations]->(children)\n".
            "WITH children, relations\n".
            "LIMIT 101\n".
            "WITH children, relations\n".
            "ORDER BY children.id, relations.id\n".
            "WITH COLLECT([children.id, children.updated]) + COLLECT([relations.id, relations.updated]) AS rawTuples, count(children) as childrenCount\n".
            "CALL {\n".
            "  WITH rawTuples\n".
            "  UNWIND rawTuples as tuple\n".
            "  WITH tuple ORDER BY tuple[0]\n".
            "  RETURN COLLECT(tuple) AS sortedTuples\n".
            "}\n".
            'RETURN sortedTuples, childrenCount',
            $statement->getText()
        );

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for children collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculated Etag for children collection.'));
    }

    public function testCalculateChildrenCollectionEtagWithNoElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([]),
                    'childrenCount' => 0,
                ]),
            ]
        );
        /**
         * @var ?Statement $statement
         */
        $statement = null;

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::that(
            function ($internalStatement) use (&$statement) {
                $statement = $internalStatement;

                return true;
            }
        ))->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateChildrenCollectionEtag($uuid);

        // assert result
        $this->assertSame('3F8H5eXjtu0', (string) $etag);

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertSame(
            "MATCH (parent {id: \$parentUuid})\n".
            "MATCH (parent)-[:OWNS]->(children)\n".
            "MATCH (parent)-[relations]->(children)\n".
            "WITH children, relations\n".
            "LIMIT 101\n".
            "WITH children, relations\n".
            "ORDER BY children.id, relations.id\n".
            "WITH COLLECT([children.id, children.updated]) + COLLECT([relations.id, relations.updated]) AS rawTuples, count(children) as childrenCount\n".
            "CALL {\n".
            "  WITH rawTuples\n".
            "  UNWIND rawTuples as tuple\n".
            "  WITH tuple ORDER BY tuple[0]\n".
            "  RETURN COLLECT(tuple) AS sortedTuples\n".
            "}\n".
            'RETURN sortedTuples, childrenCount',
            $statement->getText()
        );

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for children collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculated Etag for children collection.'));
    }

    public function testCalculateChildrenCollectionEtagWithTooManyElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $resultList = [];
        for ($i = 0; $i < 101; ++$i) {
            $resultList[] = new CypherList([
                '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                new DateTimeZoneId(1705772003, 646811000, 'UTC'),
            ]);
        }
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => $resultList,
                    'childrenCount' => 101,
                ]),
            ]
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateChildrenCollectionEtag($uuid);

        // assert result
        $this->assertNull($etag);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for children collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculation of Etag for children collection stopped due to too many children.'));
    }

    public function testCalculateChildrenCollectionEtagDifferentObjectIsReturned(): void
    {
        if (array_key_exists('LEAK', $_ENV)) {
            $this->markTestSkipped();
        }
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([
                        new CypherList([
                            '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                            new stdClass(),
                        ]),
                    ]),
                    'childrenCount' => 1,
                ]),
            ]
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        $this->expectExceptionMessage('Unable to get DateTime from stdClass.');

        // run service method
        $etagCalculatorService->calculateChildrenCollectionEtag($uuid);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for children collection.'));
    }

    public function testCalculateChildrenCollectionEtagWhereNoDataIsReturned(): void
    {
        if (array_key_exists('LEAK', $_ENV)) {
            $this->markTestSkipped();
        }
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            []
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        $this->expectExceptionMessage('Unexpected result.');

        // run service method
        $etagCalculatorService->calculateChildrenCollectionEtag($uuid);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for children collection.'));
    }

    public function testCalculateParentsCollectionEtagWithExistingElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([
                        new CypherList([
                            '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                            new DateTimeZoneId(1705772003, 646811000, 'UTC'),
                        ]),
                        new CypherList([
                            '2c42deee-ad24-4f04-bb37-7c31fd5b3345',
                            new DateTimeZoneId(1705772003, 646811000, 'UTC'),
                        ]),
                    ]),
                    'parentsCount' => 1,
                ]),
            ]
        );
        /**
         * @var ?Statement $statement
         */
        $statement = null;

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::that(
            function ($internalStatement) use (&$statement) {
                $statement = $internalStatement;

                return true;
            }
        ))->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateParentsCollectionEtag($uuid);

        // assert result
        $this->assertSame('EKHX4b5HhHX', (string) $etag);

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertSame(
            "MATCH (child {id: \$childUuid})\n".
            "MATCH (child)<-[:OWNS]-(parents)\n".
            "MATCH (child)<-[relations]-(parents)\n".
            "WITH parents, relations\n".
            "LIMIT 101\n".
            "WITH parents, relations\n".
            "ORDER BY parents.id, relations.id\n".
            "WITH COLLECT([parents.id, parents.updated]) + COLLECT([relations.id, relations.updated]) AS rawTuples, count(parents) as parentsCount\n".
            "CALL {\n".
            "  WITH rawTuples\n".
            "  UNWIND rawTuples as tuple\n".
            "  WITH tuple ORDER BY tuple[0]\n".
            "  RETURN COLLECT(tuple) AS sortedTuples\n".
            "}\n".
            'RETURN sortedTuples, parentsCount',
            $statement->getText()
        );

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for parents collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculated Etag for parents collection.'));
    }

    public function testCalculateParentsCollectionEtagWithNoElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([]),
                    'parentsCount' => 0,
                ]),
            ]
        );
        /**
         * @var ?Statement $statement
         */
        $statement = null;

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::that(
            function ($internalStatement) use (&$statement) {
                $statement = $internalStatement;

                return true;
            }
        ))->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateParentsCollectionEtag($uuid);

        // assert result
        $this->assertSame('3F8H5eXjtu0', (string) $etag);

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertSame(
            "MATCH (child {id: \$childUuid})\n".
            "MATCH (child)<-[:OWNS]-(parents)\n".
            "MATCH (child)<-[relations]-(parents)\n".
            "WITH parents, relations\n".
            "LIMIT 101\n".
            "WITH parents, relations\n".
            "ORDER BY parents.id, relations.id\n".
            "WITH COLLECT([parents.id, parents.updated]) + COLLECT([relations.id, relations.updated]) AS rawTuples, count(parents) as parentsCount\n".
            "CALL {\n".
            "  WITH rawTuples\n".
            "  UNWIND rawTuples as tuple\n".
            "  WITH tuple ORDER BY tuple[0]\n".
            "  RETURN COLLECT(tuple) AS sortedTuples\n".
            "}\n".
            'RETURN sortedTuples, parentsCount',
            $statement->getText()
        );

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for parents collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculated Etag for parents collection.'));
    }

    public function testCalculateParentsCollectionEtagWithTooManyElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $resultList = [];
        for ($i = 0; $i < 101; ++$i) {
            $resultList[] = new CypherList([
                '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                new DateTimeZoneId(1705772003, 646811000, 'UTC'),
            ]);
        }
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => $resultList,
                    'parentsCount' => 101,
                ]),
            ]
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateParentsCollectionEtag($uuid);

        // assert result
        $this->assertNull($etag);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for parents collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculation of Etag for parents collection stopped due to too many parents.'));
    }

    public function testCalculateParentsCollectionEtagDifferentObjectIsReturned(): void
    {
        if (array_key_exists('LEAK', $_ENV)) {
            $this->markTestSkipped();
        }
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([
                        new CypherList([
                            '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                            new stdClass(),
                        ]),
                    ]),
                    'parentsCount' => 1,
                ]),
            ]
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        $this->expectExceptionMessage('Unable to get DateTime from stdClass.');

        // run service method
        $etagCalculatorService->calculateParentsCollectionEtag($uuid);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for parents collection.'));
    }

    public function testCalculateParentsCollectionEtagWhereNoDataIsReturned(): void
    {
        if (array_key_exists('LEAK', $_ENV)) {
            $this->markTestSkipped();
        }
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            []
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        $this->expectExceptionMessage('Unexpected result.');

        // run service method
        $etagCalculatorService->calculateParentsCollectionEtag($uuid);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for parents collection.'));
    }

    public function testCalculateRelatedCollectionEtagWithExistingElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([
                        new CypherList([
                            '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                            new DateTimeZoneId(1705772003, 646811000, 'UTC'),
                        ]),
                        new CypherList([
                            '2c42deee-ad24-4f04-bb37-7c31fd5b3345',
                            new DateTimeZoneId(1705772003, 646811000, 'UTC'),
                        ]),
                    ]),
                    'relatedCount' => 1,
                ]),
            ]
        );
        /**
         * @var ?Statement $statement
         */
        $statement = null;

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::that(
            function ($internalStatement) use (&$statement) {
                $statement = $internalStatement;

                return true;
            }
        ))->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateRelatedCollectionEtag($uuid);

        // assert result
        $this->assertSame('EKHX4b5HhHX', (string) $etag);

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertSame(
            "MATCH (center {id: \$centerUuid})\n".
            "MATCH (center)-[relations]-(related)\n".
            "WITH related, relations\n".
            "LIMIT 101\n".
            "WITH related, relations\n".
            "ORDER BY related.id, relations.id\n".
            "WITH COLLECT([related.id, related.updated]) + COLLECT([relations.id, relations.updated]) AS rawTuples, count(related) as relatedCount\n".
            "CALL {\n".
            "  WITH rawTuples\n".
            "  UNWIND rawTuples as tuple\n".
            "  WITH tuple ORDER BY tuple[0]\n".
            "  RETURN COLLECT(tuple) AS sortedTuples\n".
            "}\n".
            'RETURN sortedTuples, relatedCount',
            $statement->getText()
        );

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for related collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculated Etag for related collection.'));
    }

    public function testCalculateRelatedCollectionEtagWithNoElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([]),
                    'relatedCount' => 0,
                ]),
            ]
        );
        /**
         * @var ?Statement $statement
         */
        $statement = null;

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::that(
            function ($internalStatement) use (&$statement) {
                $statement = $internalStatement;

                return true;
            }
        ))->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateRelatedCollectionEtag($uuid);

        // assert result
        $this->assertSame('3F8H5eXjtu0', (string) $etag);

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertSame(
            "MATCH (center {id: \$centerUuid})\n".
            "MATCH (center)-[relations]-(related)\n".
            "WITH related, relations\n".
            "LIMIT 101\n".
            "WITH related, relations\n".
            "ORDER BY related.id, relations.id\n".
            "WITH COLLECT([related.id, related.updated]) + COLLECT([relations.id, relations.updated]) AS rawTuples, count(related) as relatedCount\n".
            "CALL {\n".
            "  WITH rawTuples\n".
            "  UNWIND rawTuples as tuple\n".
            "  WITH tuple ORDER BY tuple[0]\n".
            "  RETURN COLLECT(tuple) AS sortedTuples\n".
            "}\n".
            'RETURN sortedTuples, relatedCount',
            $statement->getText()
        );

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for related collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculated Etag for related collection.'));
    }

    public function testCalculateRelatedCollectionEtagWithTooManyElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $resultList = [];
        for ($i = 0; $i < 101; ++$i) {
            $resultList[] = new CypherList([
                '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                new DateTimeZoneId(1705772003, 646811000, 'UTC'),
            ]);
        }
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => $resultList,
                    'relatedCount' => 101,
                ]),
            ]
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateRelatedCollectionEtag($uuid);

        // assert result
        $this->assertNull($etag);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for related collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculation of Etag for related collection stopped due to too many related elements.'));
    }

    public function testCalculateRelatedCollectionEtagDifferentObjectIsReturned(): void
    {
        if (array_key_exists('LEAK', $_ENV)) {
            $this->markTestSkipped();
        }
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([
                        new CypherList([
                            '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                            new stdClass(),
                        ]),
                    ]),
                    'relatedCount' => 1,
                ]),
            ]
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        $this->expectExceptionMessage('Unable to get DateTime from stdClass.');

        // run service method
        $etagCalculatorService->calculateRelatedCollectionEtag($uuid);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for related collection.'));
    }

    public function testCalculateRelatedCollectionEtagWhereNoDataIsReturned(): void
    {
        if (array_key_exists('LEAK', $_ENV)) {
            $this->markTestSkipped();
        }
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            []
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        $this->expectExceptionMessage('Unexpected result.');

        // run service method
        $etagCalculatorService->calculateRelatedCollectionEtag($uuid);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for related collection.'));
    }

    public function testCalculateIndexCollectionEtagWithExistingElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([
                        new CypherList([
                            '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                            new DateTimeZoneId(1705772003, 646811000, 'UTC'),
                        ]),
                        new CypherList([
                            '2c42deee-ad24-4f04-bb37-7c31fd5b3345',
                            new DateTimeZoneId(1705772003, 646811000, 'UTC'),
                        ]),
                    ]),
                    'elementsCount' => 2,
                ]),
            ]
        );
        /**
         * @var ?Statement $statement
         */
        $statement = null;

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::that(
            function ($internalStatement) use (&$statement) {
                $statement = $internalStatement;

                return true;
            }
        ))->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateIndexCollectionEtag($uuid);

        // assert result
        $this->assertSame('EKHX4b5HhHX', (string) $etag);

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertSame(
            "MATCH (user:User {id: \$userUuid})\n".
            "MATCH (user)-[:OWNS|IS_IN_GROUP|HAS_READ_ACCESS]->(elements)\n".
            "WITH elements\n".
            "LIMIT 101\n".
            "WITH elements\n".
            "ORDER BY elements.id\n".
            "WITH COLLECT([elements.id, elements.updated]) AS rawTuples, count(elements) as elementsCount\n".
            "CALL {\n".
            "  WITH rawTuples\n".
            "  UNWIND rawTuples as tuple\n".
            "  WITH tuple ORDER BY tuple[0]\n".
            "  RETURN COLLECT(tuple) AS sortedTuples\n".
            "}\n".
            'RETURN sortedTuples, elementsCount',
            $statement->getText()
        );

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for index collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculated Etag for index collection.'));
    }

    public function testCalculateIndexCollectionEtagWithNoElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([]),
                    'elementsCount' => 0,
                ]),
            ]
        );
        /**
         * @var ?Statement $statement
         */
        $statement = null;

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::that(
            function ($internalStatement) use (&$statement) {
                $statement = $internalStatement;

                return true;
            }
        ))->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateIndexCollectionEtag($uuid);

        // assert result
        $this->assertSame('3F8H5eXjtu0', (string) $etag);

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertSame(
            "MATCH (user:User {id: \$userUuid})\n".
            "MATCH (user)-[:OWNS|IS_IN_GROUP|HAS_READ_ACCESS]->(elements)\n".
            "WITH elements\n".
            "LIMIT 101\n".
            "WITH elements\n".
            "ORDER BY elements.id\n".
            "WITH COLLECT([elements.id, elements.updated]) AS rawTuples, count(elements) as elementsCount\n".
            "CALL {\n".
            "  WITH rawTuples\n".
            "  UNWIND rawTuples as tuple\n".
            "  WITH tuple ORDER BY tuple[0]\n".
            "  RETURN COLLECT(tuple) AS sortedTuples\n".
            "}\n".
            'RETURN sortedTuples, elementsCount',
            $statement->getText()
        );

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for index collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculated Etag for index collection.'));
    }

    public function testCalculateIndexCollectionEtagWithTooManyElements(): void
    {
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $resultList = [];
        for ($i = 0; $i < 101; ++$i) {
            $resultList[] = new CypherList([
                '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                new DateTimeZoneId(1705772003, 646811000, 'UTC'),
            ]);
        }
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => $resultList,
                    'elementsCount' => 101,
                ]),
            ]
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        // run service method
        $etag = $etagCalculatorService->calculateIndexCollectionEtag($uuid);

        // assert result
        $this->assertNull($etag);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for index collection.'));
        $this->assertTrue($logger->records->includeMessagesContaining('Calculation of Etag for index collection stopped due to too many index elements.'));
    }

    public function testCalculateIndexCollectionEtagDifferentObjectIsReturned(): void
    {
        if (array_key_exists('LEAK', $_ENV)) {
            $this->markTestSkipped();
        }
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            [
                new CypherMap([
                    'sortedTuples' => new CypherList([
                        new CypherList([
                            '06f5da99-dfca-43c9-9d5f-3254c0d5f3c9',
                            new stdClass(),
                        ]),
                    ]),
                    'elementsCount' => 1,
                ]),
            ]
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagSeed()->shouldBeCalledOnce()->willReturn('seed');
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        $this->expectExceptionMessage('Unable to get DateTime from stdClass.');

        // run service method
        $etagCalculatorService->calculateIndexCollectionEtag($uuid);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for index collection.'));
    }

    public function testCalculateIndexCollectionEtagWhereNoDataIsReturned(): void
    {
        if (array_key_exists('LEAK', $_ENV)) {
            $this->markTestSkipped();
        }
        // setup variables
        $uuid = Uuid::fromString('224a787e-3b32-4822-8697-61047175505d');
        $null = null;
        $queryResult = new SummarizedResult(
            $null,
            []
        );

        // setup service dependencies
        $emberNexusConfiguration = $this->prophesize(EmberNexusConfiguration::class);
        $emberNexusConfiguration->getCacheEtagUpperLimitInCollectionEndpoints()->shouldBeCalledOnce()->willReturn(100);

        $clientInterface = $this->prophesize(ClientInterface::class);
        $clientInterface->runStatement(Argument::any())->shouldBeCalledOnce()->willReturn($queryResult);

        $cypherEntityManager = $this->prophesize(CypherEntityManager::class);
        $cypherEntityManager->getClient()->shouldBeCalledOnce()->willReturn($clientInterface->reveal());

        $logger = TestLogger::create();

        // setup service
        $etagCalculatorService = new EtagCalculatorService(
            $emberNexusConfiguration->reveal(),
            $cypherEntityManager->reveal(),
            $logger
        );

        $this->expectExceptionMessage('Unexpected result.');

        // run service method
        $etagCalculatorService->calculateIndexCollectionEtag($uuid);

        // assert logs
        $this->assertTrue($logger->records->includeMessagesContaining('Calculating Etag for index collection.'));
    }
}
