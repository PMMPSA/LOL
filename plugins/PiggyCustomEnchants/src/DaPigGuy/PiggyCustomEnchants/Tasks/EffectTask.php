<?php

namespace DaPigGuy\PiggyCustomEnchants\Tasks;

use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use DaPigGuy\PiggyCustomEnchants\Main;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\scheduler\Task;

/**
 * Class EffectTask
 * @package DaPigGuy\PiggyCustomEnchants\Tasks
 */
class EffectTask extends Task
{
    /** @var Main */
    private $plugin;

    /**
     * EffectTask constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::HASTE);
            if ($enchantment !== null) {
                $effect = new EffectInstance(Effect::getEffect(Effect::HASTE), 10, $enchantment->getLevel() - 1, false);
                $player->addEffect($effect);
            }
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::OXYGENATE);
            if ($enchantment !== null) {
                $effect = new EffectInstance(Effect::getEffect(Effect::WATER_BREATHING), 10, 0, false);
                $player->addEffect($effect);
            }
            $enchantment = $player->getArmorInventory()->getHelmet()->getEnchantment(CustomEnchantsIds::GLOWING);
            if ($enchantment !== null) {
                $effect = new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), 220, 0, false);
                $player->addEffect($effect);
                $this->plugin->glowing[$player->getLowerCaseName()] = true;
            } else {
                if (isset($this->plugin->glowing[$player->getLowerCaseName()])) {
                    $player->removeEffect(Effect::NIGHT_VISION);
                    unset($this->plugin->glowing[$player->getLowerCaseName()]);
                }
            }
            $enchantment = $player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantsIds::ENRAGED);
            if ($enchantment !== null) {
                $effect = new EffectInstance(Effect::getEffect(Effect::STRENGTH), 10, $enchantment->getLevel() - 1, false);
                $player->addEffect($effect);
            }
            $enchantment = $player->getArmorInventory()->getBoots()->getEnchantment(CustomEnchantsIds::GEARS);
            if ($enchantment !== null) {
                $effect = new EffectInstance(Effect::getEffect(Effect::SPEED), 10, 0, false);
                $player->addEffect($effect);
            }
            //$shielded = 0;
            foreach ($player->getArmorInventory()->getContents(true) as $slot => $armor) {
                $enchantment = $armor->getEnchantment(CustomEnchantsIds::OBSIDIANSHIELD);
                if ($enchantment !== null) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE), 10, 0, false);
                    $player->addEffect($effect);
                }
                $enchantment = $armor->getEnchantment(CustomEnchantsIds::SHIELDED);
                if ($enchantment !== null) {
                    //$shielded += $enchantment->getLevel();
                    $effect = new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 10, $enchantment->getLevel() - 1, false);
                    $player->addEffect($effect);
                }
            }
        }
    }
}