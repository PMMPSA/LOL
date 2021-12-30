<?php

namespace MasApip\AntiNuke;

use pocketmine\entity\Effect;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {
	private $breakTimes = [];
	private $time;
	private $count;

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onJoin(PlayerJoinEvent $ev)
	{
	    $this->time[$ev->getPlayer()->getName()] = time();
		$this->count[$ev->getPlayer()->getName()] = 0;
	}

	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		if($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK){
			$this->breakTimes[$event->getPlayer()->getRawUniqueId()] = floor(microtime(true) * 20);
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void{
		if((!$event->getInstaBreak())){
			$f = false;
			$player = $event->getPlayer();
				$pname = $player->getName();
			do{
				if(!isset($this->breakTimes[$uuid = $player->getRawUniqueId()])){
					$f=true;
					foreach($this->getServer()->getOnlinePlayers() as $staff){
						if($staff->hasPermission("antinuke.notice")){
							if($this->time[$pname] !== time())
							$staff->sendMessage("§cPlayer {$player->getName()} tried to break a block without a start-break action");
						}
					}
					if($this->time[$pname] !== time())
					$this->getLogger()->info("Player " . $player->getName() . " tried to break a block without a start-break action");
					break;
				}

				$target = $event->getBlock();
				$item = $event->getItem();

				$expectedTime = ceil($target->getBreakTime($item) * 20);

				if($player->hasEffect(Effect::HASTE)){
					$expectedTime *= 1 - (0.2 * $player->getEffect(Effect::HASTE)->getEffectLevel());
				}

				if($player->hasEffect(Effect::MINING_FATIGUE)){
					$expectedTime *= 1 + (0.3 * $player->getEffect(Effect::MINING_FATIGUE)->getEffectLevel());
				}

				$expectedTime -= 1;

				$actualTime = ceil(microtime(true) * 20) - $this->breakTimes[$uuid = $player->getRawUniqueId()];

				if($actualTime < $expectedTime){
					$f=true;
					foreach($this->getServer()->getOnlinePlayers() as $staff){
						if($staff->hasPermission("antihack.notice")){
							if($this->time[$pname] !== time())
							$staff->sendMessage("§cPlayer {$player->getName()} tried to break a block too fast, expected $expectedTime ticks, got $actualTime ticks");
						}
					}
					if($this->time[$pname] !== time())
					$this->getLogger()->info("Player " . $player->getName() . " tried to break a block too fast, expected $expectedTime ticks, got $actualTime ticks");
					break;
				}

			}while(false);
			if($this->time[$pname] !== time()){$c = $this->count[$pname]; if($c >= 10){
				foreach($this->getServer()->getOnlinePlayers() as $staff){
						if($staff->hasPermission("antihack.notice")){
							$staff->sendMessage("§cPlayer {$player->getName()} break block too fast $c block in one second");
						}
					}
					$this->getLogger()->info("§cPlayer {$player->getName()} break block too fast $c block in one second");
			} $this->count[$pname] = 0; }
			if($f){ $this->time[$pname] = time(); $this->count[$pname]++;}
				unset($this->breakTimes[$uuid]);
if($this->count[$pname] >= 25) $player->close("Nghi van hack","Nghi van hack");
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		unset($this->breakTimes[$event->getPlayer()->getRawUniqueId()]);
	}
}
