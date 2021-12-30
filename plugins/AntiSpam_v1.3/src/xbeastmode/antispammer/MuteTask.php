<?php
namespace xbeastmode\antispammer;
use pocketmine\Player;
use pocketmine\scheduler\Task;
class MuteTask extends Task{
    private $main;
    private $player;
    public function __construct(AntiSpammer $main, Player $p){
        $this->main = $main;
        $this->player = $p;
    }
    public function onRun($tick){
        $this->main->unMutePlayer($this->player);
        $this->player->sendMessage(FMT::colorMessage($this->main->getConfig()->getAll(){"un-muted_message"}));
    }
}