<?php
namespace MCPEVN;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;

class Kickk extends PluginBase implements Listener
{

	public $user;

   public function onEnable()
   {
        if(!is_dir($this->getDataFolder()))	
	{
        mkdir($this->getDataFolder());
        }
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }


    public function onCommand(CommandSender $sender, Command $cmd,string $label, array $args) : bool
    {
        if($cmd->getName()=="kickk")
		{
			if(!$sender->HasPermission("kickk.vip")){
				return true;
			}
			if(isset($this->user[$sender->getName()])){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã hết lượt kick!");
				return true;
			}else{
				if(isset($args[0])){
					$name = array_shift($args);
					$n = $sender->getName();
					$reason = trim(implode(" ", $args));
					if($reason == ""){
						$reason = "§l§6[§bSky§aBlock(Bin)§6]§e Không xác định";
					}
					if(($player = $sender->getServer()->getPlayer($name)) instanceof Player){
						$player->kick($reason);
						$this->user[$sender->getName()] = true;
						$this->getServer()->broadcastMessage("§l§3● §eNgười chơi§c $n §eđã kick§a $name §era khỏi server với lý do:§b ". $reason);
						return true;
					}else{
						$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§b $name §ekhông tồn tại");
						return true;
					}					
				}
				$sender->sendMessage("§l§c/kickk <player> <reason>");
				return true;
				
			}
		}
		return true;
    }
}
