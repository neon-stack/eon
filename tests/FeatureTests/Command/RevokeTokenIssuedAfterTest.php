<?php

namespace App\tests\FeatureTests\Command;

use App\Tests\FeatureTests\BaseRequestTestCase;

class RevokeTokenIssuedAfterTest extends BaseRequestTestCase
{
    private const string TOKEN = 'secret-token:IJA6RN4dKZENVerSXGp4H';
    private const string TOKEN_ISSUED_AFTER_UUID = 'fbb02626-c3e8-46de-93d4-ee33607dbc69';
    private const string USER_UUID = 'e1284efb-d66e-47fd-83b9-4c375ae1c9a2';

    public function testRevokeTokenIssuedAfter(): void
    {
        $response = $this->runGetRequest(sprintf('/%s', self::TOKEN_ISSUED_AFTER_UUID), self::TOKEN);
        $this->assertIsTokenWithState($response, 'ACTIVE');

        \Safe\exec(sprintf(
            'php bin/console token:revoke -f --user %s --issued-after "2021-01-01 00:00"',
            self::USER_UUID
        ));

        $response = $this->runGetRequest(sprintf('/%s', self::TOKEN_ISSUED_AFTER_UUID), self::TOKEN);
        $this->assertIsTokenWithState($response, 'REVOKED');
    }
}
