<?php
namespace koth;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\level\Position;
use pocketmine\Server;

class KothCommand extends Command{
    private $plugin;
    public function __construct($name, KothMain $main)
    {
        parent::__construct($name, "");
        $this->plugin = $main;
	    $this->arena = $main->arena;
    }
    public function execute(CommandSender $sender, $commandLabel, array $args){
        if ($sender instanceof KothPlayer){
		if(isset($args[0])){
				if(strtolower($args[0]) === "help"){
					$this->sendPlayerHelp($sender);
					return true;
                }elseif (strtolower($args[0]) === "leave"){
                    if($this->plugin->isRunning()){
                        if($sender instanceof KothPlayer){
                            if(!$sender->isInGame()){
                            $sender->sendMessage(TextFormat::colorize("§l§6● &eBạn không ở trong sự kiện koth."));
                            }else{
                     
                       $this->plugin->sentToKoth($sender, true);
                        $sender->sendMessage(TextFormat::colorize("§l§6● &6Bạn đã thoát khỏi sự kiện."));
                        $sender->setInGame(false);
						 }
                        }
                        }else{
                        $sender->sendMessage(TextFormat::colorize("§l§6● &eKhông có sự kiện vào thời gian này. &aSự kiện tiếp theo diễn ra vào: &b" . gmdate("H:i:s", $this->plugin->getKothTimer())));
                    return true;
}
 }elseif (strtolower($args[0]) === "join"){
                    if($this->plugin->isRunning()){
                        if($sender instanceof KothPlayer){
                            if($sender->isInGame()){
                            $sender->sendMessage(TextFormat::colorize("§l§6● &eBạn đã ở trong sự kiện."));
                           }else{
                         $this->plugin->sentToKoth($sender);
                        $sender->sendMessage(TextFormat::colorize("§l§6● &aBạn đã tham gia sự kiện."));
                        $sender->setInGame(true);
                            }
                        }
                        }else{
                        $sender->sendMessage(TextFormat::colorize("§l§6● &eKhông có sự kiện vào thời gian này. &aSự kiện tiếp theo diễn ra vào: &b" . gmdate("H:i:s", $this->plugin->getKothTimer())));
                    }
                    return true;
				}else if(strtolower($args[0]) === "seteventtime"){
					if(!isset($args[1])){
						$sender->sendMessage(TextFormat::colorize("&cPlease use: /koth seteventtime <time-in-seconds>"));
						return true;
					}
					if(!is_numeric($args[1])){
						$sender->sendMessage(TextFormat::colorize("&cKoth Event Timer must be a number."));
						return true;
					}
					
					$this->plugin->setEventTime((int)$args[1]);
					$translatetoseconds = (int)$args[1] * 20;
					$sender->sendMessage(TextFormat::colorize("&aKoth Event timer has been set to &b" . $args[1] . " &aminutes &b(" . $translatetoseconds . " seconds)"));
					return true;

				}elseif(strtolower($args[0]) === "setgametime"){
					if(!isset($args[1])){
						$sender->sendMessage(TextFormat::colorize("&cPlease use: /koth setgametime <game-time-in-minutes>"));
						return true;
					}
					if(!is_numeric($args[1])){
						$sender->sendMessage(TextFormat::colorize("&cKoth Game Timer must be a number."));
						return true;
					}
					$this->plugin->setGameTime((int)$args[1]);
					$translatetoseconds = (int)$args[1] * 20;
					$sender->sendMessage(TextFormat::colorize("&aKoth Game Time has been set to &b" . $args[1] . " &aminutes &b(" . $translatetoseconds . " seconds)"));
					return true;

                } else if (strtolower($args[0]) === "setspawn"){
                    if (!$sender->hasPermission("koth.start")) return true;
                    $this->plugin->setPoint($sender,"spawn");
                    $sender->sendMessage("Successfully Added spawnpoint!");
                    return true;

                } else if (strtolower($args[0]) === "pos1"){
                    if (!$sender->hasPermission("koth.start")) return true;
                    $this->plugin->setPoint($sender,"p1");
                    $sender->sendMessage("Successfully Added p1 point (make sure to set p2)");

                } else if (strtolower($args[0]) === "pos2"){
                    if (!$sender->hasPermission("koth.start")) return true;
                    $this->plugin->setPoint($sender,"p2");
                    $sender->sendMessage("Successfully Added p2 point!");
                 
                } else if (strtolower($args[0]) === "start"){
                    if (!$sender->hasPermission("koth.start")) return true;
                             if(!$this->plugin->hasStartedKoth() && !$this->plugin->isRunning()){
                        $this->plugin->startKoth();
                         $this->plugin->setStartKoth(true);
                         $this->plugin->setStopKoth(false);
                   $sender->sendMessage(TextFormat::colorize("&6KOTH Event Starting..."));
                             }else{
                                $sender->sendMessage(TextFormat::colorize("&cKoth game already started."));
                             }     

                } else if (strtolower($args[0]) === "stop"){
                    if (!$sender->hasPermission("koth.stop")) return true;
                             if(!$this->plugin->hasStoppedKoth() && $this->plugin->isRunning()){
                         $this->plugin->stopKoth();
                         $this->plugin->setStopKoth(true);
                         $this->plugin->setStartKoth(false);
                        $sender->sendMessage(TextFormat::colorize("&6KOTH Event Stopped."));
                             }else{
                                 $sender->sendMessage(TextFormat::colorize("&cKoth game already stopped."));
                             return true;
                             }
                } else{
                  if(!isset($args[0])) $this->sendPlayerHelp($sender);
                    if ($sender->isOp()) $this->sendAdminHelp($sender);
                    if (!$sender->isOp()) $sender->sendMessage(TextFormat::colorize("&l&6[&cKoth&6] &aTham giam koth với lệnh&b /koth join"));
        } 
            }else{
          		if(!isset($args[0])) $this->sendPlayerHelp($sender);
                if ($sender->isOp()) $this->sendAdminHelp($sender);
                if (!$sender->isOp()) $sender->sendMessage(TextFormat::colorize("&l&6[&cKoth&6] &aTham giam koth với lệnh&b /koth join"));
            }
        }else{
            if (isset($args[0])){
                if (strtolower($args[0]) === "start"){
                   // if($sender instanceof KothPlayer){
                             if(!$this->plugin->hasStartedKoth() && !$this->plugin->isRunning()){
                         $this->plugin->startKoth();
                         $this->plugin->setStartKoth(true);
                         $this->plugin->setStopKoth(false);
                        $sender->sendMessage(TextFormat::colorize("&6KOTH Event Starting..."));
                             }else{
                                 $sender->sendMessage(TextFormat::colorize("&cKoth game already started."));
                             }

                } else if (strtolower($args[0]) === "stop"){
                             if(!$this->plugin->hasStoppedKoth() && $this->plugin->isRunning()){
                         $this->plugin->stopKoth();
                        $this->plugin->setStopKoth(true);
                         $this->plugin->setStartKoth(false);
                        $sender->sendMessage(TextFormat::colorize("&6KOTH Event Stopped."));
                             }else{
                                 $sender->sendMessage(TextFormat::colorize("&cKoth game already stopped."));
                             }
                           return true;
}
}
            $sender->sendMessage("Error- Cant run that in console!");
        }
      return true;
}
	public function sendPlayerHelp(CommandSender $sender){
		$sender->sendMessage(TextFormat::colorize("§l§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬§c【 §dKoth §c】§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬"));
		$sender->sendMessage(TextFormat::colorize("&l&b/koth join &6- &aTham Gia Koth."));
		$sender->sendMessage(TextFormat::colorize("&l&b/koth leave &6- &cThoát Koth."));
		}
    public function sendAdminHelp(CommandSender $sender){
        $sender->sendMessage(TextFormat::colorize("&4&lKoth Admin Setup"));
		$sender->sendMessage(TextFormat::colorize("&cHere are the 4 steps you need to setup before fully completing your koth arena."));
		$sender->sendMessage(TextFormat::colorize("&4&l1. &r&cGo to the first position capture point, and type /koth pos1 - Required"));
		$sender->sendMessage(TextFormat::colorize("&4&l2. &r&cGo to the second position capture point, and type /koth pos2 - Required"));
		$sender->sendMessage(TextFormat::colorize("&4&l3. &r&cOnce you set both positions of the koth capture point, you can use /koth setspawn, which will allow you to set as much spawn points as possible. - Required"));
		$sender->sendMessage(TextFormat::colorize("&4&l4. &r&cWant to set the game timer? Now you can! Just type /koth setgametime <game-time-in-minutes> - Optional"));
		$sender->sendMessage(TextFormat::colorize("&4&l5. &r&cWant to set the event timer? Now you can! Just type /koth seteventtime <game-time-in-seconds> - Required"));
		$sender->sendMessage(TextFormat::colorize("&4&l6. &r&cOnce you've completed those three steps, you will be required to restart your server."));
    }
}
