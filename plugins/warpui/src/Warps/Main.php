<?php

namespace Warps;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use jojoe77777\FormAPI;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener{
	
	public function onEnable(){
		$this->getServer()->getLogger()->Info("§bWarpsGUI - Enabled!");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		$player = $sender->getPlayer();
		switch($cmd->getName()){
			case "warpui":
			$this->mainForm($player);
        }
        return true;
    }
	
	public function mainForm($player){
			$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
			$form = $api->createSimpleForm(function (Player $player, $data){
            $result = $data;
            if ($result == null) {
            }
            switch($result) {
              case 0:
                    $command = "sbui";
					$this->getServer()->getCommandMap()->dispatch($player, $command);
			     break;
              case 1:
                    $command = "warp pvp";
					$this->getServer()->getCommandMap()->dispatch($player, $command);
                break;
              case 2:
                    $command = "warp crate";
					$this->getServer()->getCommandMap()->dispatch($player, $command);
				break;
			  case 3:
                    $command = "warp mine";
					$this->getServer()->getCommandMap()->dispatch($player, $command);
				break;
			  case 4:
                    $command = "warp nhatu";
					$this->getServer()->getCommandMap()->dispatch($player, $command);
				break;
            }
			});
			$form->setTitle("§l§d♦§b Warps§6 System §d♦");
			//$form->setContent("§l§b♦§6 Hệ Thống §bWarps§6 Của Máy Chủ:");
			$form->addButton("§l§3● §fSkyBlock §3●");
            $form->addButton("§l§3● §fPvP §3●");			
            $form->addButton("§l§3● §fCrate §3●");
			$form->addButton("§l§3● §fMine §3●");
			$form->addButton("§l§3● §fNhà Tù§3●");
            //$form->addButton("§l§cGiveCup");			
			$form->sendToPlayer($player);
	}
}