<?php
namespace xBeastMode\Weapons;
use pocketmine\command\{Command, CommandSender, CommandExecutor, ConsoleCommandSender};
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
class WeaponCommand extends Command implements PluginIdentifiableCommand{
        /** @var Weapons */
        protected $core;

        public function __construct(Weapons $c){
                $this->core = $c;

                $this->setPermission("command.weapon");
                parent::__construct("weapon", "Danh Sách Lệnh Của Súng", "§l§c● §eSử dụng: §b/weapon", []);
        }

        /**
         * @param CommandSender $sender
         * @param string        $commandLabel
         * @param string[]      $args
         *
         * @return mixed
         */
        public function execute(CommandSender $sender, string $commandLabel, array $args){
                if(!$this->testPermission($sender)){
                        return false;
                }

                /** @var Player $sender */

                if(count($args) <= 0){
                        $sender->sendMessage(RandomUtils::colorMessage("§l§c--=[§dWeapon§c]=--"));
                        $sender->sendMessage(RandomUtils::colorMessage("§l§e/weapon guns : §adanh sách súng"));
                        $sender->sendMessage(RandomUtils::colorMessage("§l§e/weapon gun <gun> [player] : §ađưa súng cho người chơi"));
                        $sender->sendMessage(RandomUtils::colorMessage("§l§e/weapon ammo <amount> [player] : §ađưa đạn cho người chơi"));
                        return true;
                }

                if(strtolower($args[0]) === "guns"){
                        $sender->sendMessage(RandomUtils::colorMessage("§l§c● §eDanh sách súng: §b" . implode(", ", GunData::GUN_LIST)));
                        return true;
                }elseif(strtolower($args[0]) === "gun"){
                        if(count($args) < 2){
                                $sender->sendMessage(RandomUtils::colorMessage("§l§c● §eSử dụng: §b/weapon gun <gun> [player]"));
                                return false;
                        }

                        $gun = strtolower($args[1]);
						

                        if(!in_array($gun, GunData::GUN_LIST)){
                                $sender->sendMessage(TextFormat::RED . "§l§c●§d $args[1] §ekhông phải là tên súng.");
                                return false;
                        }
                        $player = isset($args[2]) ? $sender->getServer()->getPlayer($args[2]) : $sender;

						if(!$player instanceof Player) //check if the target is a Player / Online
                        {
                            $sender->sendMessage(TextFormat::RED . "§l§c●§e Người chơi đang không trực tuyến.");
				            return true;
                        }

                        $item = Item::get(Item::HORSE_ARMOR_IRON);
                        $item->setCustomName(RandomUtils::colorMessage("§l§b{$gun} §6[Chuột Phải§6]"));
                        $item->setCustomBlockData(new CompoundTag("", [new StringTag("gunType", $gun)]));

                        $player->getInventory()->addItem($item);

                        return true;
                }elseif(strtolower($args[0]) === "ammo"){
                        if(count($args) < 2){
                                $sender->sendMessage(RandomUtils::colorMessage("§l§c● §eSử dụng: §b/weapon ammo <amount> [player]"));
                                return false;
                        }

                        $amount = strtolower($args[1]);
                        $player = isset($args[2]) ? $sender->getServer()->getPlayer($args[2]) : $sender;
						
						if(!isset($amount) || !is_numeric($amount)){
				            $sender->sendMessage("§l§c● §ePhải là số.");
			                return false;
			            }

                        if(!$player instanceof Player) //check if the target is a Player / Online
                        {
                            $sender->sendMessage(TextFormat::RED . "§l§c●§e Người chơi đang không trực tuyến.");
				            return true;
                        }
						
                        $item = Item::get(Item::FIREBALL);
                        $item->setCustomName(RandomUtils::colorMessage("§l§dĐạn"));
                        $item->setCustomBlockData(new CompoundTag("", [new IntTag("ammoAmount", $amount)]));

                        $player->getInventory()->addItem($item);
                        return true;
                }

                return true;
        }

        /**
         * @return Plugin
         */
        public function getPlugin(): Plugin{
                return $this->core;
        }
}