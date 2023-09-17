<?php

namespace App\Tests\ExampleGeneration\Error;

use App\Tests\ExampleGeneration\BaseRequestTestCase;

class Get403ForbiddenTest extends BaseRequestTestCase
{
    private const PATH_TO_ROOT = __DIR__.'/../../../';

    public function testRegisterSuccess(): void
    {
        $response = $this->runGetRequest('/error/403/forbidden', null);
        $documentationHeadersPath = 'docs/api-endpoints/error/get-403-forbidden/200-response-header.txt';
        $documentationBodyPath = 'docs/api-endpoints/error/get-403-forbidden/200-response-body.txt';
        $this->assertHeadersInDocumentationAreIdenticalToHeadersFromRequest(
            self::PATH_TO_ROOT,
            $documentationHeadersPath,
            $response
        );
        $this->assertBodyInDocumentationIsIdenticalToBodyFromRequest(
            self::PATH_TO_ROOT,
            $documentationBodyPath,
            $response,
            false
        );
    }
}