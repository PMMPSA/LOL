<?php
declare(strict_types = 1);

/**
 *     _____                    _   _           _
 *    /  ___|                  | | | |         | |
 *    \ `--.  ___ ___  _ __ ___| |_| |_   _  __| |
 *     `--. \/ __/ _ \| '__/ _ \  _  | | | |/ _` |
 *    /\__/ / (_| (_) | | |  __/ | | | |_| | (_| |
 *    \____/ \___\___/|_|  \___\_| |_/\__,_|\__,_|
 *
 * ScoreHud, a Scoreboard plugin for PocketMine-MP
 * Copyright (c) 2018 JackMD  < https://github.com/JackMD >
 *
 * Discord: JackMD#3717
 * Twitter: JackMTaylor_
 *
 * This software is distributed under "GNU General Public License v3.0".
 * This license allows you to use it and/or modify it but you are not at
 * all allowed to sell this plugin at any cost. If found doing so the
 * necessary action required would be taken.
 *
 * ScoreHud is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License v3.0 for more details.
 *
 * You should have received a copy of the GNU General Public License v3.0
 * along with this program. If not, see
 * <https://opensource.org/licenses/GPL-3.0>.
 * ------------------------------------------------------------------------
 */

namespace JackMD\ScoreHud;

use JackMD\ScoreHud\libs\JackMD\ScoreFactory\ScoreFactory;
//use JackMD\ScoreHud\data\DataManager;
use JackMD\ScoreHud\task\ScoreUpdateTask;
use JackMD\ScoreHud\libs\JackMD\UpdateNotifier\UpdateNotifier;
use pocketmine\Player;
use _64FF00\PurePerms\PurePerms;
use FactionsPro\FactionMain;
use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use ShinPickaxeLevel\Main as ShinPickaxeLevel;
use instantlyta\fcaclan\FCAClan;
use DOLNapTheUI\Main as CP;
class Main extends PluginBase{
	
	/** @var string */
	private const CONFIG_VERSION = 4;
	
	/** @var DataManager */
	private $dataManager;
	
	public function onLoad(){
//		$this->checkVirions();
		$this->saveDefaultConfig();
		$this->checkConfig();		
		//UpdateNotifier::checkUpdate($this, $this->getDescription()->getName(), $this->getDescription()->getVersion());
	}
	
	/**
	 * Checks if the required virions/libraries are present before enabling the plugin.
	 */
//	private function checkVirions(): void{
//		if(!class_exists(ScoreFactory::class) || !class_exists(UpdateNotifier::class)){
//			throw new \RuntimeException("ScoreHud plugin will only work if you use the plugin phar from Poggit.");
//		}
//	}
	
	/**
	 * Check if the config is up-to-date.
	 */
	public function checkConfig(): void{
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		if((!$config->exists("config-version")) || ($config->get("config-version") !== self::CONFIG_VERSION)){
			rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config_old.yml");
			$this->saveResource("config.yml");
			$this->getLogger()->critical("Your configuration file is outdated.");
			$this->getLogger()->notice("Your old configuration has been saved as config_old.yml and a new configuration file has been generated.");
			return;
		}
	}
	
	public function onEnable(): void{
		//$this->dataManager = new DataManager($this);
		
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->pick = $this->getServer()->getPluginManager()->getPlugin("ShinPickaxeLevel");
	    $this->clan = $this->getServer()->getPluginManager()->getPlugin("GCPClan");
		$this->getScheduler()->scheduleRepeatingTask(new ScoreUpdateTask($this), (int) $this->getConfig()->get("update-interval") * 20);
		$this->getLogger()->info("ScoreHud Plugin Enabled.");
	}
	
	/**
	 * @param $timezone
	 * @return mixed
	 */
	
	/**
	 * @param Player $player
	 * @param string $title
	 */
	 public function getPlayerPoint(Player $player){
		/** @var PointAPI $pointAP */
		$pointAPI = $this->getServer()->getPluginManager()->getPlugin("PointAPI");
		if($pointAPI !== null){
			return $pointAPI->myMoney($player);
		}else{
			return "Plugin not found";
		}
	}
	
	public function getPlayerMoney(Player $player){
		/** @var EconomyAPI $economyAPI */
		$economyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		if($economyAPI instanceof EconomyAPI){
			return $economyAPI->myMoney($player);
		}else{
			return "Plugin not found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return string
	 */
	public function getPlayerRank(Player $player): string{
		/** @var PurePerms $purePerms */
		$purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
		if($purePerms instanceof PurePerms){
			$group = $purePerms->getUserDataMgr()->getData($player)['group'];
			if($group !== null){
				return $group;
			}else{
				return "No Rank";
			}
		}else{
			return "Plugin not found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return bool|int|string
	 */
	
	/**
	 * @param Player $player
	 * @return string
	 */
	public function getPlayerClan(Player $player): string{
		$fcaClan = $this->getServer()->getPluginManager()->getPlugin("GCPClan");
		if($fcaClan instanceof FCAClan){
			if ($fcaClan->haveClan($player)) {
				$Clantag = $fcaClan->getClanTag($player);
			}else{
				$Clantag = "Không Có Clan";
			}
			
			return $Clantag;
			
		}
		return "Plugin not found";
	}
	
	public function addScore(Player $player, string $title): void{
		if(!$player->isOnline()){
			return;
		}
		ScoreFactory::setScore($player, $title);
		$this->updateScore($player);
	}
	
	/**
	 * @param Player $player
	 */
	public function updateScore(Player $player): void{
		$i = 0;
		$lines = $this->getConfig()->get("score-lines");
		if((is_null($lines)) || empty($lines) || !isset($lines)){
			$this->getLogger()->error("Please set score-lines in config.yml properly.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		foreach($lines as $line){
			$i++;
			if($i <= 15){
				ScoreFactory::setScoreLine($player, $i, $this->process($player, $line));
			}
		}
	}
	
	/**
	 * @param Player $player
	 * @param string $string
	 * @return string
	 */
	public function process(Player $player, string $string): string{
		$string = str_replace("{name}", $player->getName(), $string);
		//var_dump($this->pick);
		$string = str_replace("{cs}", $this->pick->getRebirth($player) == false ? '' : $this->pick->getReBirth($player), $string);
		$string = str_replace("{money}", $this->getPlayerMoney($player), $string);
		$string = str_replace("{point}", $this->getPlayerPoint($player), $string);
		$string = str_replace("{online}", count($this->getServer()->getOnlinePlayers()), $string);
		$string = str_replace("{max_online}", $this->getServer()->getMaxPlayers(), $string);
		$string = str_replace("{item_id}", $player->getInventory()->getItemInHand()->getId(), $string);
		$string = str_replace("{clantag}", $this->getPlayerClan($player), $string);

		return $string;
	}
}