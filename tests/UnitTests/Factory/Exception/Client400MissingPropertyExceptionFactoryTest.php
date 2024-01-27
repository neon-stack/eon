<?php

namespace App\tests\UnitTests\Factory\Exception;

use App\Factory\Exception\Client400MissingPropertyExceptionFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Client400MissingPropertyExceptionFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateFromTemplate(): void
    {
        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $urlGenerator->generate(Argument::cetera())->shouldBeCalledOnce()->willReturn('https://mock.dev/123');
        $factory = new Client400MissingPropertyExceptionFactory($urlGenerator->reveal());

        $exception = $factory->createFromTemplate('a', 'b');

        $this->assertSame(400, $exception->getStatus());
        $this->assertSame('Missing property', $exception->getTitle());
        $this->assertSame('https://mock.dev/123', $exception->getType());
        $this->assertSame("Endpoint requires that the request contains property 'a' to be set to b.", $exception->getDetail());
        $this->assertSame(null, $exception->getInstance());
        $this->assertSame('', $exception->getMessage());
    }
}
