<?php
namespace nlog\Police;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use _64FF00\PureChat\PureChat;
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\Utils;
class Main extends PluginBase implements Listener{
	
public $pre = "POLICE";
 	 public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
       
		$this->getLogger()->notice("§l§bPlugin Police");
		$this->getLogger()->info("§l§eEnable");
$this->PureChat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
    	
    	
    	//Config
    	@mkdir($this->getDataFolder(), 0744, true);
    	$this->police = new Config($this->getDataFolder() . "office.yml", Config::YAML); //Config 생성
 	 }
 	 
 	 
 	 //경찰 API
 	 public function getPolice() {
 	 	/*
 	 	 * 경찰의 목록을 Config에서 가져옵니다.
 	 	 */
 	 	return $this->police->getAll(true);
 	 }
 	 
 	 public function isPolice($name) {
 	 	/*
 	 	 * Config에 $name이 있으면 true를 없으면 false를 반환합니다.
 	 	 */
 	 	return $this->police->exists($name);
 	 }
 	 
 	 public function setPolice($name) {
 	 	/*
 	 	 * 경찰을 Config에 추가합니다.
 	 	 */
 	 	$this->police->set($name, "police");
 	 	$this->police->save();
 	 	return true;
 	 }
 	 
 	 public function removePolice($name) {
 	 	/*
 	 	 * 경찰을 Config에서 제거합니다.
 	 	 */
 	 	$this->police->remove($name, "police");
 	 	$this->police->save();
 	 	return true;
 	 }
 	 
 	 public function onCommand(CommandSender $sender,Command $cmd,string $label,array $args) :bool{
 	 	
 	 	$msg = "Sử dụng: /police <set|del|help>";
 	 	
 	 	if(strtolower($cmd->getName() === "police")) {
//if(!$sender instanceof Player){
//$sender->sendMessage("§cVui lòng dùng lệnh trong Game");
                //return true;
       // }
 	 		//if (!($sender->isOp())) {
 	 			//$sender->sendMessage("Bạn không có đủ quyền sử dụng lệnh này !");
 	 			//return true; //OP 가 아닐 때 - 안전빵으로 한번 더ㅋㅋ
 	 		//}
 	 		if (!(isset($args[0]))) {
 	 			$sender->sendMessage($msg);
 	 			return true;
 	 		}
//if ($args[0] === "help") {
 	 		//	if (!(isset($args[1]))) {
 	 			//	$sender->sendMessage("/police help: Hướng dẫn lệnh Police\n/police set <player>: Set police cho người chơi\n/police del <player>: Xóa police của người chơi\n/police list: Danh sách Police của server");
 	 			//	return true;
 	 		//	} 
			#-----------------------------------------------------------------------------
 	 		if ($args[0] === "set") {
 	 			if (!(isset($args[1]))) {
 	 				$sender->sendMessage("Sử dụng: /police set <player>");
 	 				return true;
 	 			} //닉네임이 없을 
$p = Server::getInstance()->getPlayer($args[1]);
 	 			if (!$p) {
 	 				$sender->sendMessage("Người này hiện không hoạt động !");
 	 				return true;
 	 			} //닉네임이 경
 	 		$this->setPolice(strtolower($args[1]));
 	 		$p = Server::getInstance()->getPlayer($args[1]);
$name = $sender->getName();
$per = $this->getServer()->getPlayer($name)->addAttachment($this);
 	 	$per->setPermission("pocketmine.command.kick", true);
				$per->setPermission("pocketmine.command.teleport", true);
				$per->setPermission("gb.cmd.invis", true);
				$per->setPermission("freeze.command", true);
$per->setPermission("gb.cmd.mute", true);
$per->setPermission("gb.cmd.unmute", true);
                $per->setPermission("jail.command.jail", true);
				$per->setPermission("jail.command.unjail", true);
 	 		$p->sendMessage("Bạn đã trở thành Helper, hãy hoàn thành tốt nhiệm vụ của mình !");
$this->PureChat->setPolice("§r[§l§eHelper§r] ", $p->getPlayer());
 	 		$sender->sendMessage("Bạn đã set Police thành công cho ".$args[1]."");
			return true;
 	 		}
			#-----------------------------------------------------------------------------
 	 		if ($args[0] === "del") {
 	 			if (!(isset($args[1]))) {
 	 				$sender->sendMessage("Sử dụng: /police del <player>");
 	 				return true;
 	 			} //닉네임이 없을 때
$p = Server::getInstance()->getPlayer($args[1]);
 	 			if (!$p) {
 	 				$sender->sendMessage("Police này hiện không hoạt động !");
 	 				return true;
 	 			} //닉네임이 경
 	 		if (!($this->isPolice(strtolower($args[1])))) {
 	 				$sender->sendMessage("Người này không có trong danh sách Police");
 	 				return true;
 	 			} //닉네임이 경찰이 아닐 때
				
 	 			$this->removePolice($args[1]);
 	 			$p = Server::getInstance()->getPlayer($args[1]);
$name = $sender->getName();
$per = $this->getServer()->getPlayer($name)->addAttachment($this);
 	 	$per->setPermission("pocketmine.command.kick", false);
				$per->setPermission("pocketmine.command.teleport", false);
				$per->setPermission("gb.cmd.invis", false);
				$per->setPermission("gb.cmd.freeze", false);
$per->setPermission("gb.cmd.unfreeze", false);
$per->setPermission("gb.cmd.mute", false);
$per->setPermission("gb.cmd.unmute", false);
 	 			$p->sendMessage("Bạn đã bị cắt chức Helper vì chưa hoàn thành nhiệm vụ !");
$this->PureChat->setPolice("{no}", $p->getPlayer());
 	 			$sender->sendMessage("Xóa Police của ".$args[1]." thành công");
 	 			return true;
 	 		}
			#-----------------------------------------------------------------------------
 	 		if ($args[0] === "list") {
 	 			$list = implode("\n- ", $this->getPolice());
 	 			$sender->sendMessage("Danh sách Police của server:\n" . $list);
 	 			return true; //리스트
				
			}else{
				$sender->sendMessage($msg);
				return true; //$args[0]이 없을 때
			}
 	 	}
 	 }
}