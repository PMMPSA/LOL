<?php

namespace noob;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\Player;
use noob\FixTask;


class AutoFix extends PluginBase implements Listener{
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$task = new FixTask($this);
		$this->getScheduler()->scheduleRepeatingTask($task, 20);
	}
	
    public function onItemHeld(PlayerItemHeldEvent $ev){
		$p = $ev->getPlayer();
		$i = $p->getInventory()->getItemInHand();
		if($p->hasPermission('autofix.mf')){
			if(in_array($i->getId(), array(278, 279, 293, 277, 276))){
				$damage = $i->getDamage();
				if ($damage > 100) {
					$i->setDamage(0);
					$p->getInventory()->setItemInHand($i);
				}
			}
		}
	}
	
	public function onFix(){
	  foreach($this->getServer()->getOnlinePlayers() as $ev) {
		$p = $ev->getPlayer();
		$e = $p->getArmorInventory();
		if($p->hasPermission('autofix.mf')){
			if($e->getHelmet() !== NULL){
				$damage = $e->getHelmet()->getDamage();
				if ($damage > 100) {
					$a = $e->getHelmet()->setDamage(0);
					$e->setHelmet($a);
				}
			}
			
			if($e->getChestplate() !== NULL){
				$damage = $e->getChestplate()->getDamage();
				if ($damage > 100) {
					$a = $e->getChestplate()->setDamage(0);
					$e->setChestplate($a);
				}
			}
			
			if($e->getLeggings() !== NULL){
				$damage = $e->getLeggings()->getDamage();
				if ($damage > 100) {
					$a = $e->getLeggings()->setDamage(0);
					$e->setLeggings($a);
				}
			}
			
			if($e->getBoots() !== NULL){
				$damage = $e->getBoots()->getDamage();
				if ($damage > 100) {
					$a = $e->getBoots()->setDamage(0);
					$e->setBoots($a);
				}
			}
		}
	  }
	}
}