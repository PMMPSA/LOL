<?php

namespace CLADevs\Minion;

use CLADevs\Minion\upgrades\HopperInventory;
use pocketmine\block\Block;
use pocketmine\block\Chest;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;
use SellHand\Main as SellHand;
use CLADevs\Minion\Main as Mini;
use pocketmine\nbt\tag\StringTag;

class Minion extends Human{

    public function initEntity(): void{
        parent::initEntity();
        $this->setHealth(1);
        $this->setMaxHealth(1);
        $this->setNameTagAlwaysVisible();
        $this->setNameTag("§l§6♦ §cĐệ Tử§6§6§6§6 ♦");
        $this->setScale(1);
        //$this->sendSpawnItems();
    }

    public function attack(EntityDamageEvent $source): void{
        $source->setCancelled();
		
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			$all = Mini::get()->user->getAll();
			unset($all[$this->getOwner()]);
			Mini::get()->user->setAll($all);
			Mini::get()->user->save();
			//$this->close();
		}
		
        if($source instanceof EntityDamageByEntityEvent){
            $damager = $source->getDamager();
            if($damager instanceof Player){
				if($damager->getName() !== $this->getOwner()){
					$damager->sendMessage("§l§6[§bSky§aBlock(§aʟ§cᴏ§aʟ)§6] §cNgươi không phải chủ của ta! Biến đi.");
					return;
				}
                $pos = new Position(intval($damager->getX()), intval($damager->getY()) + 2, intval($damager->getZ()), $damager->getLevel());
                $damager->addWindow(new HopperInventory($pos, $this));
            }
        }
    }

    public function entityBaseTick(int $tickDiff = 1): bool{
        $update = parent::entityBaseTick($tickDiff);
		if($this->getNameTag() !== "§l§6♦ §cĐệ Tử§6§6§6§6 ♦" or !$this->namedtag->hasTag("xyz", StringTag::class)){
			$this->flagForDespawn();
			return $update;

		}
        if($this->getLevel()->getServer()->getTick() % $this->getMineTime() == 0){
			$player = $this->getLevel()->getServer()->getPlayer($this->getOwner());
			
			//Checks if theres a chest behind him
            if($this->getLookingBehind() instanceof Chest){
                $b = $this->getLookingBehind();
                $this->namedtag->setString("xyz", $b->getX() . ":" . $b->getY() . ":" . $b->getZ());
            }
            //Update the coordinates

            if($this->namedtag->getString("xyz") !== "n"){
                if(isset($this->getCoord()[1])){
                    $block = $this->getLevel()->getBlock(new Vector3(intval($this->getCoord()[0]), intval($this->getCoord()[1]), intval($this->getCoord()[2])));
                    if(!$block instanceof Chest){
                        $this->namedtag->setString("xyz", "n");
                    }
                }
            }

            if ($this->getLookingBlock()->getId() !== Block::AIR  and $player !== NULL and $this->isChestLinked()){
				
                if($this->checkEverythingElse($player)){
                    $pk = new AnimatePacket();
                    $pk->entityRuntimeId = $this->id;
                    $pk->action = AnimatePacket::ACTION_SWING_ARM;
                    foreach (Server::getInstance()->getOnlinePlayers() as $p) $p->dataPacket($pk);
                    $this->breakBlock($this->getLookingBlock(), $player);
                }
            }
        }
        return $update;
    }

    public function sendSpawnItems(): void{
        $this->getInventory()->setItemInHand(Item::get(Item::DIAMOND_PICKAXE));
    }

    public function getLookingBlock(): Block{
        $block = Block::get(Block::AIR);
        switch($this->getDirection()){
            case 0:
                $block = $this->getLevel()->getBlock($this->add(1, 0, 0));
                break;
            case 1:
                $block = $this->getLevel()->getBlock($this->add(0, 0, 1));
                break;
            case 2:
                $block = $this->getLevel()->getBlock($this->add(-1, 0, 0));
                break;
            case 3:
                $block = $this->getLevel()->getBlock($this->add(0, 0, -1));
                break;
        }
        return $block;
    }

    public function getLookingBehind(): Block{
        $block = Block::get(Block::AIR);
        switch($this->getDirection()){
            case 0:
                $block = $this->getLevel()->getBlock($this->add(-1, 0, 0));
                break;
            case 1:
                $block = $this->getLevel()->getBlock($this->add(0, 0, -1));
                break;
            case 2:
                $block = $this->getLevel()->getBlock($this->add(1, 0, 0));
                break;
            case 3:
                $block = $this->getLevel()->getBlock($this->add(0, 0, 1));
                break;
        }
        return $block;
    }

    public function checkEverythingElse(Player $player): bool{
        $block = $this->getLevel()->getBlock(new Vector3(intval($this->getCoord()[0]), intval($this->getCoord()[1]), intval($this->getCoord()[2])));
        $tile = $this->getLevel()->getTile($block);
		$block2 = $this->getLookingBlock();
		if(!in_array($block2->getId(), [56, 14, 15, 16, 129, 133, 57, 42, 1, 41, 153, 73, 21, 152, 22, 173])){
			return false;
		}
        if($tile instanceof \pocketmine\tile\Chest){
            $inventory = $tile->getInventory();
			$it = $inventory->getItem(0);
			if(!in_array($it->getId(),array(278,285,257,274,270))){
				$this->getInventory()->setItemInHand(Item::get(0));
				$this->getInventory()->sendHeldItem($this->getViewers());
				return false;
			}
            if(!$inventory->canAddItem(Item::get($block->getId(), $block->getDamage()))){
				SellHand::getInstance()->sellAll2($inventory, $player, SellHand::getInstance());
			}
        }else{
			return false;
		}
		return true;
    }

    public function breakBlock(Block $block, $player): void{
		$block2 = $this->getLevel()->getBlock(new Vector3(intval($this->getCoord()[0]), intval($this->getCoord()[1]), intval($this->getCoord()[2])));
        $tile = $this->getLevel()->getTile($block2);
        if($tile instanceof \pocketmine\tile\Chest){
            $inv = $tile->getInventory();
			$it = $inv->getItem(0);
			if(in_array($it->getId(),array(278,285,257,274,270))){
				$icn = $it->getCustomName();
		        $pas = explode(" ", $icn);
		        if($pas[0] == "§l§6【"){
				   $player->sendMessage("§l§6[§bSky§aBlock(§aʟ§cᴏ§aʟ)§6] §cĐệ tử của bạn không thể dùng cúp level!");
					return;
				}
				$sl = count($block->getDropsForCompatibleTool($it));
				if($it->hasEnchantment(18)){
					$sl = $sl * (($it->getEnchantment(18)->getLevel() > 1000) ? 1000 : $it->getEnchantment(18)->getLevel());

				}
				$it->setDamage($it->getDamage() - 2);
				$inv->setItem(0,$it);
				$this->getInventory()->setItemInHand($it);
				$this->getInventory()->sendHeldItem($this->getViewers());
			}
			else{
				$this->getInventory()->setItemInHand(Item::get(0));
				$this->getInventory()->sendHeldItem($this->getViewers());
			}
            if(!$inv->canAddItem(Item::get($block->getId(), $block->getDamage(), $sl))){
				SellHand::getInstance()->sellAll2($inv, $player, SellHand::getInstance());
			}
            $inv->addItem(Item::get($block->getId(), $block->getDamage(), $sl));
        }
        $this->getLevel()->setBlock($block, Block::get(Block::AIR), true, true);
    }

    public function getMineTime(): int{
        return 20 * $this->namedtag->getInt("Time");
    }

    public function flagForDespawn(): void{
        parent::flagForDespawn();
        foreach($this->getDrops() as $drop){
			$nbt = $drop->getNamedTag();
			$nbt->setInt("Time", $this->getTime());
			$nbt->setString("Owner", $this->getOwner());
			$nbt->setString("xyz", 'n');
			$drop->setNamedTag($nbt);
			$p = $this->getLevel()->getServer()->getPlayer($this->getOwner());
			$all = Mini::get()->user->getAll();
			unset($all[$this->getOwner()]);
			Mini::get()->user->setAll($all);
			Mini::get()->user->save();
			if($p !== NULL){
				$inv = $p->getInventory();
				if($inv->canAddItem($drop)){
					$inv->addItem($drop);
					return;
				}			
			}
        }
    }

    public function getCost(): int{
        switch($this->getTime()){
			case 3:
				return 1000; 
			break;
			
			default:
			case 2:
				return 2000; 
			break;
		}
    }

    public function getTime(): int{
        return $this->namedtag->getInt("Time");
    }
	
	public function getOwner(): string{
		return $this->namedtag->getString("Owner");
	}

    public function getDrops(): array{
        return [Main::get()->getItem($this->getOwner())];
    }
	
    public function isChestLinked(): bool{
        return $this->namedtag->getString("xyz") === "n" ? false : true;
    }

    public function getChestCoordinates(): string{
        if(!isset($this->getCoord()[1])){
            return C::RED . "§l§cKhông rõ";
        }
        $coord = C::YELLOW . "X: " . C::WHITE . $this->getCoord()[0] . " ";
        $coord .= C::YELLOW . "Y: " . C::WHITE . $this->getCoord()[1] . " ";
        $coord .= C::YELLOW . "Z: " . C::WHITE . $this->getCoord()[2] . " ";
        return $coord;
    }

    public function getCoord(): array{
        $coord = explode(":", $this->namedtag->getString("xyz"));
        return $coord;
    }
}
