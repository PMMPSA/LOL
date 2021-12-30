<?php

namespace CLADevs\Minion\upgrades;

use CLADevs\Minion\Minion;
use pocketmine\block\Block;
use pocketmine\inventory\CustomInventory;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;

class HopperInventory extends CustomInventory{

    protected $holder;
    protected $entity;

    public function __construct(Position $position, Minion $entity){
        parent::__construct($position);
        $this->entity = $entity;
        $this->setItem(0, $this->getDestoryItem());
		$this->setItem(2, $this->getChestItem());
        $this->setItem(4, $this->getSpeedUp());
    }

    public function getName(): string{
        return "Hopper";
    }

    public function getDefaultSize(): int{
        return 5;
    }

    public function getNetworkType(): int{
        return WindowTypes::HOPPER;
    }

    public function onOpen(Player $who): void{
        $block = Block::get(Block::HOPPER_BLOCK);
        $block->x = $this->getHolder()->getX();
        $block->y = $this->getHolder()->getY();
        $block->z = $this->getHolder()->getZ();
        $block->level = $this->getHolder()->getLevel();
        $who->getLevel()->sendBlocks([$who], [$block]);
        $w = new NetworkLittleEndianNBTStream;
        $nbt = new CompoundTag("", []);
        $nbt->setString("id", "Hopper");
        $nbt->setString("CustomName", "§l§6♦§a Nâng Cấp§c Đệ Tử§6 ♦");
        $pk = new BlockActorDataPacket();
        $pk->x = $this->getHolder()->getX();
        $pk->y = $this->getHolder()->getY();
        $pk->z = $this->getHolder()->getZ();
        $pk->namedtag = $w->write($nbt);
        $who->dataPacket($pk);
        parent::onOpen($who);
    }

    public function onClose(Player $who): void{
        $block = Block::get(Block::AIR);
        $block->x = $this->getHolder()->getX();
        $block->y = $this->getHolder()->getY();
        $block->z = $this->getHolder()->getZ();
        $block->level = $this->getHolder()->getLevel();
        $who->getLevel()->sendBlocks([$who], [$block]);
        parent::onClose($who);
    }

    public function getHolder(): Position{
        return $this->holder;
    }

    public function getEntity(): Minion{
        return $this->entity;
    }
	
	public function getDestoryItem(): Item{
        $item = Item::get(Item::REDSTONE_DUST);
        $item->setCustomName(C::RED . "§l§3● §6Lấy lại đệ tử.");
        return $item;
    }

    public function getSpeedUp(): Item{
        $item = Item::get(Item::DIAMOND);
        $item->setCustomName(C::GOLD . "§l§3● §6Nâng cấp tốc độ" . C::GRAY . " (" . C::BLUE . $this->entity->getTime() . "s". C::GRAY . ")");
        $item->setLore([C::YELLOW . "§l§3● §6Gía: " . C::AQUA . $this->entity->getCost() . " §6point"]);
        return $item;
    }
	
    public function getChestItem(): Item{
        $islinked = $this->entity->isChestLinked() ? "§aĐã" : "§cChưa";
        $item = Item::get(Item::CHEST);
        $item->setCustomName(C::DARK_GREEN . "§l§3● §6Liên kết một cái rương");
        $item->setLore([" ",  C::YELLOW . "§l§3● §6Liên kết: " . C::WHITE . $islinked, C::YELLOW . "§l§3● §6Tọa độ:§d " . C::WHITE . $this->entity->getChestCoordinates()]);
        return $item;
    }
}