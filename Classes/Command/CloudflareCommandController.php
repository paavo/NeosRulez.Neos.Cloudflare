<?php
namespace NeosRulez\Neos\Cloudflare\Command;

/*
 * This file is part of the NeosRulez.Neos.Cloudflare package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use NeosRulez\Neos\Cloudflare\Domain\Service\ProxyCacheService;

#[Flow\Scope("singleton")]
class CloudflareCommandController extends CommandController
{

    #[Flow\Inject]
    protected ProxyCacheService $proxyCacheService;

    /**
     * Flush cloudflare proxy caches
     *
     * @return void
     */
    public function flushProxyCacheCommand(): void
    {
        $result = true;
        foreach ($this->proxyCacheService->flushProxyCache() as $item) {
            if (!$item['result']) {
                $result = false;
            }
        }
        if ($result) {
            $this->outputLine('"Cloudflare proxy caches" are flushed.');
        } else {
            $this->outputLine('Not all "Cloudflare proxy caches" are flushed!');
        }
    }

}
