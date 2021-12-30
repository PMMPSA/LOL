<?php

declare(strict_types=1);

namespace AntiNuker;

use AntiNuker\Npc;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as C;
use pocketmine\scheduler\ClosureTask;
use pocketmine\event\block\BlockBreakEvent;

class Main extends PluginBase implements Listener{
    
    public $bl;
    public $bl2;
    public $time;
    private static $instance;

	public function onLoad(): void{
		self::$instance = $this;
	}
	
	public static function get(): self{
		return self::$instance;
	}

	public function onEnable(): void{
	Entity::registerEntity(Npc::class, true);
    $this->bl["AntiNuker"] = 0;
    $this->time = time();
    $this->task();
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function count(){
	if(!isset($this->bl2)) return "none";
	$a = $this->bl2;
	arsort($a);
	$t = 0;
	foreach($a as $p=>$b){
	if($t !== 9){
	$l[$t] = "$p: $b";
	$t++;
	}else break;
	}
	return implode("\n",$l);
	}

    public function task():void{
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function($_) : void{
    if($this->time !== time()){
	    if(count($this->bl) >= 1){
    foreach($this->bl as $p => $t){
    $this->bl2[$p] = $t;
    $this->bl[$p] = 0;
    }
	    }
	$this->time = time();
    }
        }), 1);
    }
    
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
	    if($command->getName() === "antinuker"){
          if($sender->hasPermission("antinuker.cmd"))
	           if($sender instanceof Player){
	$this->spawnnpc($sender);
            }
	    }
        return true;
    }
    
    public function spawnnpc(Player $p){
        $nbt = Entity::createBaseNBT($p, null, (90 + ($p->getDirection() * 90)) % 360);
        $nbt->setTag($p->namedtag->getTag("Skin"));
        $entity = new Npc($p->getLevel(), $nbt);
        $entity->spawnToAll();
    }
    
    public function onBreak(BlockBreakEvent $ev) : void {
	$this->bl[$ev->getPlayer()->getName()]++;
	}
	public function onJoin(PlayerJoinEvent $ev){
$this->bl[$ev->getPlayer()->getName()] = 0;
	}
}
