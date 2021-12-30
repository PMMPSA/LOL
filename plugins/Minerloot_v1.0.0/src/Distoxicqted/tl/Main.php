<?php

namespace Distoxicqted\tl;

use pocketmine\plugin\PluginBase as P;
use pocketmine\event\Listener as L;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\event\block\BlockBreakEvent as BBL;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\item\Item;

class Main extends P implements L{
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->saveDefaultConfig();
		$this->getServer()->getLogger()->info(TextFormat::GREEN."[Minerloot] Activated!");
	}

	public function onBreak(BBL $e){
		if($e->getBlock()->getId() == 133 && mt_rand(0,10000) == "1"){
			$p = $e->getPlayer();
			$this->getServer()->broadcastMessage(TextFormat::LIGHT_PURPLE.TextFormat::BOLD."§l§6[§aʟ§cᴏ§aʟ§6] ".TextFormat::GREEN.$p->getName().TextFormat::AQUA."§l tìm thấy ".TextFormat::BOLD.TextFormat::YELLOW."§l1 key weapons".TextFormat::RESET.TextFormat::AQUA."§l từ việc đi mine!");
		    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "key weapons " .  $p->getName() . " 1");               	
		}
		else{
		}
		if($e->getBlock()->getId() == 57 && mt_rand(0,10000) == "1"){
			$p = $e->getPlayer();
			$this->getServer()->broadcastMessage(TextFormat::LIGHT_PURPLE.TextFormat::BOLD."§l§6[§aʟ§cᴏ§aʟ§6] ".TextFormat::GREEN.$p->getName().TextFormat::AQUA."§l tìm thấy ".TextFormat::BOLD.TextFormat::YELLOW."§l1 key gun".TextFormat::RESET.TextFormat::AQUA."§l từ việc đi mine!");
		    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "key gun " .  $p->getName() . " 1");               	
		}
		else{
		}
	}
}
