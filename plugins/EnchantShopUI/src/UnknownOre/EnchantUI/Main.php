<?php

namespace UnknownOre\EnchantUI;

use pocketmine\{
    Server,
    Player
};
use pocketmine\item\{
    Item,
    Tool,
    Armor,
    enchantment\Enchantment,
    enchantment\EnchantmentInstance
};
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use onebone\economyapi\EconomyAPI;
use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;

class Main extends PluginBase{
    
    public function onEnable(): void{
        @mkdir($this->getDataFolder());
		$this->economyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        $this->getLogger()->notice("EnchantShopUI has been enabled.");
        $this->shop = new Config($this->getDataFolder() . "Shop.yml", Config::YAML);
        if(is_null($this->shop->getNested('version'))){
            file_put_contents($this->getDataFolder() . "Shop.yml",$this->getResource("Shop.yml"));
            $this->getLogger()->notice("Updating Plugin Config.....");
        }
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register("enchantui", new Commands\ShopCommand($this));
        $this->piggyCE = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
		$this->formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
    }
    
	/**
    * @param Player $player
    */
    public function listForm(Player $player): void{
        $form = $this->formapi->createSimpleForm(function (Player $player, $data = null){
            if ($data === null){
                return;
            }
            $this->buyForm($player, $data);
        });
		foreach ($this->shop->getNested('shop') as $name){
            $var = array(
            "NAME" => $name['name'],
            //"PRICE" => $name['price']
            );
			$form->addButton($this->replace($this->shop->getNested('Button'), $var));
		}
		$eco = $this->economyAPI->myMoney($player);
        $form->setTitle($this->shop->getNested('Title'));
		$form->setContent("§l§3●§e Tiền của bạn:§a ".$eco." Xu");
       $form->sendToPlayer($player);
    }
    
	/**
    * @param Player $player
    * @param int $id
    */
    public function buyForm(Player $player,int $id): void{
        $array = $this->shop->getNested('shop');
        $form = $this->formapi->createCustomForm(function (Player $player, $data = null) use ($array, $id){
            $var = array(
            "NAME" => $array[$id]['name'],
            "PRICE" => $array[$id]['price'] * $data[1],
            "LEVEL" => $data[1],
            "MONEY" => $this->economyAPI->myMoney($player)
            );
            if ($data === null){
                $this->listForm($player);
                return;
            }
            if(!$player->getInventory()->getItemInHand() instanceof Tool and !$player->getInventory()->getItemInHand() instanceof Armor){
                $player->sendMessage($this->shop->getNested('messages.hold-item'));
                return;
            }
            if($this->economyAPI->myMoney($player) >= $c = $array[$id]['price'] * $data[1]){
                $this->economyAPI->reduceMoney($player, $c);
                $this->enchantItem($player, $data[1], $array[$id]['enchantment']); 
                $player->sendMessage($this->replace($this->shop->getNested('messages.paid-success'), $var));
            }else{
                $player->sendMessage($this->replace($this->shop->getNested('messages.not-enough-money'), $var));
            }
        }
	);
        $form->addLabel($this->replace($this->shop->getNested('messages.label'),["PRICE" => $array[$id]['price']]));
        $form->setTitle($this->shop->getNested('Title'));
        $form->addSlider($this->shop->getNested('slider-title'), 1, $array[$id]['max-level'], 1, -1);
        $form->sendToPlayer($player);
    }
    
    /**
    * @param Player $Item
    * @param int $level
    * @param int|String $enchantment
    */
	public function enchantItem(Player $player, int $level, $enchantment): void{
        $item = $player->getInventory()->getItemInHand();
        if(is_string($enchantment)){
            $ench = Enchantment::getEnchantmentByName((string) $enchantment);
            if($this->piggyCE !== null && $ench === null){
                $ench = CustomEnchants::getEnchantmentByName((string) $enchantment);
            }
            if($this->piggyCE !== null && $ench instanceof CustomEnchants){
                $this->piggyCE->addEnchantment($item, $ench->getName(), (int) $level);
            }else{
                $item->addEnchantment(new EnchantmentInstance($ench, (int) $level));
            }
        }
        if(is_int($enchantment)){
            $ench = Enchantment::getEnchantment($enchantment);
            $item->addEnchantment(new EnchantmentInstance($ench, (int) $level));
        }
        $player->getInventory()->setItemInHand($item);
    }
    
    /**
    * @param string $message
    * @param array $keys
    *
    * @return string
    */
    public function replace($message, array $keys){
        foreach($keys as $word => $value){
            $message = str_replace("{".$word."}", $value, $message);
        }
        return $message;
    }
}