<?php

declare(strict_types=1);

namespace NeosRulez\Neos\Cloudflare\ContentRepository;

/*
 * This file is part of the NeosRulez.Neos.Cloudflare package.
 */

use Neos\ContentRepository\Core\EventStore\EventInterface;
use Neos\ContentRepository\Core\Feature\WorkspacePublication\Event\WorkspaceWasPublished;
use Neos\ContentRepository\Core\Projection\CatchUpHook\CatchUpHookInterface;
use Neos\ContentRepository\Core\Subscription\SubscriptionStatus;
use Neos\EventStore\Model\EventEnvelope;
use NeosRulez\Neos\Cloudflare\Domain\Service\ProxyCacheService;

/**
 * Flushes the Cloudflare proxy caches whenever content is published to the live workspace.
 *
 * This replaces the "afterNodePublishing" signal of the Neos 8 content repository, which does
 * not exist anymore in the event sourced content repository of Neos 9.
 */
final class FlushProxyCacheCatchUpHook implements CatchUpHookInterface
{
    private bool $isBooting = false;

    private bool $flushRequired = false;

    public function __construct(
        private readonly ProxyCacheService $proxyCacheService
    ) {
    }

    public function onBeforeCatchUp(SubscriptionStatus $subscriptionStatus): void
    {
        // A replay/setup does not change live content, so it must never purge the proxy caches
        $this->isBooting = $subscriptionStatus === SubscriptionStatus::BOOTING;
    }

    public function onBeforeEvent(EventInterface $eventInstance, EventEnvelope $eventEnvelope): void
    {
        // Nothing to do here
    }

    public function onAfterEvent(EventInterface $eventInstance, EventEnvelope $eventEnvelope): void
    {
        if ($this->isBooting) {
            return;
        }
        if (!$eventInstance instanceof WorkspaceWasPublished) {
            return;
        }
        if (!$eventInstance->targetWorkspaceName->isLive()) {
            return;
        }
        // Only remember that a flush is due: the HTTP request to Cloudflare must not happen
        // inside the projection transaction, which still holds the database lock at this point
        $this->flushRequired = true;
    }

    public function onAfterBatchCompleted(): void
    {
        // Nothing to do here
    }

    public function onAfterCatchUp(): void
    {
        if (!$this->flushRequired) {
            return;
        }
        // Reset first, so a failing flush is not retried on every subsequent catch up
        $this->flushRequired = false;
        $this->proxyCacheService->flushAll();
    }
}
