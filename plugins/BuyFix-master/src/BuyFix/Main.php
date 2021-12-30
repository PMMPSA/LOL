<?php
namespace BuyFix;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use onebone\economyapi\EconomyAPI;
use pocketmine\inventory\PlayerInventory;

use pocketmine\block\Block;
use pocketmine\event\Event;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\{PlayerInteractEvent, PlayerJoinEvent};
use pocketmine\event\entity\EntityLevelChangeEvent;

use pocketmine\level\Level;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\item\Armor;

class Main extends PluginBase implements Listener{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		Item::removeCreativeItem(Item::get(373, 0));
    	$this->getLogger()->info("BuyFix enable");
    	$this->saveResource("config.yml");  				
    }
	
	
	public function onJoin(PlayerJoinEvent $ev){
		if($ev->getPlayer()->isCreative()){
			$ev->getPlayer()->setGamemode(Player::SURVIVAL);
			$ev->getPlayer()->getInventory()->clearAll();
			$ev->getPlayer()->getInventory()->setHeldItemIndex(0);
		}
	}
	
	public function onGameModeChange(PlayerGameModeChangeEvent $event){
        $player = $event->getPlayer();
        $newGM = $event->getNewGamemode();
        if ($newGM === 0){
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            return;
        }
    }
	
    public function onTap(PlayerInteractEvent $event){
     $id = $event->getBlock()->getId();
     $player = $event->getPlayer();
		if(in_array($id, array(54, 130, 205, 125, 410, 23, 61, 146, 116, 218, 199, 389, 410, 379, 145, 154)) and !$player->isSurvival()){
			  $event->setCancelled(true);
			  $player->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn chỉ có thể mở vật phẩm này ở chế độ sinh tồn ");
		}
		if(in_array($id, array(199, 389)) and !$player->isSurvival()){
			  $event->setCancelled(true);
			  $player->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn không được mở vật phẩm này!");
		}
	}
    
    public function onCommand(CommandSender $sender, Command $cmd, String $label, array $args) : bool {
		if($cmd->getName() == "sangtao"){
			if($sender->hasPermission('sangtao.sudung')){
				if(!$sender->isCreative()){
					$sender->setGamemode(Player::CREATIVE);
					$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn đã được chuyển sang chế độ sáng tạo");
					return true;
				} 
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn đang ở chế độ sáng tạo");
				return true;
			}
			$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn phải có rank §cMa§bst§aer để sử dụng lệnh này");
			return true;
		}
		if($cmd->getName() == "sinhton"){
			if($sender->hasPermission('sinhton.sudung')){
				if(!$sender->isSurvival()){
					$sender->setGamemode(Player::SURVIVAL);
					$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn đã được chuyển sang chế độ sinh tồn");
					return true;
				} 
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn đang ở chế độ sinh tồn");
				return true;
			}
			$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn phải có rank §cMa§bst§aer để sử dụng lệnh này");
			return true;
		}
		
        if($cmd->getName() == "suado"){
          if(!$sender instanceof Player){
            $sender->sendMessage("Please use command in game!");
            return true;
          }
          $economy = EconomyAPI::getInstance();
          $mymoney = $economy->myMoney($sender);
          $cash = $this->getConfig()->get("price-buyfix");
          if($mymoney >= $cash){
            $economy->reduceMoney($sender, $cash);
            $item = $sender->getInventory()->getItemInHand();
				      if($item instanceof Armor or $item instanceof Tool){
				        $id = $item->getId();
					      $meta = $item->getDamage();
					      $item->setDamage(0);
						  $sender->getInventory()->setItemInHand($item);
					      $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §a" . $item->getName() . " Đã được sửa!");
					      return true;
					    } else {
				        	$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eHãy để vật phẩm hoặc giáp trên tay của bạn!");
					        return false;
					    }
            return true;
          } else {
            $sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6] §eBạn không có đủ $cash xu để sửa đồ.");
            return true;
		  }
		}
	}
}