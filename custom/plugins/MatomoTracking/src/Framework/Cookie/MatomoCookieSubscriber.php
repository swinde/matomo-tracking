<?php declare(strict_types=1);

namespace MatomoTracking\Framework\Cookie;

use Shopware\Core\Content\Cookie\Event\CookieGroupCollectEvent;
use Shopware\Core\Content\Cookie\Struct\CookieEntry;
use Shopware\Core\Content\Cookie\Struct\CookieEntryCollection;
use Shopware\Core\Content\Cookie\Struct\CookieGroup;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MatomoCookieSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CookieGroupCollectEvent::class => 'onCookieGroupCollect',
        ];
    }

    public function onCookieGroupCollect(CookieGroupCollectEvent $event): void
    {
        $group = new CookieGroup('matomo.cookie.group');
        $group->name = 'Matomo Tracking';
        $group->description = 'matomo.cookie.groupDescription';

        $entry = new CookieEntry('matomo-tracking');
        $entry->name = 'matomo.cookie.name';
        $entry->description = 'matomo.cookie.description';
        $entry->value = '1';
        $entry->expiration = 365;

        $entries = new CookieEntryCollection();
        $entries->add($entry);

        $group->setEntries($entries);

        $event->cookieGroupCollection->add($group);
    }
}
