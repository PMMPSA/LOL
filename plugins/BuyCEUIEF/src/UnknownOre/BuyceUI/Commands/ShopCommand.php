<?php
namespace UnknownOre\BuyceUI\Commands;

use pocketmine\command\{
    Command,
    PluginCommand,
    CommandSender
};
use pocketmine\Player;
use UnknownOre\BuyceUI\Main;

class ShopCommand extends PluginCommand {
    
    /**
     * ShopCommand constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin) {
        parent::__construct('muace', $plugin);
        $this->setAliases(['buyce','buyceui']);
        $this->setDescription('Hệ Thống Mua CustomEnchant');
        $this->setPermission("eshop.command");
        $this->plugin = $plugin;
    }
    
   /**
    * @param CommandSender $sender
    * @param string $commandLabel
    * @param array $args
    *
    * @return bool
    */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if(!$sender->hasPermission("eshop.command")){
            $sender->sendMessage($this->plugin->shop->getNested('messages.no-perm'));
            return true;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage("Please use this in-game.");
            return true;
        }   
        $this->plugin->listForm($sender);
        return true;
	}
   
}
