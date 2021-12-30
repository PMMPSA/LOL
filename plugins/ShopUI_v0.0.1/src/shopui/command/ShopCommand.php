<?php


namespace shopui\command;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use shopui\form\MainShopForm;

class ShopCommand extends Command
{
    public function __construct()
    {
        parent::__construct("shop", "Shop", "/shop");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
		if(!$sender->isSurvival()){
			$sender->sendMessage("§l§6[§bSky§aBlock(§aʟ§cᴏ§aʟ)§6] §eBạn chỉ có thể xài shop ở chế độ sinh tồn!");
			return false;
		}
        if (!($sender instanceof Player)) {
            $sender->sendMessage("§cPlease run this command ingame.");
            return true;
        }
        $sender->sendForm(new MainShopForm($sender));
        return true;
    }
}