<?php

namespace OreGenerator;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\block\Iron;
use pocketmine\block\Cobblestone;
use pocketmine\block\Glowingobsidian;
use pocketmine\block\Diamond;
use pocketmine\block\Emerald;
use pocketmine\block\Gold;
use pocketmine\block\Coal;
use pocketmine\block\Lapis;
use pocketmine\block\Redstone;
use pocketmine\block\IronOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\EmeraldOre;
use pocketmine\block\GoldOre;
use pocketmine\block\CoalOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\Config;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent};

class Generate extends PluginBase implements Listener{

    public function onEnable(){
        $this->getLogger()->info("Plugin OreGenerator by vividmemory!");
		$this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
         $this->block = new Config($this->getDataFolder()."Block.yml",Config::YAML);
    }
    
    public function onJoin(PlayerJoinEvent $ev) {
             if(!$this->block->exists($ev->getPlayer()->getName())) {
            $this->block->set($ev->getPlayer()->getName(), 0);
            $this->block->save();
             }
        }
        
    public function onQuit(PlayerQuitEvent $ev) {
           $this->block->save();
    }
    
    public function onBlockSet(BlockUpdateEvent $event){
        $block = $event->getBlock();
        $end = false;
        for ($i = 0; $i <= 1; $i++) {
            $nearBlock = $block->getSide($i);
            if ($nearBlock instanceof glowingobsidian) {
                $end = true;
            }
            if ($end) {
                $id = mt_rand(1, 15);
                switch ($id) {
                    case 8;
                        $newBlock = new IronOre();
                        break;
                    case 8;
                        $newBlock = new GoldOre();
                        break;
                    case 6;
                        $newBlock = new EmeraldOre();
                        break;
                    case 8;
                        $newBlock = new CoalOre();
                        break;
                    case 10;
                        $newBlock = new RedstoneOre();
                        break;
                    case 9;
                        $newBlock = new DiamondOre();
                        break;
                    case 7;
                        $newBlock = new Iron();
                        break;
                    case 9;
                        $newBlock = new Gold();
                        break;
                    case 6;
                        $newBlock = new Emerald();
                        break;
                    case 11;
                        $newBlock = new Coal();
                        break;
                    case 15;
                        $newBlock = new Redstone();
                        break;
                    case 10;
                        $newBlock = new Diamond();
                        break;
                    default:
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                        $newBlock = new DiamondOre();
                }
                $block->getLevel()->setBlock($block, $newBlock, true, false);
                return;
            }
        }
    }
 
	public function onCommand(CommandSender $sender, Command $command, String $label, array $args) : bool {
        switch($command->getName()){
            case "buyrandom":
            $this->menu($sender);
            return true;
        }
        return true;
	}
	
	 public function menu($sender){
        $formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createSimpleForm(function(Player $sender, $data){
          $result = $data;
          if($result === null){
          }
          switch($result){
              case 0:
              break;
              case 1:
        $block = $this->block->get($sender->getPlayer()->getName());
        if($block > 0){
            $money = $this->eco->MyMoney($sender);
            if($money >= 10000){
               $this->eco->reduceMoney($sender, 10000);	
			$inv = $sender->getInventory();  
			$item = Item::get(246, 0, 1);
			$item->setCustomName("§l§6● §aKhối Tạo Ore §6●");
			$inv->addItem($item);
			$sender->sendMessage("§l§c•§b Bạn Đã Nhận Block Random");
            $this->block->set($sender->getPlayer()->getName(), ($this->block->get($sender->getPlayer()->getName()) + 1));
            $this->block->save();
            }
        }else{
			$inv = $sender->getInventory();  
			$item = Item::get(246, 0, 1);
			$item->setCustomName("§l§6● §aKhối Tạo Ore §6●");
			$inv->addItem($item);
			$sender->sendMessage("§l§c•§b Bạn Đã Nhận Block Random");
            $this->block->set($sender->getPlayer()->getName(), ($this->block->get($sender->getPlayer()->getName()) + 1));
            $this->block->save();
        }
              break;
         }
        });
        $block = $this->block->get($sender->getPlayer()->getName());
        if($block > 0){
            $msg = "Mua Một Blockrandom với giá 10k xu";
        }else{
            $msg = "miễn Phí Nhận Block Đầu Tiên";
        }
        $form->setTitle("§l§6● §aKhối Tạo Ore §6●");
        $form->setContent("§e§l".$msg);
		$form->addButton("§7》§c§lThoát§r§7《");
        $form->addButton("§6★§c§lMua Block Random§l§6★");
        $form->sendToPlayer($sender);
	}
    
}
