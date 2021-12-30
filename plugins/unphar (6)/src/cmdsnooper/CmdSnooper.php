<?php
namespace cmdsnooper;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
class CmdSnooper extends PluginBase {
	public $snoopers = [];
	
	public function onEnable() {
		@mkdir($this->getDataFolder());
		$this->getLogger()->info("Enabled! Ready to snoop >:D");
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML, array(
	  	"Console.Logger" => "true",
  		));
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{				
		if(strtolower($cmd->getName()) == "theodoi") {
		 	if($sender instanceof Player) {
				if($sender->hasPermission("theodoi.command")) {
					if(!isset($this->snoopers[$sender->getName()])) {
						$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã bật chế độ theo dõi!");
						$this->snoopers[$sender->getName()] = $sender;
						return true;
					} else {
						$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã tắt chế độ theo dõi!");
						unset($this->snoopers[$sender->getName()]);
						return true;
					}
				}
			}
		}
		//$this->getLogger()->info("Command '/snoop' must be run as a player");
		return false;
	}
 }