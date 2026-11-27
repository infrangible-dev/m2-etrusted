<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Test\Unit\Model;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Stores;
use Infrangible\ETrusted\Model\AccessToken;
use Infrangible\ETrusted\Model\AggregateRating;
use Infrangible\ETrusted\Model\Api;
use Infrangible\ETrusted\Model\Cache\Type\ETrusted;
use Infrangible\ETrusted\Model\Reviews;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Tests the requests to eTrusted: access token, aggregate rating and reviews, the cache and errors.
 */
class ApiTest extends TestCase
{
    /** @var array<string, string> the configuration of the store */
    private array $config = [
        AccessToken::CONFIG_CLIENT_ID => 'client',
        AccessToken::CONFIG_SECRET    => 'encrypted:secret',
        Api::CONFIG_CHANNEL_ID        => 'chl-1'
    ];

    /** @var array<string, array{0: string, 1: int}> the cached data with lifetime by cache ID */
    private array $cache = [];

    /** @var array<int, array{url: string, headers: array<string, string>, form: array<string, string>|null}> */
    private array $requests = [];

    /** @var array<string, array{0: int, 1: string}> the status and body of the responses by URL */
    private array $responses = [];

    /** @var string[] the logged errors */
    private array $errors = [];

    /**
     * Returns the services of the models.
     *
     * @return array{ETrusted, Stores, Json, Variables, LoggerInterface, CurlFactory}
     */
    private function createServices(): array
    {
        $cache = $this->createStub(ETrusted::class);
        $cache->method('load')->willReturnCallback(fn(string $id): string|false => $this->cache[ $id ][ 0 ] ?? false);
        $cache->method('save')->willReturnCallback(
            function (string $data, string $id, array $tags, int $lifetime): bool {
                $this->cache[ $id ] = [$data, $lifetime];

                return true;
            }
        );

        $storeHelper = $this->createStub(Stores::class);
        $storeHelper->method('getStoreConfig')->willReturnCallback(
            fn(string $path): ?string => $this->config[ $path ] ?? null
        );

        $client = $this->createStub(Curl::class);
        $headers = [];
        $url = '';
        $client->method('setHeaders')->willReturnCallback(
            function (array $value) use (&$headers): void {
                $headers = $value;
            }
        );
        $client->method('get')->willReturnCallback(
            function (string $value) use (&$url, &$headers): void {
                $url = $value;
                $this->requests[] = ['url' => $value, 'headers' => $headers, 'form' => null];
            }
        );
        $client->method('post')->willReturnCallback(
            function (string $value, array $form) use (&$url, &$headers): void {
                $url = $value;
                $this->requests[] = ['url' => $value, 'headers' => $headers, 'form' => $form];
            }
        );
        $client->method('getStatus')->willReturnCallback(
            function () use (&$url): int {
                return $this->responses[ $url ][ 0 ] ?? 404;
            }
        );
        $client->method('getBody')->willReturnCallback(
            function () use (&$url): string {
                return $this->responses[ $url ][ 1 ] ?? '';
            }
        );

        $curlFactory = $this->createStub(CurlFactory::class);
        $curlFactory->method('create')->willReturn($client);

        $logging = $this->createStub(LoggerInterface::class);
        $logging->method('error')->willReturnCallback(
            function (string|Stringable $message): void {
                $this->errors[] = (string)$message;
            }
        );

        return [$cache, $storeHelper, new Json(), new Variables(), $logging, $curlFactory];
    }

    /**
     * Returns the access token model.
     *
     * @return AccessToken
     */
    private function createAccessToken(): AccessToken
    {
        $encryptor = $this->createStub(EncryptorInterface::class);
        $encryptor->method('decrypt')->willReturnCallback(
            fn(string $value): string => str_starts_with($value, 'encrypted:') ? substr($value, 10) : ''
        );

        [$cache, $storeHelper, $json, $variables, $logging, $curlFactory] = $this->createServices();

        return new AccessToken($cache, $storeHelper, $json, $variables, $logging, $curlFactory, $encryptor);
    }

    /**
     * Returns the reviews model.
     *
     * @return Reviews
     */
    private function createReviews(): Reviews
    {
        [$cache, $storeHelper, $json, $variables, $logging, $curlFactory] = $this->createServices();

        return new Reviews(
            $cache,
            $storeHelper,
            $json,
            $variables,
            $logging,
            $curlFactory,
            $this->createAccessToken()
        );
    }

    /**
     * Returns the aggregate rating model.
     *
     * @return AggregateRating
     */
    private function createAggregateRating(): AggregateRating
    {
        [$cache, $storeHelper, $json, $variables, $logging, $curlFactory] = $this->createServices();

        return new AggregateRating(
            $cache,
            $storeHelper,
            $json,
            $variables,
            $logging,
            $curlFactory,
            $this->createAccessToken()
        );
    }

    /**
     * Lets eTrusted issue an access token.
     *
     * @return void
     */
    private function respondToken(): void
    {
        $this->responses[ AccessToken::URL ] = [200, '{"access_token":"token-1","expires_in":86400}'];
    }

