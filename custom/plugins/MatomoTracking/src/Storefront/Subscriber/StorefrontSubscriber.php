<?php declare(strict_types=1);

namespace MatomoTracking\Storefront\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StorefrontSubscriber implements EventSubscriberInterface
{
    private const CONFIG_PREFIX = 'MatomoTracking.config.';

    public function __construct(
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GenericPageLoadedEvent::class => 'onGenericPageLoaded',
        ];
    }

    public function onGenericPageLoaded(
        GenericPageLoadedEvent $event
    ): void {
        $active = $this->systemConfigService->get(
            self::CONFIG_PREFIX . 'active'
        );

        if ($active !== true) {
            return;
        }

        $baseUrl = $this->systemConfigService->get(
            self::CONFIG_PREFIX . 'baseUrl'
        );

        $siteId = $this->systemConfigService->get(
            self::CONFIG_PREFIX . 'siteId'
        );

        if (
            !is_string($baseUrl)
            || trim($baseUrl) === ''
            || !is_numeric($siteId)
        ) {
            return;
        }

        $event->getPage()->addExtension(
            'matomoTracking',
            new ArrayStruct([
                'baseUrl' => rtrim(trim($baseUrl), '/') . '/',
                'siteId' => (int) $siteId,
            ])
        );
    }
}
