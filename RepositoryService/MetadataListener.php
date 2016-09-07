<?php

namespace DocteurKlein\RepositoryService;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

final class MetadataListener implements EventSubscriber
{
    private $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        $meta = $event->getClassMetadata();
        if (!isset($this->map[$meta->name])) {
            return;
        }
        if (!empty($meta->customRepositoryClassName)) {
            return ;
        }
        $meta->setCustomRepositoryClass($this->map[$meta->name]);
    }
}
