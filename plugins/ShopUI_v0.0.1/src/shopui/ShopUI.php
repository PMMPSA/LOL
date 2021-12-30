<?php

namespace shopui;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use shopui\command\ShopCommand;
use shopui\util\ShopProvider;
class ShopUI extends PluginBase
{
    private static $instance;

    public function onLoad()
    {
        self::$instance = $this;
    }

    /**
     * @return ShopUI
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    public function onEnable()
    {
        $this->saveDefaultConfig();
        $this->saveResource("language.yml");

        ShopProvider::init($this->getConfig()->get("database"));
        $this->getServer()->getCommandMap()->register("shopui", new ShopCommand());
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
    }

    public static function _(string $translateKey)
    {
        $config = new Config(self::getInstance()->getDataFolder() . "language.yml", Config::YAML);
        return $config->get($translateKey, "NOT_FOUND");
    }
}
