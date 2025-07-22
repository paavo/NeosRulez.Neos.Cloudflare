<?php
namespace NeosRulez\Neos\Cloudflare\Domain\Service;

/*
 * This file is part of the NeosRulez.Neos.Cloudflare package.
 */

use Neos\Flow\Annotations as Flow;
use Cloudflare\API\Auth\APIToken;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\Zones;
use Neos\Flow\Log\ThrowableStorageInterface;

class ProxyCacheService
{

    #[Flow\InjectConfiguration(path: 'proxyCache', package: 'NeosRulez.Neos.Cloudflare')]
    protected array $proxyCacheApiConfiguration;

    /**
     * @Flow\Inject
     * @var ThrowableStorageInterface
     */
    protected $throwableStorage;

    /**
     * @param string $apiKey
     * @return Guzzle
     */
    private function adapter(string $apiKey): Guzzle
    {
        $auth = new APIToken($apiKey);
        return new Guzzle($auth);
    }

    /**
     * @param Zones $zones
     * @param string $zoneName
     * @return bool
     */
    private function flush(Zones $zones, string $zoneName): bool
    {
        try {
            $zoneID = $zones->getZoneID($zoneName);
            if ($zoneID) {
                $result = $zones->cachePurgeEverything($zoneID);
                if ($result) {
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        } catch (\Throwable $e) {
            echo $e->getMessage() . ' "' . $zoneName . '"' . "\n";
            $this->throwableStorage->logThrowable($e);
        }
        return false;
    }

    /**
     * @param string|null $zoneName
     * @return array
     */
    public function flushProxyCache(string|null $zoneName = null): array
    {
        $result = [];
        if ($this->proxyCacheApiConfiguration) {
            foreach ($this->proxyCacheApiConfiguration as $configuration) {
                if (array_key_exists('apiKey', $configuration) && array_key_exists('zoneName', $configuration)) {
                    if ($zoneName === null || $zoneName === $configuration['zoneName']) {
                        $adapter = $this->adapter($configuration['apiKey']);
                        $zones = new Zones($adapter);
                        $result[] = [
                            'zoneName' => $configuration['zoneName'],
                            'result' => $this->flush($zones, $configuration['zoneName'])
                        ];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param string $zoneName
     * @return void
     */
    public function flushOne(string $zoneName): void
    {
        $this->flushProxyCache($zoneName);
    }

    /**
     * @return void
     */
    public function flushAll(): void
    {
        $this->flushProxyCache();
    }

}
