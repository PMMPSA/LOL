<?php
namespace xBeastMode\Weapons;
use pocketmine\event\Listener;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\event\player\PlayerInteractEvent;;
use pocketmine\event\player\PlayerQuitEvent;;
use pocketmine\event\entity\EntityLevelChangeEvent;
class WeaponsListener implements Listener{
        /** @var Weapons */
        protected $core;

        /**
         * @param Weapons $core
         */
        public function __construct(Weapons $core){
                $this->core = $core;
        }

        /**
         * @param PlayerInteractEvent $event
         */
        public function onInteract(PlayerInteractEvent $event){
			if ($event->isCancelled()) return;
                $item = $event->getItem();
                $player = $event->getPlayer();
				$level = $event->getBlock()->getLevel()->getFolderName();
           if(in_array($level, array('Koth'))){
                if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_AIR){
                        if($item->hasCustomBlockData() && $item->getCustomBlockData()->hasTag("gunType")){
                                $gunType = $item->getCustomBlockData()->getString("gunType");
                                if(in_array($gunType, GunData::FULL_AUTO)){
                                        $this->core->toggleGun($player);
                                }else{
                                        if($this->core->fire($player, $item)){
                                                $gunType = $item->getCustomBlockData()->getString("gunType");
                                                RandomUtils::playSound("firework.blast", $player, 500, GunData::SHOT_PITCH[$gunType]);
                                        }else{
                                                RandomUtils::playSound("random.click", $player, 500, 0.5);
                                                $player->sendTip("§l§cĐã Hết Đạn");
										}
								}
						}
				}
		   }
		}
		
		public function onQuit(PlayerQuitEvent $event){
		   if($event->getPlayer() instanceof Player){
			   $player = $event->getPlayer();
				$this->core->offGun($player);
		   }
		}
		
		public function onWorldChange(EntityLevelChangeEvent $event){
		   if($event->getEntity() instanceof Player){
			   $level = $event->getTarget()->getFolderName();
               if(in_array($level, array('sb', 'boss', 'skyblock', 'ender', 'teanether'))){
					$player = $event->getEntity();
					$this->core->offGun($player);
			   }
		   }
		}
}		