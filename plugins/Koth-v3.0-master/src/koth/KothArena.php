<?php

/**

 * Created: 

 * User: 

 * Date: 

 * Time: 

 */

namespace koth;

use pocketmine\command\ConsoleCommandSender;

use pocketmine\level\Position;

use pocketmine\Player;

use pocketmine\scheduler\Task;

use pocketmine\Server;

use pocketmine\item\Item;

use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;

class KothArena

{

    private $running = false;

    public $players = [];

    public $spawns = [];

    private $p1;

    private $p2;


    public $plugin;


    private $timer = null;

 

    

    public function __construct(KothMain $main, $spawns, $capture){

        $this->plugin = $main;
	   
    

      foreach($spawns as $val => $pos){
				$strPos = explode(':', $pos);
	      if(!$this->plugin->getServer()->isLevelLoaded($strPos[3])){
	      Server::getInstance()->loadLevel($strPos[3]);
	      }

				$this->spawns[] = new Position(intval($strPos[0]), intval($strPos[1]), intval($strPos[2]), $main->getServer()->getLevelByName($strPos[3]));

        }

       $l = explode(":",$capture["p1"]);

        $this->p1 = new Position($l[0],$l[1],$l[2],$main->getServer()->getLevelByName($l[3]));

        $l = explode(":",$capture["p2"]);

        $this->p2 = new Position($l[0],$l[1],$l[2],$main->getServer()->getLevelByName($l[3]));

    }

    public function inCapture(Player $player) : bool {

        

       $l = $player->getPosition();

        $x = $l->getX();

        $z = $l->getZ();

        $y = $l->getY();

        $p1 = $this->p1;

        $p2= $this->p2;

        $minx = min($p1->getX(),$p2->getX());

        $maxx = max($p1->getX(),$p2->getX());

        $minz = min($p1->getZ(),$p2->getZ());

        $maxz = max($p1->getZ(),$p2->getZ());

        $miny = min($p1->getY(),$p2->getY());

        $maxy = max($p1->getY(),$p2->getY());

        

        return ($minx <= $x && $x <= $maxx && $minz <= $z && $z <= $maxz && $miny <= $y && $y <= $maxy);

   }
   public function startGame(){

        $task = new GameTimer($this->plugin,$this);

        $handler = $this->plugin->getScheduler()->scheduleRepeatingTask($task,20);

        $task->setHandler($handler);

        $this->timer = $task;

    }

     public function resetAllPlayers(){

     

        foreach ($this->players as $player => $time){

            $p = $this->plugin->getServer()->getPlayer($player);

            

            if ($p instanceof KothPlayer){

                $p->setInGame(false);

                $p->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
            }

            unset($this->players[$player]);

        }

    }

    public function resetCapture(Player $player){

        if (isset($this->players[$player->getName()])){

            $this->players[$player->getName()] = $this->plugin->getData("capture_time");

        }

    }

    public function resetGame(){

        $this->resetAllPlayers();

        $this->players = [];

        $this->running = false;
	     $this->plugin->started = false;
	    $this->plugin->stopped = true;

        $timer = $this->timer;

        if ($timer instanceof Task && !$timer->getHandler()->isCancelled()) $timer->getHandler()->cancel();

        $this->timer = null;

    }

    public function isRunning() : bool {

        return $this->running;

    }

    public function checkPlayers(){

        foreach ($this->players as $player => $time){

            $p = $this->plugin->getServer()->getPlayer($player);
						  
          if ($p instanceof Player){
			  
			if ($p->isAlive()){
			  
              if($this->inCapture($p)){

                    $time = --$this->players[$player];

                    $this->sendProgress($p,$time);

                    if ($time < 1){

                        $this->won($p);

                    }

                }
				
			}
			
		  }else{

                unset($this->players[$player]);

            }

        }

    }
	public function preStart(){

        $task = new PreGameTimer($this->plugin,$this);

        $handler = $this->plugin->getScheduler()->scheduleRepeatingTask($task,20);

        $task->setHandler($handler);

        $this->timer = $task;

        $this->running = true;
		$this->plugin->started = true;
		$this->plugin->stopped = false;

    }

