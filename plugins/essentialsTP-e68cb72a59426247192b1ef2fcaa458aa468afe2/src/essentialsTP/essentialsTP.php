<?php
/**
 * author: advocaite aka serverkart_rod
 * MONETISE YOUR POCKETMINE SERVER WITH http://serverkart.com
 * skype: advocaite
 */

namespace essentialsTP;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\tile\Sign;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\Server;





class essentialsTP extends PluginBase  implements CommandExecutor, Listener {
    /** @var \SQLite3 */
    private $db2;
    /** @var string */
    public $username;
    /** @var string */
    public $world;
    /** @var string */
    public $home_loc;
    /** @var string */
    public $warp_loc;
    /** @var Position[] */
    public $death_loc;
    /** @var Config */
    public $config;
    /** @var int[] */
    public $player_cords;
    /** @var string */
    public $tp_sender;
    /** @var string */
    public $tp_reciver;
    /** @var \SQLite3Result */
    public $result;
    /** @var \SQLite3Stmt */
    public $prepare;

    public function fetchall(){
        $row = array();

        $i = 0;

        while($res = $this->result->fetchArray(SQLITE3_ASSOC)){

            $row[$i] = $res;
            $i++;

        }
        return $row;
    }

    public function onLoad(){

    }


    public function onPlayerDeath(PlayerDeathEvent $event){
        $player = $event->getEntity();
        $this->death_loc[$player->getName()] = new Position(
            round($player->getX()),
            round($player->getY()),
            round($player->getZ()),
            $player->getLevel()
        );
    }

