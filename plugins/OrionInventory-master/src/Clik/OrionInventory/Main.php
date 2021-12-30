<?php

namespace Clik\OrionInventory;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\Plugin\PluginBase;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityLevelChangeEvent;

class Main extends PluginBase implements Listener{
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
	}
	
   public function onWorldChange(EntityLevelChangeEvent $event)
    {
        if($event->getEntity() instanceof Player){
			$level = $event->getTarget()->getFolderName();
        if(in_array($level, array('boss', 'Koth'))){
        $p = $event->getEntity();
        if (!($p instanceof Player)) return;
		$p->setScale(1);
		$p->setAllowFlight(false);
		$p->setFlying(false);
        $p->setGamemode(0);
		$p->removeAllEffects();
			}
		}
	}
}