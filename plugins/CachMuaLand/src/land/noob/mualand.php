<?php
declare(strict_types=1);
namespace land\noob;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use jojoe77777\FormAPI;
class mualand extends PluginBase implements Listener{

public function onEnable(){
$this->getLogger()->info("§a CachMuaLand Enable");
$this->getServer()->getPluginManager()->registerEvents($this, $this);
$this->getServer()->getPluginManager()->getPlugin("FormAPI");
}
  
public function onCommand(CommandSender $s, Command $cmd, string $label, array $args):bool {
if ($cmd->getName() == "huongdan"){
    $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
   $form = $api->createCustomForm(function (Player $s, $data){

 });
  $form->setTitle("§l§6♦§a Hướng Dẫn §6♦");
  $form->addLabel("§l§c●§a /detu :§e mua một đệ tử mine hộ giúp bạn!");
  $form->addLabel("§l§c●§a /eternity :§e cho phép người chơi đập block vĩnh viễn trong vòng 15 phút");
  $form->addLabel("§l§c●§a /buypet :§e mua một con thú cưng riêng cho bạn!");
  $form->addLabel("§l§c●§a /menu :§e mở giao diện của server");
  $form->addLabel("§l§c●§a /ah :§e đấu giá của server");
  $form->addLabel("§l§c●§a /warpui :§e truy cập các khu vực của server");
  $form->addLabel("§l§c●§a /shop :§e mở cửa hàng chính của server");
  $form->addLabel("§l§c●§a /sell all :§e bán đồ trong server");
  $form->addLabel("§l§c●§a /sbui :§e mở giao diện skyblock ui của server");
  $form->addLabel("§l§c●§a /clan help :§e mở giao diện clan của server");
  $form->addLabel("§l§c●§a /nganhang :§e gửi tiền vô ngân hàng");
  $form->addLabel("§l§c●§a /detu :§e tính năng đệ tử mine hộ của server");
  $form->addLabel("§l§c●§a /buyec :§e mua enchant cho vật phẩm của bạn");
  $form->addLabel("§l§c●§a /buyce :§e mua customenchant cho vật phẩm của bạn");
  $form->addLabel("§l§c●§a /muapoint :§e mua point bằng xu");
  $form->addLabel("§l§c●§a /muarank :§e mua tất cả rank vip trong server bằng point");
  $form->addLabel("§l§c●§a /kit :§e lựa chọn kit cho rank của bạn");
  $form->addLabel("§l§c●§a /muado :§e mua các vật phẩm vip tại đây");
  $form->addLabel("§l§c●§a /choden :§e mua các tính năng hiếm hoặc key crate tại đây");
  $form->addLabel("§l§c●§a /muadanhhieu :§e mua các danh hiệu cực ngầu tại đây (/danhhieu)");
  $form->addLabel("§l§c●§a /chuyensinh :§e chuyển sinh xong khi mine sẽ được xu và tăng tim vĩnh viễn");
  $form->addLabel("§l§c●§a /napthe :§e tại nạp thẻ bạn có thể mua được coupon và các vật phẩm chỉ nạp thẻ mới có"); 
  $form->sendToPlayer($s); 
}
return true;
}
}