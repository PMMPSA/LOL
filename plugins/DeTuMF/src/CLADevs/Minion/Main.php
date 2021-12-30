<?php

namespace CLADevs\Minion;

use CLADevs\Minion\upgrades\EventListener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

	private static $instance;
	
	public $user;
	public function onLoad(): void{
		self::$instance = $this;
	}

	public function onEnable(): void{
		Entity::registerEntity(Minion::class, true);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$this->eco = $this->getServer()->getPluginManager()->getPlugin("PointAPI");
		$this->user = new Config($this->getDataFolder() ."user.yml", Config::YAML, [
		]);				
	}
	
	public static function get(): self{
		return self::$instance;
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
	    if($sender instanceof Player){
            //to remove cmd remove this below than go to plugin.yml remove commands section
            if($command->getName() === "detu"){
				if(!($sender instanceof Player)){
					$sender->sendMessage("Hãy sử dụng Command trong Game");
					return true;
				}
				$this->mainForm($sender);                
            }
        }
        return true;
    }
	
	
	private function mainForm(Player $player){
		$form = $this->formapi->createSimpleForm(function (Player $sender, $data){
			$result = $data;			
			if ($result == null) {
				return false;
			}
			switch ($result) {

				case 0:
				
				break;	
				
				case 1:
					$this->muaDT($sender);
				break;		
			
			}
		});
		$form->setTitle("§l§6♦ §cĐệ Tử§6 ♦");
		$form->setContent("§l§3● §aSố Point của bạn: §e". $this->eco->myMoney($player));
		$form->addButton("§l§3● §cThoát §3●", 1, 'http://minefore.tk/png/exit.png');
		$form->addButton("§l§3● §9Mua đệ tử §e1.500 Point §3●", 1, 'http://minefore.tk/png/miner.png');		
		$form->sendToPlayer($player);
	}

	private function muaDT(Player $player){
		$form = $this->formapi->createSimpleForm(function (Player $sender,$data){
			if($data === NULL) return false;
			
			switch($data){
				case 0:
					if($this->eco->myMoney($sender) >= 1500){
						$this->msgForm($sender, $this->muaDTSucess($sender, 1500),"§l§3●§e Bạn có chắc muốn mua §dĐệ tử §ebằng §c1500 Point§e không ?");
						return false;
					}
					$this->msgForm($sender, false,"§l§3●§e Bạn không đủ Point");
					return false;
				break;
			}
		});
		$form->setTitle("§l§6♦ §cĐệ Tử§6 ♦");
		$form->setContent("§l§3● §cKhi mua đệ tử bạn sẽ nhận được 1 vật phẩm đặc biệt để triệu hồi đệ tử, đệ tử sẽ tự động mine và bỏ đồ vô rương giúp bạn,\nkhi rương đầy đệ tử sẽ tự động bán và chuyển\nPoint cho chủ!\n§3● §6Lưu ý Mỗi Người Chỉ Triệu Hồi Tối Đa Được\n1 Đệ Tử.");
		$form->addButton("§l§e● §9Mua Đệ Tử §e●");
		$form->sendToPlayer($player);	
	}
	
	private function muaDTSucess(Player $player, $eco){
		$this->eco->reduceMoney($player, $eco);
		$player->getInventory()->addItem($this->getItem($player->getName()));
		$this->msgForm($player,$this->mainForm($player) ,"§l§3●§e Mua §cĐệ Tử§b §l§athành công!.\n§l§3●§e Bạn có muốn quay về trang chủ không ?");
	}
	
	private function msgForm(Player $player, $nextform = false, $msg){
		if($nextform !== false){
			$form = $this->formapi->createSimpleForm(function (Player $p, $data) {
				if($data === NULL) return false;	
					var_dump($data);
					switch($data){
						case 0:
							$nextform;						
						break;
						
						case 1:

						break;
					}
			
			});
		    $form->setTitle("§l§6♦ §cĐệ Tử§6 ♦");
			$form->setContent($msg);
			$form->addButton("§l§3● §2Tiếp tục §3●");
			$form->addButton("§l§3● §cThoát §3●");		
			$form->sendToPlayer($player);	
			return true;
		}
		
		
        $form = $this->formapi->createSimpleForm(function (Player $p, $data) {

			if($data === NULL) return false;	
			
			switch($data){
				case 0:
				
				break;
				
				case 1:

				break;
				
			}
		
		});
		$form->setTitle("§l§6♦ §cĐệ Tử§6 ♦");
        $form->setContent($msg);
		$form->addButton("§l§3● §4Thoát §3●");		
        $form->sendToPlayer($player);			
	}	
	
    public function getItem(string $player,  string $xyz = "n"): Item{
	    $item = Item::get(Item::MAGMA_CREAM);
	    $item->setCustomName(C::GREEN . "§l§aTriệu Hồi " . C::GOLD . "§c§cĐệ Tử");
	    $item->setLore([C::GRAY . "§l§3●§6 Tự động mine và bán đồ giúp bạn!"]);
	    $nbt = $item->getNamedTag();
	    $nbt->setString("TrieuHoiMF", "DeTuMF");
		$nbt->setString("xyz", $xyz);
		$nbt->setInt("Time", 3);
		$nbt->setString("Owner", $player);
	    $item->setNamedTag($nbt);
	    return $item;
    }
}