    /*public function onPlayerSleep(PlayerBedEnterEvent $event){
        if($this->config->get("bed-sets-home") == true)
        {
            $player = $event->getPlayer();
            if ($player->hasPermission("essentialstp.command.bedsethome")) {
                $this->player_cords = array('x' => (int) $player->getX(),'y' => (int) $player->getY(),'z' => (int) $player->getZ());
                $this->username = $player->getName();
                $this->world = $player->getLevel()->getName();
                $this->home_loc = "bed";
                $this->prepare = $this->db2->prepare("SELECT player,title,x,y,z,world FROM homes WHERE player = :name AND title = :title");
                $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                $this->result = $this->prepare->execute();
                $sql          = $this->fetchall();
                if( count($sql) > 0 )
                {
                    $this->prepare = $this->db2->prepare("UPDATE homes SET world = :world, title = :title, x = :x, y = :y, z = :z WHERE player = :name AND title = :title");
                    $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                    $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                    $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                    $this->prepare->bindValue(":x", $this->player_cords['x'], SQLITE3_TEXT);
                    $this->prepare->bindValue(":y", $this->player_cords['y'], SQLITE3_TEXT);
                    $this->prepare->bindValue(":z", $this->player_cords['z'], SQLITE3_TEXT);
                    $this->result = $this->prepare->execute();

                }
                else
                {
                    $this->prepare = $this->db2->prepare("INSERT INTO homes (player, title, world, x, y, z) VALUES (:name, :title, :world, :x, :y, :z)");
                    $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                    $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                    $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                    $this->prepare->bindValue(":x", $this->player_cords['x'], SQLITE3_TEXT);
                    $this->prepare->bindValue(":y", $this->player_cords['y'], SQLITE3_TEXT);
                    $this->prepare->bindValue(":z", $this->player_cords['z'], SQLITE3_TEXT);
                    $this->result = $this->prepare->execute();

                }
            }
        }
    }

    public function onPlayerRespawn(PlayerRespawnEvent $event){
        $player = $event->getPlayer();
        if ( isset($this->death_loc[$player->getName()]) ){
            $this->username = $player->getName();
            $this->prepare = $this->db2->prepare("SELECT player,x,y,z,title,world FROM homes WHERE player =:name AND title =:bed");
            $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
            $this->prepare->bindValue(":bed", 'bed', SQLITE3_TEXT);
            $this->result = $this->prepare->execute();
            $sql = $this->fetchall();
            if (count($sql) > 0){
                $sql = $sql[0];
                foreach($player->getServer()->getLevels() as $aval_world => $curr_world)
                {
                    if ($sql['world'] == $curr_world->getName())
                    {
                        $event->setRespawnPosition(new Position((int) $sql['x'], (int) $sql['y'], (int) $sql['z'], $curr_world));
                        $player->sendMessage($this->config->get("Lang_teleport_home"));
                        return true;
                    }
                }
            }
        }

    }*/

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
        switch($cmd->getName()){
            case 'home':
                if ($sender instanceof Player)
                {
                    if (!$sender->hasPermission("essentialstp.command.home")) {
                        $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_permissions"));
                        return true;
                    }
                    $this->username = $sender->getName();
                    if (count($args) == 0)
                    {
                        $this->prepare = $this->db2->prepare("SELECT player,x,y,z,title,world FROM homes WHERE player =:name");
                        $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $sql = $this->fetchall();
                        $home_list = null;
                        foreach ($sql as $ptu)
                        {
                            $home_list .= '['.TextFormat::GOLD.$ptu['title'].TextFormat::WHITE.'] ';
                        }
                        if($home_list != null){
                            $sender->sendMessage($this->config->get("Lang_your_homes")." ".$home_list);
                            return true;
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_no_home_set"));
                            return true;
                        }

                    }else{
                        $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $cool_sql = $this->fetchall();
                        if (count($cool_sql) > 0){
                            $this->home_loc = $args[0];
                            $this->prepare = $this->db2->prepare("SELECT player,title,x,y,z,world FROM homes WHERE player = :name AND title = :title");
                            $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                            $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                            $sql = $this->fetchall();
                            if( count($sql) > 0 ) {
								$sql = $sql[0];
								if(isset($sql['world']) && Server::getInstance()->loadLevel($sql['world']) != false){
									$curr_world = Server::getInstance()->getLevelByName($sql['world']);
									$pos = new Position((int) $sql['x'], (int) $sql['y'], (int) $sql['z'], $curr_world);
									$sender->teleport($pos);
									$sender->sendMessage($this->config->get("Lang_teleport_home"));
									return true;
								}else{
									$sender->sendMessage(TextFormat::RED . $this->config->get("Land_chunk_not_loaded"));
									return true;
								}
							}
                            else
                            {
                                $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_no_home_name"));
                                return true;
                            }
                        }
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
                    return true;
                }
                break;
            case 'sethome':
                if ($sender instanceof Player)
                {
                    if (!$sender->hasPermission("essentialstp.command.sethome")) {
                        $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_permissions"));
                        return true;
                    }
                    if((count($args) != 0) && (count($args) < 2))
                    {
                        $this->player_cords = array('x' => (int) $sender->getX(),'y' => (int) $sender->getY(),'z' => (int) $sender->getZ());
                        $this->username = $sender->getName();
                        $this->world = $sender->getLevel()->getName();
                        $this->home_loc = $args[0];
                        $this->prepare = $this->db2->prepare("SELECT player,title,x,y,z,world FROM homes WHERE player = :name AND title = :title");
                        $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                        $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $sql          = $this->fetchall();
                        if( count($sql) > 0 )
                        {
                            $this->prepare = $this->db2->prepare("UPDATE homes SET world = :world, title = :title, x = :x, y = :y, z = :z WHERE player = :name AND title = :title");
                            $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                            $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                            $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                            $this->prepare->bindValue(":x", $this->player_cords['x'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":y", $this->player_cords['y'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":z", $this->player_cords['z'], SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();

                        }
                        else
                        {
                            $this->prepare = $this->db2->prepare("INSERT INTO homes (player, title, world, x, y, z) VALUES (:name, :title, :world, :x, :y, :z)");
                            $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                            $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                            $this->prepare->bindValue(":world", $this->world, SQLITE3_TEXT);
                            $this->prepare->bindValue(":x", $this->player_cords['x'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":y", $this->player_cords['y'], SQLITE3_TEXT);
                            $this->prepare->bindValue(":z", $this->player_cords['z'], SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();

                        }
                        $sender->sendMessage($this->config->get("Lang_home_set")." ".TextFormat::GOLD.$args[0]);
                        return true;
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_invalid_usage"));
                        return false;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
                    return true;
                }
                break;
            case 'delhome':
                if ($sender instanceof Player)
                {
                    if (!$sender->hasPermission("essentialstp.command.delhome")) {
                        $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_permissions"));
                        return true;
                    }
                    if((count($args) != 0) && (count($args) < 2))
                    {
                        $this->username = $sender->getName();
                        $this->home_loc = $args[0];
                        $this->prepare = $this->db2->prepare("SELECT * FROM homes WHERE player = :name AND title = :title");
                        $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                        $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        $sql          = $this->fetchall();
                        if( count($sql) > 0 )
                        {
                            $this->prepare = $this->db2->prepare("DELETE FROM homes WHERE player = :name AND title = :title");
                            $this->prepare->bindValue(":name", $this->username, SQLITE3_TEXT);
                            $this->prepare->bindValue(":title", $this->home_loc, SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                            $sender->sendMessage($this->config->get("Lang_home_delete_1")." ".TextFormat::GOLD.$this->home_loc.TextFormat::WHITE." ".$this->config->get("Lang_home_delete_2"));
                            return true;
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_no_home_name"));
                            return true;
                        }
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_invalid_usage"));
                        return false;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
                    return true;
                }
                break;
            case 'tpa':
                if (!$sender->hasPermission("essentialstp.command.tpa")) {
                    $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_permissions"));
                    return true;
                }
                if ($sender instanceof Player)
                {
                  if ((count($args) != 0) && (count($args) < 2)) {
                            if (trim(strtolower($sender->getName())) == trim(strtolower($args[0]))) {
                                $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_teleport_self"));
                                return true;
                            }
                            $this->tp_sender  = $sender->getName();
                            $this->tp_reciver = $args[0];
                            if ($this->getServer()->getPlayer($this->tp_reciver) instanceof Player) {
                                $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_sent_request_you"). ' '. TextFormat::GOLD . $this->tp_sender . TextFormat::WHITE);
                                $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_type").' ' . TextFormat::GOLD . '/tpaccept' . TextFormat::WHITE . ' '.$this->config->get("Lang_accept_request"));
                                $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_type").' ' . TextFormat::GOLD . '/tpdeny' . TextFormat::WHITE . ' '.$this->config->get("Lang_decline_request"));
                                $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_request_expire_1").' ' . TextFormat::GOLD .$this->config->get("tpa-here-cooldown").' '.$this->config->get("Lang_request_expire_2") . TextFormat::WHITE . ' '.$this->config->get("Lang_request_expire_3"));
                                $this->prepare = $this->db2->prepare("INSERT INTO tp_requests (player, player_from, type, time, status) VALUES (:name, :name_from, :type, :time, :status)");
                                $this->prepare->bindValue(":name", trim(strtolower($this->getServer()->getPlayer($this->tp_reciver)->getName())), SQLITE3_TEXT);
                                $this->prepare->bindValue(":name_from", trim(strtolower($this->tp_sender)), SQLITE3_TEXT);
                                $this->prepare->bindValue(":type", 'tpa', SQLITE3_TEXT);
                                $this->prepare->bindValue(":time", time(), SQLITE3_TEXT);
                                $this->prepare->bindValue(":status", 0, SQLITE3_TEXT);
                                $this->result = $this->prepare->execute();
                                return true;
                            } else {
                                $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_player_not_online"));
                                return true;
                            }
                        } else {
                            $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_invalid_usage"));
                            return false;
                        }

                }
                else
                {
                    $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
                    return true;
                }
                break;
            case 'tpahere':
                if (!$sender->hasPermission("essentialstp.command.tpahere")) {
                    $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_permissions"));
                    return true;
                }
                if ($sender instanceof Player)
                {
                    if((count($args) != 0) && (count($args) < 2))
                    {
                        if(trim(strtolower($sender->getName())) == trim(strtolower($args[0]))){$sender->sendMessage(TextFormat::RED.$this->config->get("Lang_no_teleport_self"));return true;}
                        $this->tp_sender = $sender->getName();
                        $this->tp_reciver = $args[0];
                        if($this->getServer()->getPlayer($this->tp_reciver) instanceof Player)
                        {
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_sent_request_them").' '.TextFormat::GOLD.$this->tp_sender.TextFormat::WHITE);
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_type").' '.TextFormat::GOLD.'/tpaccept'.TextFormat::WHITE.' '.$this->config->get("Lang_accept_request"));
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_type").' '.TextFormat::GOLD.'/tpdeny'.TextFormat::WHITE.' '.$this->config->get("Lang_decline_request"));
                            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_request_expire_1").' '.TextFormat::GOLD.$this->config->get("tpa-here-cooldown").' '.$this->config->get("Lang_request_expire_2").TextFormat::WHITE.' '.$this->config->get("Lang_request_expire_3"));
                            $this->prepare = $this->db2->prepare("INSERT INTO tp_requests (player, player_from, type, time, status) VALUES (:name, :name_from, :type, :time, :status)");
                            $this->prepare->bindValue(":name", trim(strtolower($this->getServer()->getPlayer($this->tp_reciver)->getName())), SQLITE3_TEXT);
                            $this->prepare->bindValue(":name_from", trim(strtolower($this->tp_sender)), SQLITE3_TEXT);
                            $this->prepare->bindValue(":type", 'tpahere', SQLITE3_TEXT);
                            $this->prepare->bindValue(":time", time(), SQLITE3_TEXT);
                            $this->prepare->bindValue(":status", 0, SQLITE3_TEXT);
                            $this->result = $this->prepare->execute();
                            return true;
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_player_not_online"));
                            return true;
                        }
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_invalid_usage"));
                        return false;
                    }
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
                    return true;
                }
                break;
            case 'tpaccept':
                if (!$sender->hasPermission("essentialstp.command.tpaccept")) {
                    $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_permissions"));
                    return true;
                }
                if ($sender instanceof Player)
                {
                    $this->prepare = $this->db2->prepare("SELECT id,player, player_from, type, time, status FROM tp_requests WHERE time > :time AND player = :player AND status = 0");
                    $this->prepare->bindValue(":time", (time() - $this->config->get("tpa-here-cooldown")), SQLITE3_TEXT);
                    $this->prepare->bindValue(":player", trim(strtolower($sender->getName())), SQLITE3_TEXT);
                    $this->result = $this->prepare->execute();
                    $sql          = $this->fetchall();
                    if(count($sql) > 0)
                    {
                       $sql = $sql[0];
                      switch($sql['type'])
                      {
                          case 'tpa':
                              if($this->getServer()->getPlayer($sql['player_from']) instanceof Player)
                              {
                                  $this->getServer()->getPlayer($sql['player_from'])->teleport($sender->getPosition());
                                  $this->prepare = $this->db2->prepare("UPDATE tp_requests SET status = 1 WHERE id = :id");
                                  $this->prepare->bindValue(":id", $sql['id'], SQLITE3_INTEGER);
                                  $this->result = $this->prepare->execute();
                                  return true;
                              }
                              else
                              {
                                  $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_player_not_online"));
                                  return true;
                              }
                              break;
                          case 'tpahere':
                              if($this->getServer()->getPlayer($sql['player_from']) instanceof Player)
                              {
                                  $sender->teleport($this->getServer()->getPlayer($sql['player_from'])->getPosition());
                                  $this->prepare = $this->db2->prepare("UPDATE tp_requests SET status = 1 WHERE id = :id");
                                  $this->prepare->bindValue(":id", $sql['id'], SQLITE3_INTEGER);
                                  $this->result = $this->prepare->execute();
                                  return true;
                              }
                              else
                              {
                                  $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_player_not_online"));
                                  return true;
                              }
                              break;
                          default:
                              return false;
                      }
                    }
                    else
                    {
                        $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_no_active_request"));
                        $this->prepare = $this->db2->prepare("DELETE FROM tp_requests WHERE time < :time AND player = :player AND status = 0");
                        $this->prepare->bindValue(":time", (time() - $this->config->get("tpa-here-cooldown")), SQLITE3_TEXT);
                        $this->prepare->bindValue(":player", trim(strtolower($sender->getName())), SQLITE3_TEXT);
                        $this->result = $this->prepare->execute();
                        return true;
                    }
                }
                 else
                {
                    $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
                    return true;
                }
                break;
            case 'tpdeny':
                if (!$sender->hasPermission("essentialstp.command.tpdeny")) {
                    $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_permissions"));
                    return true;
                }
                if ($sender instanceof Player)
                {
                    $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_no_active_request"));
                    $this->prepare = $this->db2->prepare("DELETE FROM tp_requests WHERE player = :player AND status = 0");
                    $this->prepare->bindValue(":player", trim(strtolower($sender->getName())), SQLITE3_TEXT);
                    $this->result = $this->prepare->execute();
                    return true;
                }
                else
                {
                    $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
                    return true;
                }
                break;
            case 'back':
                if (!$sender->hasPermission("essentialstp.command.back")) {
                    $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_permissions"));
                    return true;
                }
                if ($sender instanceof Player)
                {
                    if(isset($this->death_loc[$sender->getName()]) && $this->death_loc[$sender->getName()] instanceof Position){
                        $sender->teleport($this->death_loc[$sender->getName()]);
                        $sender->sendMessage($this->config->get("Lang_teleport_death"));
                        unset($this->death_loc[$sender->getName()]);
                        return true;
                    }else{
                        $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_no_death"));
                        return true;
                    }
                }
	            else
                {
                    $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
                    return true;
                }
                break;
            default:
                return false;
            }
            return false;
        }

    public function create_db(){
        $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='homes'");
        $this->result = $this->prepare->execute();
        $sql = $this->fetchall();
        $count = count($sql);
        if ($count == 0){
            $this->prepare = $this->db2->prepare("CREATE TABLE homes (
                      id INTEGER PRIMARY KEY,
                      player TEXT,
                      x TEXT,
                      y TEXT,
                      z TEXT,
                      title TEXT,
                      world TEXT)");
            $this->result = $this->prepare->execute();
            $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ Homes database created!");
        }
        $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='tp_requests'");
        $this->result = $this->prepare->execute();
        $sql2 = $this->fetchall();
        $count2 = count($sql2);
        if ($count2 == 0){
            $this->prepare = $this->db2->prepare("CREATE TABLE tp_requests (
                      id INTEGER PRIMARY KEY,
                      player TEXT,
                      player_from TEXT,
                      type TEXT,
                      time TEXT,
                      status TEXT)");
            $this->result = $this->prepare->execute();
            $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ request database created!");
        }
        $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='cooldowns'");
        $this->result = $this->prepare->execute();
        $sql5 = $this->fetchall();
        $count5 = count($sql5);
        if($count5 == 0){
            $this->prepare = $this->db2->prepare("CREATE TABLE cooldowns (
                      id INTEGER PRIMARY KEY,
                      home INTEGER,
                      player TEXT
                      )");
            $this->result = $this->prepare->execute();
            $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ cooldown database created!");
        }

    }

    public function check_config(){
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, array());
        $this->config->set('plugin-name',"essentalsTp+");
        $this->config->save();

        if(!$this->config->get("sqlite-dbname"))
        {
            $this->config->set("sqlite-dbname", "essentials_tp");
            $this->config->save();
        }

        if($this->config->get("tpa-here-cooldown") == false)
        {
            $this->config->set("tpa-here-cooldown", "30");
            $this->config->save();
        }
        if($this->config->get("tp-home-cooldown") == false)
        {
            $this->config->set("tp-home-cooldown", "5");
            $this->config->save();
        }


    }

    public function onEnable(){
        $this->getLogger()->info(TextFormat::GREEN."essentialsTP+ loading...");
        @mkdir($this->getDataFolder());
        $this->check_config();
        try{
            if(!file_exists($this->getDataFolder().$this->config->get("sqlite-dbname").'.db')){
                $this->db2 = new \SQLite3($this->getDataFolder().$this->config->get("sqlite-dbname").'.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            }else{
                $this->db2 = new \SQLite3($this->getDataFolder().$this->config->get("sqlite-dbname").'.db', SQLITE3_OPEN_READWRITE);
            }
        }
        catch (\Throwable $e)
        {
            $this->getLogger()->critical($e->getMessage());
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        $this->create_db();
        $this->getLogger()->info(TextFormat::GREEN."[INFO] loading [".TextFormat::GOLD."config.yml".TextFormat::GREEN."]....");
        $this->tpa_cooldown = time() - $this->config->get("tpa-here-cooldown");
        $this->getLogger()->info(TextFormat::GREEN."[INFO] loading [".TextFormat::GOLD."config.yml".TextFormat::GREEN."] DONE");
        $this->getLogger()->info(TextFormat::GREEN."essentialsTP+ loaded!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDisable(){
        if($this->prepare){
            $this->prepare->close();
        }
        $this->getLogger()->info("essentialsTP+ Disabled");
    }
}
