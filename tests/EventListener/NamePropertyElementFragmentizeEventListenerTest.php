<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Neo4j;

use App\Event\NodeElementFragmentizeEvent;
use App\EventListener\NamePropertyElementFragmentizeEventListener;
use App\Type\NodeElement;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Rfc4122\UuidV4;

class NamePropertyElementFragmentizeEventListenerTest extends TestCase
{
    public function testNodeElementToRaw(): void
    {
        $element = (new NodeElement())
            ->setLabel('Node')
            ->setIdentifier(UuidV4::fromString('78f61245-8cfd-4039-ac7e-61407fa7e969'))
            ->addProperty('name', 'some name');

        $event = new NodeElementFragmentizeEvent($element);
        $listener = new NamePropertyElementFragmentizeEventListener();
        $listener->onNodeElementFragmentizeEvent($event);
        $cypherFragment = $event->getCypherFragment();
        $documentFragment = $event->getDocumentFragment();

        $this->assertSame('some name', $cypherFragment->getProperty('name'));
        $this->assertSame('some name', $documentFragment->getProperty('name'));
    }
}
