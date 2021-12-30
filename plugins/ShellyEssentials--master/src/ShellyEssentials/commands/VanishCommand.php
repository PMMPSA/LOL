<?php

declare(strict_types=1);

namespace ShellyEssentials\commands;

use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ShellyEssentials\API;
use ShellyEssentials\Main;

class VanishCommand extends BaseCommand{

	/** @var array $vanish */
	private $vanish = [];

	public function __construct(Main $main){
		parent::__construct($main, "vanish", "Vanish yourself", "/vanish <player>", ["vanish"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$sender instanceof Player){
			$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::RED . "Use this command in-game");
			return false;
		}
		if(!$sender->hasPermission("vanish.command")){
			$sender->sendMessage(self::NO_PERMISSION);
			return false;
		}
		if(empty($args[0])){
			if(!in_array($sender->getName(), $this->vanish)){
				$this->vanish[] = $sender->getName();
				$sender->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
				$sender->setNameTagVisible(false);
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::GREEN . "You have been vanished");
			}elseif(in_array($sender->getName(), $this->vanish)){
				unset($this->vanish[array_search($sender->getName(), $this->vanish)]);
				$sender->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, false);
				$sender->setNameTagVisible(true);
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] "   . TextFormat::RED . "You have been unvanished");
			}
			return false;
		}
    return false;
	}
}