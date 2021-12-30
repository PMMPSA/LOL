<?php

namespace SkyBlockUI;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use jojoe77777\FormAPI;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener{
	
	public function onEnable(){
		$this->getServer()->getLogger()->Info("§bSkyBlockGUI - Enabled!");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		$player = $sender->getPlayer();
		switch($cmd->getName()){
			case "sbui":
			$this->mainForm($player);
        }
        return true;
    }
	
	public function mainForm($player){
		if($player instanceof Player){
			$form = $this->formapi->createSimpleForm(function (Player $player, $data){
				$result = $data;
				if ($result == null) {
				}
				switch($result) {
                            case 0:
							break;	
							
							case 1:
								$command = "sb auto";
								$this->getServer()->dispatchCommand($player, $command);
							break;
							
							case 2:
								$command = "sb claim";
								$this->getServer()->dispatchCommand($player, $command);
							break;
				  
							case 3:
								$this->addForm($player);
							break;
								
							case 4:
								$this->removeForm($player);
							break;
								
							case 5:
								$this->homeForm($player);
							break;
								
							case 6:
								$this->warpForm($player);
							break;
								
							case 7:
								$this->giveForm($player);
							break;
				}
			});
			$form->setTitle("§l§6♦§d Sky§3Block §6♦");
			$form->addButton("§l§3● §cThoát §3●");
			$form->addButton("§l§3● §dTìm Đảo §3●");
            $form->addButton("§l§3● §dLấy Đảo §3●");			
            $form->addButton("§l§3● §dThêm người chơi vào đảo §3●");	
			$form->addButton("§l§3● §dXóa người chơi khỏi đảo §3●");
			$form->addButton("§l§3● §dVề đảo của bạn §3●");
			$form->addButton("§l§3● §dDịch chuyển đến đảo khác §3●");
			$form->addButton("§l§3● §dCho người chơi khác đảo §3●");
			$form->sendToPlayer($player);
		}
	}
	
	public function addForm($player){
		if($player instanceof Player){
			
			$form = $this->formapi->createCustomForm(function (Player $player, $data){

				$result = $data[0];
				if($result != null){
					$this->Ten = $data[0];
					$this->getServer()->dispatchCommand($player, "sb addhelper " . $this->Ten);
				}
			});
			$form->setTitle("§l§bThêm Người Vào Đảo Của Bạn");
			$form->addInput("§l§eNhập Tên Người Chơi Muốn Thêm");
			$form->sendToPlayer($player);
		}
	}
	
	public function removeForm($player){
		if($player instanceof Player){
			$form = $this->formapi->createCustomForm(function (Player $player, $data){
				$result = $data[0];
				if($result != null){
					$this->Ten = $data[0];
					$this->getServer()->dispatchCommand($player, "sb removehelper " . $this->Ten);
				}
			});
			$form->setTitle("§l§bXóa Người Ra Khỏi Đảo Của Bạn");
			$form->addInput("§l§eNhập Tên Người Chơi Muốn Xóa");
			$form->sendToPlayer($player);
		}
	}

	public function homeForm($player){
		if($player instanceof Player){
			$form = $this->formapi->createCustomForm(function (Player $player, $data){

				$result = $data[0];
				if($result != null){
					$this->Home = $data[0];
					$this->getServer()->dispatchCommand($player, "sb home " . $this->Home);
				}
			});
			$form->setTitle("§l§bVề Đảo Của Bạn");
			$form->addInput("§l§eNhập Số Đảo Bạn Muốn về ( ví dụ 1 , 2 ,3 ) ");
			$form->sendToPlayer($player);
		}
	}
		
	public function warpForm($player){
		if($player instanceof Player){
				$form = $this->formapi->createCustomForm(function (Player $player, $data){

				$result = $data[0];
				if($result != null){
					$this->idDao = $data[0];
					$this->getServer()->dispatchCommand($player, "sb warp " . $this->idDao);
				}
			});
			$form->setTitle("§l§bDịch Chuyển Đến Hòn Đảo Nào Đó");
			$form->addInput("§l§eNhập Theo Địa Chỉ X;Y");
			$form->sendToPlayer($player);
		}
	}

	public function giveForm($player){
		if($player instanceof Player){
				$form = $this->formapi->createCustomForm(function (Player $player, $data){

				$result = $data[0];
				if($result != null){
					$this->Ten = $data[0];
					$this->getServer()->dispatchCommand($player, "sb give " . $this->Ten);
				}
			});
			$form->setTitle("§l§bChuyển Quyền Sở Hữu Đảo");
			$form->addInput("§l§eNhập Tên Người Muốn Chuyển");
			$form->sendToPlayer($player);
		}
	}
}