    public function won(Player $player){

        $prefix = $this->plugin->prefix();

        $msg = $this->plugin->getData("win");

        $msg = str_replace("{player}", $player->getName(), $msg);
        $msg = $prefix.$msg;
		
        $this->plugin->getServer()->broadcastMessage(TextFormat::colorize("§l§6● &b" . $player->getName() . " &6đã thắng trận đấu koth, và đã nhận được một số quà tặng!"));

        $this->giveRewards($player);

        $this->endGame();

    }

    public function removePlayer(Player $player){

        if (isset($this->players[$player->getName()])) unset($this->players[$player->getName()]);

          $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
       

         if($player instanceof KothPlayer){

            $player->setInGame(false);

    }

    }


    public function sendProgress(Player $p, int $time){

        $tip = TextFormat::colorize("&aCapture point &b");

        $max = $this->plugin->getData("capture_time");

        $time = $this->plugin->getData("capture_time") - $time;

        $percent = (($time / $max)*100).'%';

       

        $this->sendTip(TextFormat::colorize("&l&b" . $p->getName() . " &aĐang Chiếm Địa Điểm: &c" . $percent));

     

 if($percent == 1){

     $this->sendTItle(TextFormat::colorize("&b&l" . $p->getName() . " &d&lĐang"), TextFormat::colorize("&d&lChiếm Địa Điểm Koth!"));

 }

    if($percent == 25){

        Server::getInstance()->broadcastMessage(TextFormat::colorize("§l§6● &b" . $p->getName() . " &eĐã chiếm được điểm trong &a" . $percent . " giây."));

    }

    if($percent == 50){

        Server::getInstance()->broadcastMessage(TextFormat::colorize("§l§6● &b" . $p->getName() . " &eĐã chiếm được điểm trong &a" . $percent . " giây."));

    }

    if($percent == 75){

        Server::getInstance()->broadcastMessage(TextFormat::colorize("§l§6● &b" . $p->getName() . " &eĐã chiếm được điểm trong &a" . $percent . " giây."));

    }

    if($percent > 94){

        Server::getInstance()->broadcastMessage(TextFormat::colorize("§l§6● &b" . $p->getName() . " &eĐã chiếm được điểm trong &a" . $percent . " giây."));
        

    }

    }

    public function endGame(){

        foreach ($this->players as $player => $time){

            $p = $this->plugin->getServer()->getPlayer($player);

            if ($p instanceof KothPlayer){
		    $p->setInGame(false);

                 $p->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
                $p->sendMessage(TextFormat::colorize("§l§6● &eSự kiện đã kết thúc."));

            }

            unset($this->players[$player]);

        }

        $this->resetGame();

    }

    public function sendPopup(string $msg){

        foreach ($this->players as $player => $time){

            $p = $this->plugin->getServer()->getPlayer($player);

            if ($p instanceof Player) $p->sendPopup($msg);

        }

    }

     public function sendTip(string $msg){

        foreach ($this->players as $player => $time){

            $p = $this->plugin->getServer()->getPlayer($player);

            if ($p instanceof Player) $p->sendTip($msg);

        }

    }

     public function sendTitle(string $title, string $subtitle){

        foreach ($this->players as $player => $time){

            $p = $this->plugin->getServer()->getPlayer($player);

            if ($p instanceof Player) $p->addTitle($title, $subtitle);

        }

    }

    public function giveRewards(Player $player){
        $rewards = $this->plugin->getRewards();
        $name = $player->getName();
        foreach ($rewards as $key => $reward){
            $reward = str_replace("{player}", $name, $reward);
            $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(),$reward);
        }
    }
    public function addPlayer(Player $player){

        $this->players[$player->getName()] = $this->plugin->getData("capture_time");

        $this->sendRandomSpot($player);

        if($player instanceof KothPlayer){

           $player->setInGame(true);

	}
	}
	public function sendRandomSpot(Player $player) : bool{
		$old = $this->spawns[array_rand($this->spawns)];
		$player->teleport($old);
		return true;
	}
}
