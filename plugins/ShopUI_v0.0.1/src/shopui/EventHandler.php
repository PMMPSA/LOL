<?php


namespace shopui;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use shopui\form\ItemInteractForm;

class EventHandler implements Listener
{
    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        unset(ItemInteractForm::$users[$player->getLowerCaseName()]);
    }
}