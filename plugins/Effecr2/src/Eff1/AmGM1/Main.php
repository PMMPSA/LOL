<?php

namespace Eff1\AmGM1;

use pocketmine\utils\TextFormat as __;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\EffectInstance;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\entity\Effect;

class Main extends PluginBase implements Listener{
	
	public function onEnable(){
		$this->getLogger()->info("§aPlugin §dKitEffect §eđã được bật");//lời nhắn khi bật plugin
	}
		public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
			if($cmd->getName() == "kiteffect"){
			$sender->sendMessage("§l§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬§c【 §eKit §dEffect §c】§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬");
			$sender->sendMessage("§l§c•§e Để sử dụng §aKit Effect§c dành cho VIP §ehãy dùng");
			$sender->sendMessage("§l§c•§b /kiteffect [tên loại] [thời gian]\n§l§c•§b Các loại kit hiện có: §6[§adaonhanh§6] §6[§asucmanh§6] §6[§atocdo§6] §6[§ahoimau§6] §6[§anhaycao§6]\n§l§c-----------------------------------------------------");
			if(!isset($args[1]) || !is_numeric($args[1]) || $args[1] > 100000){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §cSố thời gian cần phải là số và nhỏ hơn 100000!");
			    return false;
			}
			if(isset($args[0])){
			switch(strtolower($args[0])){
			case "sucmanh":
			$sm = new EffectInstance(Effect::getEffect(Effect::STRENGTH), $args[1] * 20, 9, false);
			$sender->addEffect($sm);
			$sender->sendMessage("§l§c•§a Bạn đã lấy §bKit:§c $args[0]");
			break;
			return true;
			case "tocdo":
			$sp = new EffectInstance(Effect::getEffect(Effect::SPEED), $args[1] * 20, 4, false);
			$sender->addEffect($sp);
			$sender->sendMessage("§l§c•§a Bạn đã lấy §bKit:§c $args[0]");
			break;
			return true;
			case "daonhanh":
			$hm = new EffectInstance(Effect::getEffect(Effect::HASTE), $args[1] * 20, 9, false);
			$sender->addEffect($hm);
			$sender->sendMessage("§l§c•§a Bạn đã lấy §bKit:§c $args[0]");
			break;
			return true;
			case "hoimau":
			$hm = new EffectInstance(Effect::getEffect(Effect::REGENERATION), $args[1] * 20, 9, false);
			$sender->addEffect($hm);
			$sender->sendMessage("§l§c•§a Bạn đã lấy §bKit:§c $args[0]");
			break;
			return true;
			case "nhaycao":
			$ju = new EffectInstance(Effect::getEffect(Effect::JUMP), $args[1] * 20, 4, false);
			$sender->addEffect($ju);
			$sender->sendMessage("§l§c•§a Bạn đã lấy §bKit:§c $args[0]");
			}
		}
	}
	return true;
}
}