<?php

namespace AmGM;

use pocketmine\utils\TextFormat as __;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\level\sound\AnvilUseSound;
class QT1 extends PluginBase {

	public $eco;
	
	public function onEnable(){
		$this->eco = EconomyAPI::getInstance();
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) :bool {
		
		if($cmd->getName() == "veso") {
			if($sender instanceof Player){
				$rand = mt_rand(1, 200);
				$sender->getLevel()->addSound(new AnvilUseSound($sender));
				if($this->eco->reduceMoney($sender->getName(), 10000)){
					switch($rand){
						case 200:
							$this->eco->addMoney($sender->getName(), 200000);
							$this->getServer()->broadcastMessage("§l§c⬛§c⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛§c⬛\n\n§aHôm nay:\n§c". $sender->getName() ."§b đã nhận§6 200.000 Xu\n\n§a✔§d Giao dịch hoàn tất\n\n§a•§e Liên hệ tổng đài §b/veso\n\n§l§c⬛§c⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛§c⬛");
						break;
						
						case 100:
							$this->eco->addMoney($sender->getName(), 600000);
							$this->getServer()->broadcastMessage("§l§c⬛§c⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛§c⬛\n\n§aHôm nay:\n§c". $sender->getName() ."§b đã nhận§6 600.000 Xu\n\n§a✔§d Giao dịch hoàn tất\n\n§a•§e Liên hệ tổng đài §b/veso\n\n§l§c⬛§c⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛§c⬛");
						break;

						default:
							$sender->sendMessage("§a●§e Bạn đã không trúng giải.");
						break;
					}
				}else{
					$sender->sendMessage("§c●§a Bạn cần §e10k xu§a để mua vé số.");
					return true;
				}
			}
		}
		return true;
	}
}