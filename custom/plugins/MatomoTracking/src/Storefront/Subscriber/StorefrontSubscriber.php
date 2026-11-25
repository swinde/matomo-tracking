<?php declare(strict_types=1);

namespace MatomoTracking\Storefront\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
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
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
        ];
    }

    private function getTrackingData(): ?array
    {
        $active = $this->systemConfigService->get(
            self::CONFIG_PREFIX . 'active'
        );

        if ($active !== true) {
            return null;
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
            return null;
        }

        return [
            'baseUrl' => rtrim(trim($baseUrl), '/') . '/',
            'siteId' => (int) $siteId,
            'trackEcommerce' => $this->systemConfigService->get(
                    self::CONFIG_PREFIX . 'trackEcommerce'
                ) === true,
        ];
    }

    public function onGenericPageLoaded(
        GenericPageLoadedEvent $event
    ): void {
        $trackingData = $this->getTrackingData();

        if ($trackingData === null) {
            return;
        }

        $event->getPage()->addExtension(
            'matomoTracking',
            new ArrayStruct($trackingData)
        );
    }

    public function onProductPageLoaded(
        ProductPageLoadedEvent $event
    ): void {
        $trackingData = $this->getTrackingData();

        if ($trackingData === null) {
            return;
        }

        if ($trackingData['trackEcommerce']) {
            $product = $event->getPage()->getProduct();

            $trackingData['product'] = [
                'sku' => $product->getProductNumber(),
                'name' => $product->getName() ?? '',
                'category' => $product->getSeoCategory()?->getName() ?? '',
                'price' => $product->getCalculatedPrice()->getUnitPrice(),
            ];
        }

        $event->getPage()->addExtension(
            'matomoTracking',
            new ArrayStruct($trackingData)
        );
    }
}
