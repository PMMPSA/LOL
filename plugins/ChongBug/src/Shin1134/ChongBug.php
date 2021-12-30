<?php

namespace Shin1134;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBlockPickEvent;
//use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
class ChongBug extends PluginBase implements Listener{
 
	public static $instance;
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getLogger()->info("§c➡➡➡➡➡➡➡➡➡➡➡➡➡\n\n§l§f•§b ChongBug By Shin1134 Enable\n\n§c➡➡➡➡➡➡➡➡➡➡➡➡➡");
		if(!is_file($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}	
		self::$instance = $this;
	}

	public function onMiddleclick(PlayerBlockPickEvent $e){
        //print("a");
        if($e->getPlayer()->getGamemode() == 1){
        $e->setCancelled();
		$e->getPlayer()->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Muốn bug à? không dễ đâu nhóc!");
		}
	}
}
	
    /*public function onItemHeld(PlayerItemHeldEvent $ev){
		$p = $ev->getPlayer();
		$i = $p->getInventory()->getItemInHand();
		$icn = $i->getCustomName();
		$pas = explode(" ", $icn);
		if($icn == "§r§l§6✦§d Cúp §bMine§aFore§6 ✦"){
			if($i->hasEnchantment(18)){
				$o = $i->getEnchantment(18)->getLevel();
				if($o == 1000){
				   $i->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(18),500));
				   $i->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(15),500));
				   $i->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(17),500));
				   $p->getInventory()->setItemInHand($i);
				}
			}
		}
		
		if($pas[0] == "§l§6Kiếm"){
		   $p->getInventory()->removeItem($p->getInventory()->getItemInHand());
		   $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "givemoney " .  $p->getName() . " 100000000");
		   $p->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã được bồi thưởng 100m vì đây là đồ lậu!");
		}
		
		if($pas[0] == "§r§l§6✦§b"){
		   $p->getInventory()->removeItem($p->getInventory()->getItemInHand());
		   $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "givemoney " .  $p->getName() . " 100000000");
		   $p->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã được bồi thưởng 100m vì đây là đồ lậu!");
		}
	}
}*/
