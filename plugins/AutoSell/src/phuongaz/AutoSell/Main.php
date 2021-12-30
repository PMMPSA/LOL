<?php

namespace phuongaz\AutoSell;

use pocketmine\{Server, Player};
use pocketmine\command\{ConsoleCommandSender, Command, CommandSender};
use pocketmine\event\{Listener, block\BlockBreakEvent};
use pocketmine\plugin\PluginBase;

Class Main extends PluginBase implements Listener{
	
	public $prefix = "§7[§a Auto Sell §7]";
	public function onEnable():void{
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getLogger()->info("§l§a> §bPlugin make by phuongaz");
	   }
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args):bool{
		if(!$sender instanceof Player){
			$sender->sendMessage('use command in game');
			}
			if($cmd->getName() == "autosell"){
		       if(!isset($args[0])){
			      $sender->sendMessage($this->prefix." /autosell on | off");
			       return true;
		      }
		      if($args[0] == "on"){
			     $this->can[$sender->getName()] = true;
			   $sender->sendMessage($this->prefix."§a bạn đã bật chức năng tự động bán");
			}
			if($args[0] == "off"){
				if(isset($this->can[$sender->getName()])){
					unset($this->can[$sender->getName()]);
					$sender->sendMessage($this->prefix."§c Bạn đã tắt chức năng tự động bán");
					return true;
				}
				$sender->sendMessage($this->prefix."§c Bạn chưa bật chức năng tự động bán");
			}
	    }
		return true;
		}
	public function onBreak(BlockBreakEvent $event) {
		$player = $event->getPlayer();
		if(isset($this->can[$player->getName()])){
		foreach($event->getDrops() as $drop) {
			if(!$player->getInventory()->canAddItem($drop)) {
				$event->getPlayer()->addTitle("§l§a✠§6 FULL INVENTORY §a✠", "§l§cTự động bán!");
                $this->getServer()->getCommandMap()->dispatch($player,"sell all");

            }

				break; 
			}
		}
		}
	}
