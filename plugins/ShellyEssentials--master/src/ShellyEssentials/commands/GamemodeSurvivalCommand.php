<?php

declare(strict_types=1);

namespace ShellyEssentials\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ShellyEssentials\API;
use ShellyEssentials\Main;

class GamemodeSurvivalCommand extends BaseCommand{

	public function __construct(Main $main){
		parent::__construct($main, "gms", "Gamemode survival command", "/gms <player>", ["gms"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$sender instanceof Player){
			$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::RED . "Use this command in-game");
			return false;
		}
		if(!$sender->hasPermission("gms.command")){
			$sender->sendMessage(self::NO_PERMISSION);
			return false;
		}
		if(empty($args[0])){
			$sender->setGamemode(Player::SURVIVAL);
			$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::GREEN . "You have set your gamemode to survival");
			return false;
		}
		if(API::getMainInstance()->getServer()->getPlayer($args[0])){
			$player = API::getMainInstance()->getServer()->getPlayer($args[0]);
			$player->setGamemode(Player::SURVIVAL);
			$player->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::GREEN . "Your gamemode has been set to survival");
			$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::GREEN . "You have set " . $player->getName() . "'s gamemode to survival");
		}
		return true;
	}
}