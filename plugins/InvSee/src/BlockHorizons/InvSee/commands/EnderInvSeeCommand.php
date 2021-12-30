<?php
namespace BlockHorizons\InvSee\commands;

use BlockHorizons\InvSee\InventoryHandler;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\Player;

class EnderInvSeeCommand extends BaseCommand {

	protected function initCommand(): void {
		$this->setFlag(self::FLAG_DENY_CONSOLE);
	}

	public function onCommand(CommandSender $sender, string $commandLabel, array $args): bool {
		if(!isset($args[0])) {
			return false;
		}

        $p = isset($args[0]) ? $sender->getServer()->getPlayer($args[0]) : $sender;
		
		if(!$p instanceof Player) //check if the target is a Player / Online
        {
            $sender->sendMessage(TextFormat::RED . "§l§6[§bSky§aBlock(Bin)§6]§e Người chơi đang không trực tuyến.");
			return true;
        }
						
		if($p->hasPermission("no.ender.inv")){
		   $sender->sendMessage(TextFormat::RED . "§l§6[§bSky§aBlock(Bin)§6]§e Bạn không thể xem rương ender của người này.");
		   return true;
		}

		if(!$this->getLoader()->getInventoryHandler()->send($sender, $args[0], InventoryHandler::TYPE_ENDER_INVENTORY)) {
			$sender->sendMessage(TextFormat::RED . "§l§6[§bSky§aBlock(Bin)§6]§e Bạn không thể xem rương ender của người này.");
			return true;
		}
		return true;
	}
}