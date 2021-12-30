<?php

namespace noob;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\Player;
use noob\AutoFix;

Class FixTask extends Task{


    public function __construct(AutoFix $plugin){
        $this->plugin = $plugin;
    }

     public function onRun($tick){
		$this->plugin->onFix();
    }

	public function cancel(){
      $this->getHandler()->cancel();
    }
}
