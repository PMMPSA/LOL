<?php

namespace CLADevs\Minion\upgrades;

use CLADevs\Minion\upgrades\HopperInventory;
use CLADevs\Minion\Minion;
use pocketmine\entity\Entity;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\block\Chest;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat as C;
use doramine\economyapi\EconomyAPI;
use CLADevs\Minion\Main as Mini;
use pocketmine\Server;

class EventListener implements Listener{

    public $linkable = [];

    public function onInv(InventoryTransactionEvent $e): void{
        $tr = $e->getTransaction();
        foreach($tr->getActions() as $act){
            if($act instanceof SlotChangeAction){
                $inv = $act->getInventory();
                if($inv instanceof HopperInventory){
					$player = $tr->getSource();
                    $entity = $inv->getEntity();
                    $e->setCancelled();
					switch($act->getSourceItem()->getId()){
					case Item::REDSTONE_DUST:
						if(isset($this->linkable[$player->getName()])) unset($this->linkable[$player->getName()]);
                        $entity->flagForDespawn();
						$inv->onClose($player);
                        break;
						
                        case Item::CHEST:
                            if($entity->getLookingBehind() instanceof Chest){
                                $player->sendMessage(C::RED . "§l§6[§bSky§aBlock(Bin)§6]§e Vui lòng loại bỏ rương phía sau người khai thác, để đặt rương liên kết mới.");
                                return;
                            }
                            if(isset($this->linkable[$player->getName()])){
                                $player->sendMessage(C::RED . "§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã ở chế độ liên kết.");
                                return;
                            }
                            $this->linkable[$player->getName()] = $entity;
                            $player->sendMessage(C::LIGHT_PURPLE . "§l§6[§bSky§aBlock(Bin)§6]§e Vui lòng chạm vào rương mà bạn muốn liên kết với.");
							$inv->onClose($player);
                            break;
					    case Item::DIAMOND:
                            $time = $time = $entity->namedtag->getInt("Time");
                            if($time <= 1){
                               $tr->getSource()->sendMessage(C::RED . "§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã nâng đến cấp tối đa!");
                               return;
                            }
						    if($entity->getOwner() !==  $tr->getSource()->getName()){
							   $tr->getSource()->sendMessage(C::RED . "§l§6[§bSky§aBlock(Bin)§6]§e Bạn không phải chủ của đệ tử");
							   return;
						    }
						    if(EconomyAPI::getInstance()->myMoney($player) < $entity->getCost()){
                               $player->sendMessage(C::RED . "§l§6[§bSky§aBlock(Bin)§6]§e Bạn không đủ §aPoint§e để nâng cấp");
                               return;
                            }
                            EconomyAPI::getInstance()->reduceMoney($player, $entity->getCost());
						
                            $time = $entity->namedtag->getInt("Time") - 1;
                            $entity->namedtag->setInt("Time", $time);
                            $tr->getSource()->sendMessage(C::YELLOW . "§l§6[§bSky§aBlock(Bin)§6]§e Nâng cấp tốc độ để tử xuống§d " . $time . "s!");
                            $inv->setItem(2, $inv->getSpeedUp());
							$inv->onClose($player);
                            break;
                    }
				}
			}
		}
	}

    public function onInteract(PlayerInteractEvent $e): void{
		$player = $e->getPlayer();
        $item = $e->getItem();
        $dnbt = $item->getNamedTag();
        //$player = $e->getPlayer();
        //$hand = $player->getInventory()->getItemInHand();
        $level = $e->getBlock()->getLevel()->getFolderName();
        if ($level === 'sb') {
			if(!Mini::get()->user->exists($player->getName())){
				if($dnbt->hasTag("TrieuHoiMF", StringTag::class)){
					if($dnbt->getString('Owner') !== $player->getName()){
						$player->sendMessage('§l§6[§bSky§aBlock(Bin)§6]§e Bạn không phải chủ của đệ tử!');
						return;
					}
					$nbt = Entity::createBaseNBT($player, null, (90 + ($player->getDirection() * 90)) % 360);
					$nbt->setInt("Time", $dnbt->getInt('Time'));
					$nbt->setString("Owner",  $dnbt->getString('Owner'));
					$nbt->setString("xyz", $dnbt->getString("xyz"));
					$nbt->setTag($player->namedtag->getTag("Skin"));
					$entity = new Minion($player->getLevel(), $nbt);
					$entity->getInventory()->setItemInHand(Item::get(0));
					$entity->spawnToAll();
					$entity->getInventory()->sendHeldItem($entity->getViewers());
					Mini::get()->user->set($player->getName(), true);
					Mini::get()->user->save();
					$player->getInventory()->setItemInHand(Item::get(0));
				}
			}
            
			if(isset($this->linkable[$player->getName()])){
				if(!$e->getBlock() instanceof Chest){
					$player->sendMessage(C::RED . "§l§6[§bSky§aBlock(Bin)§6]§e Vui lòng gõ một cái rương không phải là một§c " . $e->getBlock()->getName());
					return;
				}
				$entity = $this->linkable[$player->getName()];
				$block = $e->getBlock();    			
				if($entity instanceof Minion) $entity->namedtag->setString("xyz", $block->getX() . ":" . $block->getY() . ":" . $block->getZ());
				unset($this->linkable[$player->getName()]);
				$player->sendMessage(C::GREEN . "§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã liên kết một rương!");
				return;
			}
		}
	}
	
    public function onEntitySpawn(EntitySpawnEvent $e): void{
        $entity = $e->getEntity();

        if($entity instanceof Minion){
            $pl = Server::getInstance()->getPluginManager()->getPlugin("ClearLagg");
            if($pl !== null) $pl->exemptEntity($entity);
        }
    }	
}