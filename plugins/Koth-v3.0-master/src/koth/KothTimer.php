<?php


namespace koth;

use pocketmine\scheduler\Task;

use pocketmine\utils\TextFormat;

use pocketmine\Server;

class KothTimer extends Task

{

    private $arena;

    private $plugin;

    public function __construct(KothMain $owner, KothArena $arena) {

        $this->arena = $arena;

        $this->plugin = $owner;

    }

    public function onRun(int $currentTick) : void {

       if(!file_exists($this->plugin->getDataFolder() . "kothinfo.yml")){

          $this->plugin->setKothTimer($this->plugin->msg->get("event_time"));

       }

       $this->plugin->setKothTimer($this->plugin->getKothTimer() - 1);

       $this->time = $this->plugin->getKothTimer();

       if ($this->time == 30 || $this->time == 15 || $this->time < 6){
      
       $this->plugin->getServer()->broadcastMessage(TextFormat::colorize("§l§6● &eSự kiện koth sẽ bắt đầu trong: &b" . gmdate("i:s", $this->time)));

        }

        $this->time--;

        if ($this->time < 1){

        $this->arena->preStart();
		
        $this->plugin->getServer()->broadcastMessage(TextFormat::colorize("§l§6● &aSự kiện koth đã bắt đầu... &bDùng: &d/koth join &eđể tham gia!"));

        $this->plugin->setKothTimer($this->plugin->getEventTime());
		
		}
	}
}

