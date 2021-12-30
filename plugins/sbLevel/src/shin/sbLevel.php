<?php

namespace Shin;

use pocketmine\{Server, Player};
use pocketmine\plugin\PluginBase;
use pocketmine\command\{CommandSender, Command};
use pocketmine\utils\TextFormat;

use pocketmine\event\Listener;

use pocketmine\item\Item;

use pocketmine\event\player\{PlayerInteractEvent, PlayerItemHeldEvent, PlayerJoinEvent, PlayerChatEvent};
use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent, EntityLevelChangeEvent};

use pocketmine\block\{Block, Lava, Water};

use pocketmine\utils\Config;

use Shin\task\{SortTask, UpdateTopTask};
use MyPlot\MyPlot;
use aliuly\worldprotect\WpPvpMgr;

use pocketmine\level\Level;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class sbLevel extends PluginBase implements Listener{
	
    private $updateTask;
    private $particles = [];
	
    public function onEnable(){
        if(!file_exists($this->getDataFolder())){
            @mkdir($this->getDataFolder());
        }
        self::$instance = $this;
        $this->lv = new Config($this->getDataFolder() . "leveltomine.yml", Config::YAML);
		$this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$this->initParticles();
		$this->getScheduler()->scheduleRepeatingTask($this->updateTask = new UpdateTopTask($this), max(180, $this->cfg->get('Update-Interval')) * 20);
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        $this->getLogger()->info("TopDao is enabled!");
		$this->myplot = MyPlot::getInstance();
		
    }
	
    public function onDisable(): void{
        foreach($this->particles as $level => $particles){
            $level = $this->getServer()->getLevelByName($level);

            if($level instanceof Level){
                foreach($particles as $particle){
                    $particle->setInvisible();
                    $level->addParticle($particle);
                }
            }
        }

        $this->particles = [];
        $this->getScheduler()->cancelTask($this->updateTask->getTaskId());
    }


    /**
    * @priority MONITOR
    * @ignoreCancelled true
    */
	
    public function onLevelChange(EntityLevelChangeEvent $event){
        if($event->getEntity() instanceof Player){
            $this->removeParticles($event->getOrigin(), [$event->getEntity()]);
            $this->sendParticles($event->getTarget(), [$event->getEntity()]);
        }
    }
	
	public function getAll(){
		$all = array();
		foreach($this->lv->getAll() as $key => $val){
			$all[$key] = $val[2]; 
			
		}
		return $all;
	}
	public function onCommand(CommandSender $s, Command $cmd, string $label, array $args): bool
    {
		if (strtolower($cmd->getName() == "topdao")) {
			$this->sendTop($s, $this->getAll(), 1);
		}
		return false;
	}	
	
    private function initParticles(): void{
        foreach((array) $this->cfg->get('Positions') as $pos){
            if(($level = $this->getServer()->getLevelByName($pos['world'])) instanceof Level){
                $particle = new FloatingTextParticle(new Vector3($pos['x'], $pos['y'], $pos['z']), '', $this->cfg->get('Header'));
                $particle->encode(); // prevent empty batch error
                $this->particles[$level->getFolderName()][] = $particle;
            }
        }
    }

    public function getParticles(): array{
        return $this->particles;
    }

    public function sendParticles(Level $level = null, array $players = null){
        if($level === null){
            foreach(array_keys($this->particles) as $level){
                if(($level = $this->getServer()->getLevelByName($level)) instanceof Level){
                    $this->sendParticles($level);
                }
            }

            return;
        }

        if($players === null){
            $players = $level->getPlayers();
        }

        foreach($this->particles[$level->getFolderName()] ?? [] as $particle){
            $particle->setInvisible(false);
            $level->addParticle($particle, $players);
        }
    }

    public function removeParticles(Level $level, array $players = null){
        if($players === null){
            $players = $level->getPlayers();
        }

        foreach($this->particles[$level->getFolderName()] ?? [] as $particle){
            $particle->setInvisible();
            $level->addParticle($particle, $players);
            $particle->setInvisible(false);
        }
    }

    public function updateParticles(): void{
        $text = '';
		$text = $this->cfg->get('text');
        foreach($this->particles as $levelParticles){
            foreach($levelParticles as $particle){
                $particle->setText($text);
            }
        }
    }	
	
    public static function sendTop($receiver, $data,  int $page = 1, Form $closeForm = null)
    {
        if ($receiver instanceof Player) $receiver = $receiver->getLowerCaseName();
        $server = Server::getInstance();
       
        $banned = [];
        foreach ($server->getNameBans()->getEntries() as $entry) {
            if (isset($data[strtolower($entry->getName())])) {
                $banned[] = $entry->getName();
            }
        }
        $ops = [];
        foreach ($server->getOps()->getAll() as $op) {
            if (isset($data[strtolower($op)])) {
                $ops[] = $op;
            }
        }

        $task = new SortTask($receiver, $data, false, $page, $ops, $banned, $closeForm);
        $server->getAsyncPool()->submitTask($task);
    }
	
    public function onJoin(PlayerJoinEvent $ev){
		$this->sendParticles($ev->getPlayer()->getLevel(), [$ev->getPlayer()]);
        $p = $ev->getPlayer()->getName();
        if(!($this->lv->exists(strtolower($p)))){
            $this->lv->set(strtolower($p), [0,99,1]);
            $this->lv->save();
            return true;
        }
    }
	public function checkBlock($block, $p){
			 
			$water = false;
			$lava = false;
			for ($i = 2; $i <= 5; $i++) {
				//echo " $i: ";
				$nearBlock = $block->getSide($i);
				if ($nearBlock instanceof Water) {
					$water = true;
				} else if ($nearBlock instanceof Lava) {
					$lava = true;
				}
				
			}
			return ($water && $lava);
	}  
	
	public function getScore($id){
		switch($id){
			case 42:
			case 41:
				$score = 2;
			break;

			case 152:
			case 22:
				$score = 3;
			break;

			case 57:
			case 133:
				$score = 4;
			break;

			default:
				$score = 1;
			break;
		}
		return $score;
	}

    public function onPlace(BlockPlaceEvent $ev){			
        $block = $ev->getBlock();
        $p = $ev->getPlayer();
			//if($this->onEventOnBlock($ev)){		
				$world = $ev->getPlayer()->getLevel()->getName();
				$f = $this->getServer()->getDataPath() . "worlds/$world/wpcfg.yml";
				if($ev->isCancelled()) {
                   return;
				}
			    if(in_array($block->getId(),[42, 41, 152, 22, 57, 133, 1])){
				if(is_file($f)) {
					$wcfg = (new Config($f, Config::YAML))->getAll();
					if(!isset($wcfg["protect"])){		
						$score = $this->getScore($block->getId());
						$n = $this->lv->get(strtolower($p->getName()));
						$name = strtolower($p->getName());
						$n[0] = $this->getCurrentExp($p) + $score;
						$this->lv->set(strtolower($p->getName()), $n);
						$this->lv->save();
						$p->sendPopup("§l§6Exp §a[§c" . $this->lv->get($name)[0] . "§6/§c" . $this->lv->get($name)[1] . "§a]\n");
						if($this->getCurrentExp($p) >= $this->getLevelUpExp($p)){
							$n[0] = 0;
							$n[1] = $this->getNextLevelUpExp($p);
							$n[2] = $this->getNextLevel($p);
							$this->lv->set(strtolower($p->getName()), $n);
							$this->lv->save();
							$this->getServer()->broadcastMessage("§l§6● §a" . $name . "§e đã lên cấp đảo:§c ".$this->getCurrentLevel($p)." ");
						}
						return;
					
					}
					$p->sendPopup("§l§c Bạn đang ở ngoài vùng được cộng EXP");
					return;
				}
			}
			}
    /*private function onEventOnBlock($event)
    {
        $levelName = $event->getBlock()->getLevel()->getName();
        if (!$this->myplot->isLevelLoaded($levelName)) {
            return;
        }
        $plot = $this->myplot->getPlotByPosition($event->getBlock());
        if ($plot !== null) {
            $username = $event->getPlayer()->getName();
            if ($plot->owner == $username or $plot->isHelper($username) or $event->getPlayer()->hasPermission("myplot.admin.build.plot")) {
                if (!($event instanceof PlayerInteractEvent and $event->getBlock() instanceof Sapling))
                    return;
                $block = $event->getBlock();
                $maxLengthLeaves = (($block->getDamage() & 0x07) == Sapling::SPRUCE) ? 3 : 2;
                $beginPos = $this->myplot->getPlotPosition($plot);
                $endPos = clone $beginPos;
                $beginPos->x += $maxLengthLeaves;
                $beginPos->z += $maxLengthLeaves;
                $plotSize = $this->myplot->getLevelSettings($levelName)->plotSize;
                $endPos->x += $plotSize - $maxLengthLeaves;
                $endPos->z += $plotSize - $maxLengthLeaves;

                if ($block->x >= $beginPos->x and $block->z >= $beginPos->z and $block->x < $endPos->x and $block->z < $endPos->z) {
                    return;
                }
            }
        } elseif ($event->getPlayer()->hasPermission("myplot.admin.build.road")) {
            return;
        }
        
		return false;
    }*/
    
    public function getNextLevel($player){
        if($player instanceof Player){
             $player = strtolower($player->getName());
        }
        
        $player = strtolower($player);
        $lv = $this->lv->get($player)[2] + 1;
        return $lv;
    }
    
    public function getLevelUpExp($player){
        if($player instanceof Player){
             $player = strtolower($player->getName());
        }
        
        $player = strtolower($player);
        $e = $this->lv->get($player)[1];
        return $e;
    }
    
    public function getCurrentLevel($player){
        if($player instanceof Player){
             $player = strtolower($player->getName());
        }
        
        $player = strtolower($player);
        $lv = $this->lv->get($player)[2];
        return $lv;
    }
    
    public function getCurrentExp($player){
        if($player instanceof Player){
             $player = strtolower($player->getName());
        }
        
        $player = strtolower($player);
        $e = $this->lv->get($player)[0];
        return $e;
    }
    
    public function getNextLevelUpExp($player){
        if($player instanceof Player){
            $player = strtolower($player->getName());
        }
        
        $player = strtolower($player);
        $e = $this->lv->get($player)[1];
        return $e + 99;
    }
	
	private static $instance;
	
    public static function getInstance(){
        return self::$instance;
    }
	
}

?>