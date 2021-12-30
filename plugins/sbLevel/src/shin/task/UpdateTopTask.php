<?php

namespace Shin\task;

use pocketmine\scheduler\Task;
use Shin\sbLevel as LVTM;

class UpdateTopTask extends Task {

    private $owner;
    private $amount;

    public function __construct(LVTM $owner){
        $this->owner = $owner;
    }

    public function onRun(int $currentTick): void{

		$this->owner->getServer()->getAsyncPool()->submitTask(new TopTask($this->owner->getAll(), 1));

    }
}
