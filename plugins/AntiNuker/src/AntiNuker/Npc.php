<?php

declare(strict_types=1);

namespace AntiNuker;

use AntiNuker\Main;
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

class Npc extends Human{

    protected $player;

    public function initEntity(): void{
        parent::initEntity();
        $this->setHealth(1);
        $this->setMaxHealth(1);
        $this->setNameTagAlwaysVisible();
        $this->setNameTag("none");
        $this->setScale(0.5);
    }

    public function attack(EntityDamageEvent $source): void{
        $source->setCancelled();
        if($source instanceof EntityDamageByEntityEvent){
            $damager = $source->getDamager();
            if($damager instanceof Player){
                if(!$damager->isOp()){
            return;
                }
            if($damager->getInventory()->getItemInHand()->getId() == Item::BLAZE_ROD) $this->close();
            }
        }
    }

    public function entityBaseTick(int $tickDiff = 1): bool{
        $update = parent::entityBaseTick($tickDiff);
        if($this->getLevel()->getServer()->getTick() % 5 == 0){
        $m = Main::get()->count();
        $this->setNameTag("Top Người Chơi có Tốc Độ đào Block Nhanh\n$m");
        }
        return $update;
    }
}
