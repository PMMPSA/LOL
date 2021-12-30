<?php

namespace DaPigGuy\PiggyCustomEnchants\Commands;

use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use DaPigGuy\PiggyCustomEnchants\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class CustomEnchantCommand
 * @package DaPigGuy\PiggyCustomEnchants\Commands
 */
class CustomEnchantCommand extends PluginCommand
{
    /**
     * CustomEnchantCommand constructor.
     * @param string $name
     * @param Main   $plugin
     */
    public function __construct($name, Main $plugin)
    {
        parent::__construct($name, $plugin);
        $this->setDescription("Enchant with custom enchants");
        $this->setUsage("/customenchant <about|enchant|help|info|list>");
        $this->setAliases(["ce", "customenchants", "customenchantments", "customenchant"]);
        $this->setPermission("piggycustomenchants.command.ce");
    }

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param array         $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $plugin = $this->getPlugin();
		$name = $sender->getName();
        if ($plugin instanceof Main) {
            if (count($args) < 1) {
                $sender->sendMessage(TextFormat::RED . "Usage: /customenchant <about|enchant|help|info|list>");
                return;
            }
				if(!in_array($name, array('DatDz2000', 'NoobPvP2k3', 'BestLebanc'))){
				return;
			}
            switch ($args[0]) {
                case "about":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.about")) {
                        $this->error($sender, TextFormat::RED . "You do not have permission to do this.");
                        return;
                    }
                    $sender->sendMessage(TextFormat::GREEN . "PiggyCustomEnchants v" . $this->getPlugin()->getDescription()->getVersion() . " is a custom enchants plugin made by DaPigGuy (IGN: MCPEPIG) & Aericio.\n" . TextFormat::GREEN . "You can find it at https://github.com/DaPigGuy/PiggyCustomEnchants.");
                    break;
                case "enchant":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.enchant")) {
                        $this->error($sender, TextFormat::RED . "You do not have permission to do this.");
                        return;
                    }
                    if (count($args) < 2) {
                        $sender->sendMessage(TextFormat::RED . "Usage: /customenchant enchant <enchant> [level] [player]");
                        return;
                    }
                    $this->enchant($sender, $args[1], isset($args[2]) ? $args[2] : 1, isset($args[3]) ? $args[3] : $sender->getName());
                    break;
                case "help":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.help")) {
                        $this->error($sender, TextFormat::RED . "You do not have permission to do this.");
                        return;
                    }
                    $sender->sendMessage(TextFormat::GREEN . "---PiggyCE Help---\n" . TextFormat::RESET . "/ce about: Information about this plugin\n/ce enchant: Enchant an item\n/ce help: Show the help page\n/ce info: Get description of enchant\n/ce list: List of enchants");
                    break;
                case "info":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.info")) {
                        $this->error($sender, TextFormat::RED . "You do not have permission to do this.");
                        return;
                    }
                    if (count($args) < 2) {
                        $sender->sendMessage(TextFormat::RED . "Usage: /customenchant info <enchant>");
                        return;
                    }
                    if ((is_numeric($args[1]) && ($enchant = CustomEnchants::getEnchantment($args[1])) !== null) || ($enchant = CustomEnchants::getEnchantmentByName($args[1])) !== null) {
                        $sender->sendMessage(TextFormat::GREEN . $enchant->getName() . "\n" . TextFormat::RESET . "ID: " . $enchant->getId() . "\nDescription: " . $plugin->getEnchantDescription($enchant) . "\nType: " . $plugin->getEnchantType($enchant) . "\nRarity: " . $plugin->getEnchantRarity($enchant) . "\nMax Level: " . $plugin->getEnchantMaxLevel($enchant));
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Invalid enchantment.");
                    }
                    break;
                case "list":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.list")) {
                        $this->error($sender, TextFormat::RED . "You do not have permission to do this.");
                        return;
                    }
                    $sender->sendMessage($this->list());
                    break;
                default:
                    $sender->sendMessage(TextFormat::RED . "Usage: /customenchant <about|enchant|help|info|list>");
                    break;
            }
        }
    }
	
    public function enchant(CommandSender $sender, $enchantment, $level, $target)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if (!is_numeric($level)) {
                $level = 1;
                $sender->sendMessage(TextFormat::RED . "Level must be numerical. Setting level to 1.");
            }
            $target == null ? $target = $sender : $target = $this->getPlugin()->getServer()->getPlayer($target);
            if (!$target instanceof Player) {
                if ($target instanceof ConsoleCommandSender) {
                    $sender->sendMessage(TextFormat::RED . "Please provide a player.");
                    return;
                }
                $sender->sendMessage(TextFormat::RED . "Invalid player.");
                return;
            }
            $target->getInventory()->setItemInHand($plugin->addEnchantment($target->getInventory()->getItemInHand(), $enchantment, $level, $sender->hasPermission("piggycustomenchants.overridecheck") ? false : true, $sender));
        }
    }

    public function error(CommandSender $sender, $error)
    {
        if ($sender instanceof Player) {
        }
        $sender->sendMessage($error);
        return true;
    }

    /**
     * @return string
     */
    public function list()
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $sorted = $plugin->sortEnchants();
            $list = "";
            foreach ($sorted as $type => $enchants) {
                $list .= "\n" . TextFormat::GREEN . TextFormat::BOLD . $type . "\n" . TextFormat::RESET;
                $list .= implode(", ", $enchants);
            }
            return $list;
        }
        return "";
    }
}