<?php

declare(strict_types=1);

namespace Yoti\ShareUrl;

use Yoti\Constants;
use Yoti\Exception\base\YotiException;
use Yoti\Http\Payload;
use Yoti\Http\RequestBuilder;
use Yoti\Http\Response;
use Yoti\Util\Config;
use Yoti\Util\Json;
use Yoti\Util\PemFile;

class Service
{
    /**
     * @var string
     */
    private $sdkId;

    /**
     * @var \Yoti\Util\PemFile
     */
    private $pemFile;

    /**
     * @var \Yoti\Util\Config
     */
    private $config;

    /**
     * @param string $sdkId
     * @param \Yoti\Util\PemFile $pemFile
     * @param \Yoti\Util\Config $config
     */
    public function __construct(string $sdkId, PemFile $pemFile, Config $config)
    {
        $this->sdkId = $sdkId;
        $this->pemFile = $pemFile;
        $this->config = $config;
    }

    /**
     * @param DynamicScenario $dynamicScenario
     *
     * @return Result
     *
     * @throws YotiException
     */
    public function createShareUrl(DynamicScenario $dynamicScenario): Result
    {
        $response = (new RequestBuilder($this->config))
            ->withBaseUrl($this->config->getApiUrl() ?? Constants::API_URL)
            ->withEndpoint(sprintf('/qrcodes/apps/%s', $this->sdkId))
            ->withQueryParam('appId', $this->sdkId)
            ->withPost()
            ->withPayload(Payload::fromJsonData($dynamicScenario))
            ->withPemFile($this->pemFile)
            ->build()
            ->execute();

        $httpCode = $response->getStatusCode();
        if ($httpCode < 200 || $httpCode > 299) {
            Response::createYotiExceptionFromStatusCode($response);
        }

        return new Result(Json::decode((string) $response->getBody()));
    }
}
