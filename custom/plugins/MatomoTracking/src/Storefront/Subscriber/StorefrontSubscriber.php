<?php declare(strict_types=1);

namespace MatomoTracking\Storefront\Subscriber;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
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
            CheckoutFinishPageLoadedEvent::class => 'onCheckoutFinishPageLoaded',
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

        $anonymizeIp = $this->systemConfigService->get(
            self::CONFIG_PREFIX . 'anonymizeIp'
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
            'anonymizeIp' => $anonymizeIp !== false,
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

    public function onCheckoutFinishPageLoaded(
        CheckoutFinishPageLoadedEvent $event
    ): void {
        file_put_contents(
            sys_get_temp_dir() . '/matomo-order-debug.log',
            'Checkout-Methode aufgerufen' . PHP_EOL,
            FILE_APPEND
        );
        $trackingData = $this->getTrackingData();

        if (
            $trackingData === null
            || !$trackingData['trackEcommerce']
        ) {
            file_put_contents(
                sys_get_temp_dir() . '/matomo-order-debug.log',
                'ABBRUCH: Trackingdaten fehlen oder E-Commerce ist deaktiviert'
                . PHP_EOL,
                FILE_APPEND
            );
            return;
        }

        $order = $event->getPage()->getOrder();

        $items = [];

        foreach ($order->getLineItems() ?? [] as $lineItem) {
            // Nur echte Produktpositionen berücksichtigen.
            if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                continue;
            }

            $sku = $lineItem->getPayloadValue('productNumber');

            if (!is_string($sku) || $sku === '') {
                continue;
            }

            $items[] = [
                'sku' => $sku,
                'name' => $lineItem->getLabel(),
                'category' => '',
                'price' => $lineItem->getUnitPrice(),
                'quantity' => $lineItem->getQuantity(),
            ];
        }

        $tax = 0.0;

        foreach ($order->getPrice()->getCalculatedTaxes() as $calculatedTax) {
            $tax += $calculatedTax->getTax();
        }

        $trackingData['order'] = [
            'id' => $order->getId(),
            'number' => $order->getOrderNumber(),
            'total' => $order->getAmountTotal(),
            'subtotal' => $order->getPositionPrice(),
            'shipping' => $order->getShippingTotal(),
            'tax' => $tax,
            'items' => $items,
        ];

        $event->getPage()->addExtension(
            'matomoTracking',
            new ArrayStruct($trackingData)
        );
        file_put_contents(
            sys_get_temp_dir() . '/matomo-order-debug.log',
            'PAGE-EXTENSION: ' . print_r(
                $event->getPage()->getExtension('matomoTracking'),
                true
            ) . PHP_EOL,
            FILE_APPEND
        );
    }
}
