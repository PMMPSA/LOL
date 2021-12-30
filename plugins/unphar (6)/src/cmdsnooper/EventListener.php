<?php

namespace cmdsnooper;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use cmdsnooper\CmdSnooper;

class EventListener implements Listener {
	public $plugin;
	
	public function __construct(CmdSnooper $plugin) {
		$this->plugin = $plugin;
	}

	public function getPlugin() {
		return $this->plugin;
	}
	
	public function onPlayerCmd(PlayerCommandPreprocessEvent $event) {
		$sender = $event->getPlayer();
		$msg = $event->getMessage();
		
		if($this->getPlugin()->cfg->get("Console.Logger") == "true") {
			if($msg[0] == "/") {
					if(stripos($msg, "napthe") || stripos($msg, "givecp")) {
					$this->getPlugin()->getLogger()->info("§l§6[§bSky§aBlock(Bin)§6] " . $sender->getName() . "§c Bị ẩn do quyền riêng tư!");	
				} else {
					$this->getPlugin()->getLogger()->info("§l§c●§b " . $sender->getName() . " §adùng§e " . $msg);
				}
				
			}
		}
			
			if(!empty($this->getPlugin()->snoopers)) {
				foreach($this->getPlugin()->snoopers as $snooper) {
					 if($msg[0] == "/") {
					if(stripos($msg, "napthe") || stripos($msg, "givecp")) {
							$snooper->sendMessage("§l§6[§bSky§aBlock(Bin)§6] " . $sender->getName() . "§c Bị ẩn do quyền riêng tư!");			
						} else {
							$snooper->sendMessage("§l§c●§b " . $sender->getName() . " §adùng§e " . $msg);
						}
						
					}
	     			}		
     			}
   		}
	}
