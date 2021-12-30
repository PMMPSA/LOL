<?php

declare(strict_types=1);

namespace ShellyEssentials\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ShellyEssentials\API;
use ShellyEssentials\Main;

class FlyCommand extends BaseCommand{

	public function __construct(Main $main){
		parent::__construct($main, "fly", "Allow yourself to fly", "/fly <player>", ["fly"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$sender instanceof Player){
			$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::RED . "Use this command in-game");
			return false;
		}
		if(!$sender->hasPermission("fly.command")){
			$sender->sendMessage(self::NO_PERMISSION);
			return false;
		}
		if(empty($args[0])){
			if(!$sender->isCreative()){
				$sender->sendMessage($sender->getAllowFlight() === false ? "§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::GREEN . "You have activated flight" : "§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::RED . "You have disabled flight");
				$sender->setAllowFlight($sender->getAllowFlight() === false ? true : false);
			}else{
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "  . TextFormat::RED . "You can only use this command in survival mode");
				return false;
			}
		}
	return false;
	}
}