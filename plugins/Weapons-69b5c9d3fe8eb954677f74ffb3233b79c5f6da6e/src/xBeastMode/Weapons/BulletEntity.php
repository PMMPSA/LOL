<?php
namespace xBeastMode\Weapons;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\Server;
use aliuly\worldprotect\WpProtectMgr;

class BulletEntity extends ItemEntity{
        /** @var string */
        public $gunType;
        /** @var Entity */
        public $exempt;
		
		private $iprotect;
        public function __construct(Level $level, CompoundTag $nbt){
                parent::__construct($level, $nbt);
				$this->iprotect = Server::getInstance()->getPluginManager()->getPlugin("iProtector");
        }

        public function onUpdate(int $currentTick): bool{
                if($this->onGround){
                        $this->flagForDespawn();

                        if(isset(GunData::EXPLODE[$this->gunType])){
                                $rad = GunData::EXPLODE[$this->gunType];

                                $explode = new Explosion($this, $rad);
                                $explode->explodeB();
                        }
                }
                return parent::onUpdate($currentTick);
        }

        public function onCollideWithPlayer(Player $player): void{
                if(!$this->onGround){
                        if($player === $this->exempt) return;
						if(!$this->iprotect->canGetHurt($player)){
							return;
						}
                        $e = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_ENTITY_ATTACK, GunData::DAMAGES[$this->gunType]);
                        $e->setAttackCooldown(0);
                        $player->attack($e);

                        if(isset(GunData::EXPLODE[$this->gunType])){
                                $rad = GunData::EXPLODE[$this->gunType];

                                $explode = new Explosion($this, $rad);
                                $explode->explodeB();
                        }

                        $this->flagForDespawn();
                }
        }
}