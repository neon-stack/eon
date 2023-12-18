<?php

namespace App\tests\FeatureTests\Endpoint\WebDAV;

use App\Tests\FeatureTests\BaseRequestTestCase;

class MkcolElementTest extends BaseRequestTestCase
{
    private const string SOME_UUID = 'f686f998-cb92-4db2-89e5-d088e4ac34cc';

    public function testMkcolElementIsNotImplemented(): void
    {
        $response = $this->runMkcolRequest(sprintf('/%s', self::SOME_UUID), null);
        $this->assertIsProblemResponse($response, 501);
    }
}
