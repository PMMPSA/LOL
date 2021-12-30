<?php

/* -----[MuaZCoinUI]-----
* Update Screen UI System.
* Version: 2.0
* Editor: BlackPMFury
* This Test Plugin.
*/

namespace ChoCanhUI\ChoCanh;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{Command, CommandSender, CommandExecutor, ConsoleCommandSender};
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\{Player, Server};
use jojoe7777\FormAPI;

class Main extends PluginBase{
	
	public function onEnable(){
		$this->getServer()->getLogger()->info(" §l§aEnable MuaDanhHieu System....");
		$this->point = $this->getServer()->getPluginManager()->getPlugin("PointAPI");
	}
	
	public function onLoad(): void{
		$this->getServer()->getLogger()->notice("Loading Data.....");
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		switch($cmd->getName()){
			case "muadanhhieu":
			if(!($sender instanceof Player)){
				$this->getLogger()->notice("Please Dont Use that command in here.");
				return true;
			}
			$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
			$form = $api->createSimpleForm(Function (Player $sender, $data){
				
				$result = $data;
				if ($result == null){
				}
				switch ($result) {
					case 0:
					$sender->sendMessage("§c");
					break;
					case 1:
					$this->danhhieu0($sender);
					break;
					case 2:
					$this->danhhieu1($sender);
					break;
					case 3:
					$this->danhhieu2($sender);
					break;
					case 4:
					$this->danhhieu3($sender);
					break;
					case 5:
					$this->danhhieu4($sender);
					break;
					case 6:
					$this->danhhieu5($sender);
					break;
					case 7:
					$this->danhhieu6($sender);
					break;
					case 8:
					$this->danhhieu7($sender);
					break;
					case 9:
					$this->danhhieu8($sender);
					break;
					case 10:
					$this->danhhieu9($sender);
					break;
				}
			});
			
			$form->setTitle("§l§6♦ §dMua Danh Hiệu §l§6♦");
			$form->addButton("§l§3● §cThoát §3●");	
			$form->addButton("§l§3● §bмắт§aʙιếc §3●\n§d【§660 Point§d】§r");	
			$form->addButton("§l§3● §cＦＡ §3●\n§d【§660 Point§d】§r");
			$form->addButton("§l§3● §cYOᑌ§dTᑌ§bᗷE §3●\n§d【§6100 Point§d】§r");	
			$form->addButton("§l§3● §dʟovᴇʀ §3●\n§d【§660 Point§d】§r");
			$form->addButton("§l§3● §bʙo§dss §3●\n§d【§6100 Point§d】§r");
			$form->addButton("§l§3● §aʙà§bтâɴ§cvʟoԍ §3●\n§d【§6100 Point§d】§r");	
			$form->addButton("§l§3● §bмιɴᴇ§aғoʀᴇ §3●\n§d【§6200 Point§d】§r");
			$form->addButton("§l§3● §dɴнạт §3●\n§d【§6100 Point§d】§r");
			$form->addButton("§l§3● §cтʀùм§dsᴇʀvᴇʀ §3●\n§d【§6500 Point§d】§r");
			$form->addButton("§l§3● §6тʀầɴ§aтιԍᴇʀ §3●\n§d【§6500 Point§d】§r");
			$form->sendToPlayer($sender);
			break;
		}
		return true;
	}
	
	public function danhhieu0($sender){
			if($sender->hasPermission('danhhieu.0')){
			   $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§c Bạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->point->myMoney($sender) >= 60){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §bмắт§aʙιếc");
				$this->point->reduceMoney($sender, 60);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.0");
			}else{
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn cần §c60 point§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu1($sender){
			if($sender->hasPermission('danhhieu.1')){
			   $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§c Bạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->point->myMoney($sender) >= 60){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §cＦＡ");
				$this->point->reduceMoney($sender, 60);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.1");
			}else{
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn cần §c60 point§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu2($sender){
			if($sender->hasPermission('danhhieu.2')){
			   $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§c Bạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->point->myMoney($sender) >= 100){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §cYOᑌ§dTᑌ§bᗷE");
				$this->point->reduceMoney($sender, 100);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.2");
			}else{
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn cần §c100 point§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu3($sender){
			if($sender->hasPermission('danhhieu.3')){
			   $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§c Bạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->point->myMoney($sender) >= 60){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §dʟovᴇʀ");
				$this->point->reduceMoney($sender, 60);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.3");
			}else{
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn cần §c60 point§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu4($sender){
			if($sender->hasPermission('danhhieu.4')){
			   $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§c Bạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->point->myMoney($sender) >= 100){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §bʙo§dss");
				$this->point->reduceMoney($sender, 100);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.4");
			}else{
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn cần §c100 point§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu5($sender){
			if($sender->hasPermission('danhhieu.5')){
			   $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§c Bạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->point->myMoney($sender) >= 100){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §aʙà§bтâɴ§cvʟoԍ");
				$this->point->reduceMoney($sender, 100);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.5");
			}else{
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn cần §c100 point§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu6($sender){
			if($sender->hasPermission('danhhieu.6')){
			   $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§c Bạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->point->myMoney($sender) >= 200){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §bмιɴᴇ§aғoʀᴇ");
				$this->point->reduceMoney($sender, 200);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.6");
			}else{
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn cần §c200 point§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu7($sender){
			if($sender->hasPermission('danhhieu.7')){
			   $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§c Bạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->point->myMoney($sender) >= 100){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §dɴнạт");
				$this->point->reduceMoney($sender, 100);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.7");
			}else{
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn cần §c100 point§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu8($sender){
			if($sender->hasPermission('danhhieu.8')){
			   $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§c Bạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->point->myMoney($sender) >= 500){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §cтʀùм§dsᴇʀvᴇʀ");
				$this->point->reduceMoney($sender, 500);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.8");
			}else{
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn cần §c500 point§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu9($sender){
			if($sender->hasPermission('danhhieu.9')){
			   $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§c Bạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->point->myMoney($sender) >= 500){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §l§6тʀầɴ§aтιԍᴇʀ");
				$this->point->reduceMoney($sender, 500);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.9");
			}else{
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn cần §c500 point§e để mua danh hiệu này!");
				return true;
			}
	}
}