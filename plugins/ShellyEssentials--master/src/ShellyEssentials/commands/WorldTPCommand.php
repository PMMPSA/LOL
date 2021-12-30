<?php

declare(strict_types=1);

namespace ShellyEssentials\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ShellyEssentials\API;
use ShellyEssentials\Main;

class WorldTPCommand extends BaseCommand{

	public function __construct(Main $main){
		parent::__construct($main, "worldtp", "Teleport to a specific world", "/worldtp <world>", ["worldtp"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$sender instanceof Player){
			$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::RED . "Use this command in-game");
			return false;
		}
		if(!$sender->hasPermission("worldtp.command")){
			$sender->sendMessage(self::NO_PERMISSION);
			return false;
		}
		if(empty($args[0])){
			$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::GRAY . "Usage: /worldtp <world>");
			return false;
		}
		if(file_exists(API::getMainInstance()->getServer()->getDataPath() . "worlds/" . $args[0])){
			$sender->teleport(API::getMainInstance()->getServer()->getLevelByName($args[0])->getSafeSpawn());
			$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::GREEN . "You have been teleported to the world named $args[0]");
		}else{
			$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::RED . "World does not exist");
			return false;
		}
		return true;
	}
}