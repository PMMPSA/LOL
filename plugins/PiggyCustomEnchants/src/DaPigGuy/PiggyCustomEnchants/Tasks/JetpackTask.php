<?php

namespace DaPigGuy\PiggyCustomEnchants\Tasks;

use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use DaPigGuy\PiggyCustomEnchants\Main;
use pocketmine\level\particle\FlameParticle;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

/**
 * Class JetpackTask
 * @package DaPigGuy\PiggyCustomEnchants\Tasks
 */
class JetpackTask extends Task
{
    /** @var Main */
    private $plugin;

    /**
     * JetpackTask constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $enchantment = $player->getArmorInventory()->getBoots()->getEnchantment(CustomEnchantsIds::JETPACK);
            if ($enchantment !== null) {
                if (isset($this->plugin->flying[$player->getLowerCaseName()]) && $this->plugin->flying[$player->getLowerCaseName()] > time()) {
                    if (!in_array($player->getLevel()->getName(), $this->plugin->jetpackDisabled)) {
                        if ($this->plugin->flying[$player->getLowerCaseName()] - 30 <= time()) {
                            $player->sendTip(TextFormat::RED . "§lNăng Lượng Thấp. " . floor($this->plugin->flying[$player->getLowerCaseName()] - time()) . " Giây Còn Lại Của Sức Mạnh.");
                        } else {
                            $time = ($this->plugin->flying[$player->getLowerCaseName()] - time());
                            $time = is_float($time / 15) ? floor($time / 15) + 1 : $time / 15;
                            $color = $time > 10 ? TextFormat::GREEN : ($time > 5 ? TextFormat::YELLOW : TextFormat::RED);
                            $player->sendTip($color . "§lNăng Lượng: " . str_repeat("▌", $time));
                        }
                        $this->fly($player, $enchantment->getLevel());
                        continue;
                    }
                }
            }
            if (isset($this->plugin->flying[$player->getLowerCaseName()])) {
                if ($this->plugin->flying[$player->getLowerCaseName()] > time()) {
                    $this->plugin->flyremaining[$player->getLowerCaseName()] = $this->plugin->flying[$player->getLowerCaseName()] - time();
                    unset($this->plugin->jetpackcd[$player->getLowerCaseName()]);
                }
                unset($this->plugin->flying[$player->getLowerCaseName()]);
                $player->sendPopup(TextFormat::RED . "§lJetPack Đã Tắt.");
            }
            if (isset($this->plugin->flyremaining[$player->getLowerCaseName()])) {
                if ($this->plugin->flyremaining[$player->getLowerCaseName()] < 300) {
                    if (!isset($this->plugin->jetpackChargeTick[$player->getLowerCaseName()])) {
                        $this->plugin->jetpackChargeTick[$player->getLowerCaseName()] = 0;
                    }
                    $this->plugin->jetpackChargeTick[$player->getLowerCaseName()]++;
                    if ($this->plugin->jetpackChargeTick[$player->getLowerCaseName()] >= 30) {
                        $this->plugin->flyremaining[$player->getLowerCaseName()]++;
                    }
                }
            }
        }
    }

    /**
     * @param Player $player
     * @param        $level
     */
    public function fly(Player $player, $level)
    {
        $player->setMotion($player->getDirectionVector()->multiply($level));
        $player->getLevel()->addParticle(new FlameParticle($player));
    }
}