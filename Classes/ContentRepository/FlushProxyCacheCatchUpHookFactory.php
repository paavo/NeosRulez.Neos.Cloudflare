<?php

declare(strict_types=1);

namespace NeosRulez\Neos\Cloudflare\ContentRepository;

/*
 * This file is part of the NeosRulez.Neos.Cloudflare package.
 */

use Neos\ContentRepository\Core\Projection\CatchUpHook\CatchUpHookFactoryDependencies;
use Neos\ContentRepository\Core\Projection\CatchUpHook\CatchUpHookFactoryInterface;
use Neos\ContentRepository\Core\Projection\CatchUpHook\CatchUpHookInterface;
use Neos\ContentRepository\Core\Projection\ContentGraph\ContentGraphReadModelInterface;
use NeosRulez\Neos\Cloudflare\Domain\Service\ProxyCacheService;

/**
 * @implements CatchUpHookFactoryInterface<ContentGraphReadModelInterface>
 */
final class FlushProxyCacheCatchUpHookFactory implements CatchUpHookFactoryInterface
{
    public function __construct(
        private readonly ProxyCacheService $proxyCacheService
    ) {
    }

    public function build(CatchUpHookFactoryDependencies $dependencies): CatchUpHookInterface
    {
        return new FlushProxyCacheCatchUpHook($this->proxyCacheService);
    }
}