    /**
     * Tests the request of the access token with the decrypted secret, and that it is cached a minute less than it is
     * valid.
     *
     * @return void
     */
    public function testAccessToken(): void
    {
        $this->respondToken();

        $this->assertSame('token-1', $this->createAccessToken()->getToken());
        $this->assertSame('token-1', $this->createAccessToken()->getToken());

        $this->assertCount(1, $this->requests);
        $this->assertSame(AccessToken::URL, $this->requests[ 0 ][ 'url' ]);
        $this->assertSame(
            [
                'client_id'     => 'client',
                'client_secret' => 'secret',
                'grant_type'    => 'client_credentials',
                'audience'      => 'https://api.etrusted.com'
            ],
            $this->requests[ 0 ][ 'form' ]
        );
        $this->assertSame([86340], array_column($this->cache, 1));
    }

    /**
     * Without client ID or secret nothing is requested; a declined request is logged and not cached.
     *
     * @return void
     */
    public function testAccessTokenErrors(): void
    {
        $this->config[ AccessToken::CONFIG_SECRET ] = '';

        $this->assertSame('', $this->createAccessToken()->getToken());
        $this->assertSame([], $this->requests);

        $this->config[ AccessToken::CONFIG_SECRET ] = 'encrypted:wrong';
        $this->responses[ AccessToken::URL ] = [401, '{"error":"access_denied"}'];

        $this->assertSame('', $this->createAccessToken()->getToken());
        $this->assertCount(1, $this->requests);
        $this->assertSame([], $this->cache);
        $this->assertCount(1, $this->errors);
        $this->assertStringContainsString('status 401', $this->errors[ 0 ]);
    }

    /**
     * Tests the request of the reviews with filters and the access token, and the cache by request: other filters or
     * another channel are requested again.
     *
     * @return void
     */
    public function testReviews(): void
    {
        $this->respondToken();

        $url = 'https://api.etrusted.com/reviews-minimal?channels=chl-1&count=3&rating=4,5&status=APPROVED' .
            '&type=SERVICE_REVIEW';
        $this->responses[ $url ] = [200, '{"items":[{"rating":5,"comment":"Great"}]}'];

        $reviews = $this->createReviews();

        $this->assertSame(
            [['rating' => 5, 'comment' => 'Great']],
            $reviews->getReviews(3, ['4', '5'], ['APPROVED'], ['SERVICE_REVIEW'])
        );
        $this->assertSame(
            [['rating' => 5, 'comment' => 'Great']],
            $this->createReviews()->getReviews(3, ['4', '5'], ['APPROVED'], ['SERVICE_REVIEW'])
        );
        $this->assertSame([AccessToken::URL, $url], array_column($this->requests, 'url'));
        $this->assertSame('Bearer token-1', $this->requests[ 1 ][ 'headers' ][ 'Authorization' ]);

        $this->assertSame([], $reviews->getReviews(3));
        $this->config[ Api::CONFIG_CHANNEL_ID ] = 'chl-2';
        $this->assertSame([], $reviews->getReviews(3, ['4', '5'], ['APPROVED'], ['SERVICE_REVIEW']));

        $this->assertSame(
            [
                AccessToken::URL,
                $url,
                'https://api.etrusted.com/reviews-minimal?channels=chl-1&count=3',
                'https://api.etrusted.com/reviews-minimal?channels=chl-2&count=3&rating=4,5&status=APPROVED' .
                '&type=SERVICE_REVIEW'
            ],
            array_column($this->requests, 'url')
        );
    }

    /**
     * Without channel nothing is requested; without access token the error is logged.
     *
     * @return void
     */
    public function testReviewsErrors(): void
    {
        $this->config[ Api::CONFIG_CHANNEL_ID ] = ' ';

        $this->assertSame([], $this->createReviews()->getReviews(3));
        $this->assertSame([], $this->requests);

        $this->config[ Api::CONFIG_CHANNEL_ID ] = 'chl-1';
        $this->config[ AccessToken::CONFIG_CLIENT_ID ] = '';

        $this->assertSame([], $this->createReviews()->getReviews(3));
        $this->assertSame([], $this->requests);
        $this->assertCount(1, $this->errors);
        $this->assertStringContainsString('no access token', $this->errors[ 0 ]);
    }

    /**
     * Tests the request of the aggregate rating, the channel is encoded; a response that is no JSON is logged.
     *
     * @return void
     */
    public function testAggregateRating(): void
    {
        $this->respondToken();
        $this->config[ Api::CONFIG_CHANNEL_ID ] = 'chl 1/2';

        $url = 'https://api.etrusted.com/channels/chl%201%2F2/service-reviews/aggregate-rating';
        $this->responses[ $url ] = [200, '{"7days":{"count":3,"rating":4.67}}'];

        $this->assertSame(
            ['7days' => ['count' => 3, 'rating' => 4.67]],
            $this->createAggregateRating()->getAggregateRating()
        );

        $this->cache = [];
        $this->responses[ $url ] = [200, '<html lang="en">maintenance</html>'];

        $this->assertSame([], $this->createAggregateRating()->getAggregateRating());
        $this->assertCount(1, $this->errors);
    }
}
