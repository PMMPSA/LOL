<?php

namespace instantlyta\fcaclan;

use _64FF00\PureChat\PureChat;
use instantlyta\fcaclan\event\ClanChangeEvent;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
//use LDX\iProtector\Main as Iprotect;

class FCAClan extends PluginBase implements Listener
{
    const OWNER_RANK = 3;

    const ACTION_INVITE = 0;
    const ACTION_KICK = 1;
    const ACTION_REQUEST_CONTROL = 2;
    const ACTION_SET_MOTD = 3;
    const ACTION_SET_RANK = 4;
    const ACTION_LEVEL_UP = 5;

    const SETTING_MAX_PLAYERS = 0;
    const SETTING_REQUIRED_POINT = 1;
    const SETTING_REQUIRED_MONEY = 2;
    const SETTING_CLAN_TAG = 3;

    private $settings = [];

    private $clans = [];
    private $invitePending = [];
    private $inviteSendConfirm = [];
    private $kickPending = [];
    private $quitPending = [];
    private $welcomePending = [];
    private $clanPromotePending = [];
    private $clanDeletePending = [];

    public $topClan = [];

    private static $instance = null;

    public function onLoad()
    {
        self::$instance = $this;
    }

    /**
     * @return FCAClan
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->reloadConfig();
        @mkdir($this->getDataFolder() . "profiles/");
        $this->clans = (new Config($this->getDataFolder() . "clans.yml", Config::YAML))->getAll();
        $this->settings = $this->getConfig()->get("settings");
		$this->iprotect = $this->getServer()->getPluginManager()->getPlugin("iProtector");
        $this->updateTopClan();
        $this->getScheduler()->scheduleDelayedRepeatingTask(new UpdateTopClanTask(), 36000, 36000);
    }

    public function onDisable()
    {
        $this->save();
    }

    // Events

    /*public function onJoin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        if ($this->haveClan($player)) {
            $player->setDisplayName("[??l??3??? ??a" . $this->getClanName($player) . "??f ???]" . $player->getName());
        }
    }*/

    public function updateNametag(ClanChangeEvent $event)
    { // PureChat API
        /** @var PureChat $pureChat */
        $pureChat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
        if ($pureChat === null) return;
        $p = $event->getPlayer();

        $isMultiWorldSupportEnabled = $pureChat->getConfig()->get("enable-multiworld-support");

        $levelName = $isMultiWorldSupportEnabled ? $p->getLevel()->getName() : null;

        $p->setNameTag($pureChat->getNameTag($p, $levelName));
    }


    public function onDamagee(EntityDamageEvent $event)
    {
        if($event->isCancelled() or !$this->iprotect->canGetHurt($event->getEntity())){
	    return false;
		}else{
            if ($event instanceof EntityDamageByEntityEvent) {
                $damager = $event->getDamager();
                $entity = $event->getEntity();
                if (!($damager instanceof Player) || !($entity instanceof Player) || !$this->iprotect->canGetHurt($damager)) return;
                if ($this->haveClan($damager) && $this->haveClan($entity) && $this->getProfile($damager)->get("clan") === $this->getProfile($entity)->get("clan")) {
                    $event->setCancelled();
                    $damager->sendMessage("??l??3??? ??aNg?????i ch??i ??c" . $entity->getName() . "??a c??ng Clan c???a b???n.");
                    return;
                } elseif ($entity->getHealth() - $event->getFinalDamage() <= 0) {
                    if ($this->haveClan($entity)) {
                        $this->clanAnnounce("??c" . $entity->getName() . " ??ab??? ng?????i ch??i??c " . $damager->getName() . ($this->haveClan($damager) ? " ??e(Clan " . $this->getClanName($damager) . ")??a " : "") .
                            " ??a????nh ch???t. H??y tr??? th??!", $this->getClanName($entity));
                    }
                    if ($this->haveClan($damager)) {
                        $point = $this->haveClan($entity) ? 2 : 1;
                        $this->clanAnnounce("??c" . $damager->getName() . "??a ???? gi???t ??c" . $entity->getName() . "??a, gi??nh ??c$point ??a??i???m v??? cho Clan!", $this->getClanName($damager));
                        $this->addPoint($point, $this->getClanName($damager));
                    }
                    return;
				}
			}
		}
	}

    protected function paginateText(CommandSender $sender, $pageNumber, array $txt)
    {
        $hdr = array_shift($txt);
        if ($sender instanceof ConsoleCommandSender) {
            $sender->sendMessage(TextFormat::GREEN . $hdr . TextFormat::RESET);
            foreach ($txt as $ln) $sender->sendMessage($ln);
            return true;
        }
        $pageHeight = 8;
        $lineCount = count($txt);
        $pageCount = intval($lineCount / $pageHeight) + ($lineCount % $pageHeight ? 1 : 0);
        $hdr = TextFormat::GREEN . $hdr . TextFormat::RESET;
        if ($pageNumber > $pageCount) {
            $sender->sendMessage($hdr);
            $sender->sendMessage("??3??? ??aKh??ng c?? trang Help n??y");
            return true;
        }
        $hdr .= TextFormat::RED . " ($pageNumber of $pageCount)";
        $sender->sendMessage($hdr);
        for ($ln = ($pageNumber - 1) * $pageHeight; $ln < $lineCount && $pageHeight--; ++$ln) {
            $sender->sendMessage($txt[$ln]);
        }
        return true;
    }

    protected function getPageNumber(array &$args)
    {
        $pageNumber = 1;
        if (count($args) && is_numeric($args[count($args) - 1])) {
            $pageNumber = (int)array_pop($args);
            if ($pageNumber <= 0) $pageNumber = 1;
        }
        return $pageNumber;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "clan":
                if (!isset($args[0])) {
                    $sender->sendMessage("??l??3??? ??cUsage: /clan help");
                    return true;
                }
                /** @var string[] $altArgs */
                $altArgs = array_slice($args, 1);
                switch ($args[0]) {
                    case "help":
                        $help = [
                            "??l??c-+==??b Showing Clan Help ??c=+- ",
                            "??cClan ??f??? ??e/c : ??achat k??nh Clan",
                            "??cClan ??f??? ??e/clan top : ??axem x???p h???ng Clan trong server",
                            "??cClan ??f??? ??e/clan create : ??at???o Clan m???i",
                            "??cClan ??f??? ??e/clan delete : ??axo?? Clan c???a b???n",
                            "??cClan ??f??? ??e/clan join : ??ag???i ????n xin gia nh???p Clan",
                            "??cClan ??f??? ??e/clan quit : ??atho??t Clan c???a b???n",
                            "??cClan ??f??? ??e/clan accept : ??ach???p nh???n l???i m???i v??o Clan",
                            "??cClan ??f??? ??e/clan decline : ??at??? ch???i l???i m???i v??o Clan",
                            "??cClan ??f??? ??e/clan donate : ??ac???ng hi???n cho Clan",
                            "??cClan ??f??? ??e/clan lookup : ??axem ng?????i ch??i thu???c Clan n??o",
                            "??cClan ??f??? ??e/clan requestlist : ??axem danh s??ch c??c th??nh vi??n xin v??o Clan",
                            "??cClan ??f??? ??e/clan status : ??axem th??ng tin Clan",
                            "??cClan ??f??? ??e/clan invite : ??am???i th??m th??nh vi??n v??o Clan",
                            "??cClan ??f??? ??e/clan kick : ??akick th??nh vi??n kh???i Clan",
                            "??cClan ??f??? ??e/clan motd : ??as???a Motd Clan",
                            "??cClan ??f??? ??e/clan setrank : ??aset ch???c v??? cho th??nh vi??n trong Clan",
                            "??cClan ??f??? ??e/clan levelup : ??an??ng c???p Clan",
                        ];
                        $this->paginateText($sender, $this->getPageNumber($args), $help);
                        break;
                    case "create":
                        if ($sender instanceof ConsoleCommandSender || $sender instanceof RemoteConsoleCommandSender) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") !== false) {
                            $sender->sendMessage("??3??? ??aB???n ???? c?? clan.");
                            return true;
                        }
                        if (count($altArgs) <> 1) {
                            $cost = $this->getConfig()->get("create-cost");
                            $sender->sendMessage("??3??? ??cUsage: /clan create <clanName>\nL??u ??: T???o Clan t???n $cost xu!");
                            return true;
                        }

                        if (strpos($altArgs[0], "??")) {
                            $sender->sendMessage("??3?????c Kh??ng ???????c ghi m??u trong l??n clan.");
                            return true;
                        }

                        if (strlen($altArgs[0]) > 20) {
                            $sender->sendMessage("??3?????c T??n clan ph???i ng???n h??n 20 k?? t???.");
                            return true;
                        }

                        if (isset($this->clans[$altArgs[0]])) {
                            $sender->sendMessage("??3?????c Clan ???? t???n t???i.");
                            return true;
                        }

                        $eco = EconomyAPI::getInstance();
                        $money = $eco->myMoney($sender);
                        $cost = $this->getConfig()->get("create-cost");
                        if ($money < $cost) {
                            $sender->sendMessage("??3??? ??eB???n kh??ng c?? ????? ti???n ($cost, b???n c?? $money)");
                            return true;
                        }

                        $eco->reduceMoney($sender, $cost);

                        $this->clans[$altArgs[0]] = [
                            "motd" => $altArgs[0],
                            "level" => 1,
                            "point" => 0,
                            "members" => [
                                strtolower($sender->getName()) => self::OWNER_RANK // see config.yml
                            ],
                            "request" => []
                        ];

                        $profile->set("clan", $altArgs[0]);
                        $profile->set("rank", self::OWNER_RANK);
                        $profile->save();

                        $this->save();
                        $sender->sendMessage("??3??? ??eT???o Clan th??nh c??ng!");
                        break;
                    case "invite":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "accept":
                                if (isset($this->invitePending[$sender->getName()]) && $this->invitePending[$sender->getName()][0] > time()) {
                                    $inviter = $this->getServer()->getPlayerExact($this->invitePending[$sender->getName()][2]);
                                    if ($inviter !== null) {
                                        $inviter->sendMessage($sender->getName() . " ??a???? ch???p nh???n l???i m???i v??o clan c???a b???n.???");
                                    }

                                    $this->addPlayerToClan($sender, $this->invitePending[$sender->getName()][1]);

                                    unset($this->invitePending[$sender->getName()]);
                                    return true;
                                } else {
                                    $sender->sendMessage("??3??? ??aL???i m???i kh??ng t???n t???i.");
                                    return true;
                                }
                                break;
                            case "decline":
                                if (!isset($this->invitePending[$sender->getName()])) {
                                    $sender->sendMessage("??3??? ??aB???n kh??ng ???????c b???t c??? ai m???i.");
                                    return true;
                                } else {
                                    $inviter = $this->getServer()->getPlayerExact($this->invitePending[$sender->getName()][2]);
                                    if ($inviter !== null) {
                                        $inviter->sendMessage($sender->getName() . " ??a???? t??? ch???i l???i m???i v??o clan c???a b???n.???");
                                    }
                                    unset($this->invitePending[$sender->getName()]);
                                    $sender->sendMessage("??3??? ??e???? t??? ch???i l???i m???i.");
                                    return true;
                                }
                                break;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_INVITE)) {
                            $sender->sendMessage("??3??? ??eB???n kh??ng c?? quy???n m???i ng?????i ch??i v??o clan.");
                            return true;
                        }
                        if ($this->isClanFull($profile->get("clan"))) {
                            $sender->sendMessage("??3??? ??eClan ???? ?????y.");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "yes":
                                if (isset($this->inviteSendConfirm[$sender->getName()]) && $this->inviteSendConfirm[$sender->getName()][0] > time()) {
                                    $player = $this->getServer()->getPlayerExact($this->inviteSendConfirm[$sender->getName()][1]);
                                    $this->invitePending[$player->getName()] = [time() + 50, $profile->get("clan"), $sender->getName()];
                                    $sender->sendMessage("??3??? ??aL???i m???i ???? ???????c g???i.");
                                    $player->sendMessage("??3?????d " . $sender->getName() . " ??6???? g???i cho b???n m???t l???i m???i v??o clan??b '" . $profile->get("clan") . "'.");
                                    $player->sendMessage("??3??? ??bB???m ??c/clan invite accept??a ????? ?????ng ?? ho???c decline ????? t??? ch???i.");
                                    $player->sendMessage("??3??? ??cL???i m???i s??? t??? ?????ng h???y sau 50 gi??y.");
                                    return true;
                                } else {
                                    $sender->sendMessage("??3??? ??aL???i m???i kh??ng t???n t???i.");
                                    return true;
                                }
                                break;
                            case "no":
                                if (!isset($this->inviteSendConfirm[$sender->getName()])) {
                                    $sender->sendMessage("??3??? ??aB???n kh??ng m???i b???t c??? ai.");
                                    return true;
                                } else {
                                    unset($this->inviteSendConfirm[$sender->getName()]);
                                    $sender->sendMessage("??3??? ??a???? h???y l???i m???i.");
                                    return true;
                                }
                                break;
                        }
                        if (count($altArgs) <> 1) {
                            $sender->sendMessage("??3??? ??cUsage: /clan invite <playerName>");
                            return true;
                        }
                        $player = $this->getServer()->getPlayer($altArgs[0]);
                        if ($player === null || $player->getName() === $sender->getName()) { // TODO this. is. bad.
                            $sender->sendMessage("??3??? ??eKh??ng t??m th???y ng?????i ch??i n??o v???i t??? kh??a '" . $altArgs[0] . "'");
                            return true;
                        } else if ($player->getName() === $altArgs[0] && $this->getProfile($player)->get("clan") !== false) {
                            $sender->sendMessage("??3??? ??eNg?????i ch??i " . $altArgs[0] . " ???? c?? clan r???i.");
                            return true;
                        }
                        if (strtolower($altArgs[0]) <> strtolower($player->getName())) {
                            $this->inviteSendConfirm[$sender->getName()] = [time() + 50, $player->getName()];
                            $sender->sendMessage("??3??? ??aCh??ng t??i kh??ng t??m th???y ng?????i ch??i '" . $altArgs[0] . "'.\nC?? ph???i b???n mu???n m???i '" . $player->getName() . "'? G?? /clan invite <yes(c??)|no(kh??ng)>");
                            $sender->sendMessage("??3??? ??aL???i m???i s??? t??? ?????ng h???y sau 50 gi??y.");
                            return true;
                        }
                        $this->invitePending[$player->getName()] = [time() + 50, $profile->get("clan"), $sender->getName()];
                        $sender->sendMessage("??3??? ??aL???i m???i ???? ???????c g???i.");
                        $player->sendMessage("??3?????d " . $sender->getName() . " ??6???? g???i cho b???n m???t l???i m???i v??o clan??b '" . $profile->get("clan") . "'.");
                        $player->sendMessage("??3??? ??bB???m ??c/clan invite accept??a ????? ?????ng ?? ho???c decline ????? t??? ch???i.");
                        $player->sendMessage("??3??? ??cL???i m???i s??? t??? ?????ng h???y sau 50 gi??y.");
                        break;
                    case "my":
                    case "info":
                    case "status":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false) {
                            $sender->sendMessage("??3??? ??aB???n kh??ng ??? trong clan n??o.");
                            return true;
                        }
                        $clan = $this->clans[$profile->get("clan")];
                        $mes = "??3??? ??eClan??a " . $clan["motd"] . " (" . $profile->get("clan") . "):\n";
                        $mes .= "??3??? C???p Clan: ??a" . $clan["level"] . "\n";
                        $mes .= "??3??? ??i???m Clan: ??a" . $clan["point"] . "\n";
                        $mes .= "??3??? Ch???c v??? c???a b???n: ??a" . $this->getRankName($profile->get("rank")) . "\n";
                        $mes .= "??3??? Th??nh Vi??n:??c\n";
                        foreach ($clan["members"] as $name => $rank) {
                            $mes .= "  $name: " . $this->getRankName($rank) . "; ";
                        }
                        $sender->sendMessage($mes);
                        break;
                    case "kick":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_KICK)) {
                            $sender->sendMessage("??3??? ??eB???n kh??ng c?? quy???n kick ng?????i ch??i kh???i clan.");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "yes":
                                if (isset($this->kickPending[$sender->getName()]) && $this->kickPending[$sender->getName()][0] > time()) {
                                    $pName = $this->kickPending[$sender->getName()][2];
                                    $targetProfile = $this->getProfile($pName);
                                    if ($targetProfile->get("clan") !== $profile->get("clan")) {
                                        $sender->sendMessage("??3??? ??aNg?????i n??y kh??ng ??? trong Clan c???a b???n.");
                                        unset($this->kickPending[$sender->getName()]);
                                        $sender->sendMessage("??3??? ??a???? h???y y??u c???u kick.");
                                        return true;
                                    }
                                    $result = $this->removePlayerFromClan($pName);
                                    unset($this->kickPending[$sender->getName()]);
                                    $sender->sendMessage($result ? "??3??? ??a???? kick ng?????i ch??i." : "C?? l???i x???y ra trong qu?? tr??nh kick.");
                                    return true;
                                } else {
                                    $sender->sendMessage("??3??? ??aY??u c???u kh??ng t???n t???i.");
                                    return true;
                                }
                                break;
                            case "no":
                                if (!isset($this->kickPending[$sender->getName()])) {
                                    $sender->sendMessage("??3??? ??aB???n kh??ng kick b???t c??? ai.");
                                    return true;
                                } else {
                                    unset($this->kickPending[$sender->getName()]);
                                    $sender->sendMessage("??3??? ??a???? h???y y??u c???u.");
                                    return true;
                                }
                                break;
                        }
                        if (count($altArgs) <> 1) {
                            $sender->sendMessage("??3??? ??cUsage: /clan kick <playerName>");
                            return true;
                        }
                        //$clan = $this->getClan($profile->get("clan"));
                        $needle = $altArgs[0];
                        $found = $this->getPlayerInClan($needle, $this->getClanName($sender), $sender->getName());
                        if ($found === null) {
                            $sender->sendMessage("??3??? ??eKh??ng t??m th???y ng?????i ch??i n??o v???i t??? kh??a '$needle' trong clan c???a b???n.");
                            return true;
                        }
                        $this->kickPending[$sender->getName()] = [time() + 50, $profile->get("clan"), $found];
                        $sender->sendMessage("??3??? ??cB???n c?? ch???c ch???n mu???n kick '$found' kh???i clan?");
                        $sender->sendMessage("??3??? ??cB???m /clan kick yes ????? x??c nh???n.");
                        $sender->sendMessage("??3??? ??cY??u c???u kick s??? t??? ?????ng h???y sau 50 gi??y.");
                        break;
                    case "requestlist":
                    case "rl":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_REQUEST_CONTROL)) {
                            $sender->sendMessage("??l??3??? ??aB???n kh??ng c?? quy???n ch???p nh???n/t??? ch???i y??u c???u v??o clan.");
                            return true;
                        }
                        $clan =& $this->clans[$profile->get("clan")];
                        if (isset($altArgs[0])) {
                            //in_array($altArgs[0], $clan["request"])
                            if (($found = $this->getPlayerInRequestList($altArgs[0], $profile->get("clan"))) !== null) {
                                if ($this->isClanFull($profile->get("clan"))) {
                                    $sender->sendMessage("??l??3??? ??aClan ???? ?????y.");
                                    return true;
                                }
                                $this->addPlayerToClan($found, $profile->get("clan"));
                                unset($clan["request"][array_search($found, $clan["request"])]);
                                $this->save();
                                $sender->sendMessage("??l??3??? ??a???? ch???p nh???n y??u c???u n??y.");
                            } else {
                                $sender->sendMessage("??l??3??? ??aKh??ng t??m th???y ng?????i ch??i n??y.");
                            }
                            return true;
                        }
                        $mes = "??l??3??? ??aDanh s??ch y??u c???u v??o clan:\n";
                        foreach ($clan["request"] as $name) {
                            $mes .= "$name; ";
                        }
                        $mes .= "\nG?? /clan requestlist <playerName> ????? ch???p nh???n ng?????i ch??i v??o clan.";
                        $sender->sendMessage($mes);
                        break;
                    case "delete":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || $profile->get("rank") !== self::OWNER_RANK) {
                            $sender->sendMessage("??l??3??? ??aB???n kh??ng c?? quy???n x??a clan.");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "yes":
                                if (isset($this->clanDeletePending[$sender->getName()]) && $this->clanDeletePending[$sender->getName()][0] > time()) {
                                    $this->clanAnnounce("??l??3??? ??aClan ???? b??? gi???i t??n b???i Ch??? Clan.", $profile->get("clan"));
                                    foreach ($this->clans[$profile->get("clan")]["members"] as $name => $rank) {
                                        $this->removePlayerFromClan($name);
                                    }
                                    unset($this->clans[$profile->get("clan")]);
                                    $this->save();
                                    unset($this->clanDeletePending[$sender->getName()]);
                                    $sender->sendMessage("??l??3??? ??aX??a clan th??nh c??ng.");
                                    return true;
                                } else {
                                    $sender->sendMessage("??l??3??? ??aY??u c???u kh??ng t???n t???i.");
                                    return true;
                                }
                                break;
                            case "no":
                                if (!isset($this->clanDeletePending[$sender->getName()])) {
                                    $sender->sendMessage("??l??3??? ??aY??u c???u kh??ng t???n t???i.");
                                    return true;
                                } else {
                                    unset($this->clanDeletePending[$sender->getName()]);
                                    $sender->sendMessage("??l??3??? ??a???? h???y y??u c???u.");
                                    return true;
                                }
                                break;
                        }
                        $this->clanDeletePending[$sender->getName()] = [time() + 50];
                        $sender->sendMessage("??l??3??? ??aB???n ??ang x??a clan " . $profile->get("clan") . ".");
                        $sender->sendMessage("??l??3??? ??aNh???p /clan delete yes ????? x??c nh???n");

                        break;
                    case "quit":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false) {
                            $sender->sendMessage("??l??3??? ??aB???n kh??ng ??? trong clan n??o.");
                            return true;
                        }
                        if ($profile->get("rank") === self::OWNER_RANK) {
                            $sender->sendMessage("??l??3??? ??aB???n kh??ng th??? tho??t clan khi b???n l?? ch??? clan. H??y chuy???n nh?????ng ch???c v??? tr?????c.");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "yes":
                                if (isset($this->quitPending[$sender->getName()]) && $this->quitPending[$sender->getName()][0] > time()) {
                                    $result = $this->removePlayerFromClan($sender);
                                    unset($this->quitPending[$sender->getName()]);
                                    $sender->sendMessage($result ? "??l??3??? ??aTho??t clan th??nh c??ng." : "C?? l???i x???y ra trong qu?? tr??nh tho??t clan.");
                                    return true;
                                } else {
                                    $sender->sendMessage("??l??3??? ??aY??u c???u kh??ng t???n t???i.");
                                    return true;
                                }
                                break;
                            case "no":
                                if (!isset($this->quitPending[$sender->getName()])) {
                                    return true;
                                } else {
                                    unset($this->quitPending[$sender->getName()]);
                                    $sender->sendMessage("??l??3??? ?????? h???y y??u c???u.");
                                    return true;
                                }
                                break;
                        }
                        $this->quitPending[$sender->getName()] = [time() + 50];
                        $sender->sendMessage("??l??3??? ??aB???n c?? ch???c ch???n mu???n r???i clan " . $profile->get("clan") . "?");
                        $sender->sendMessage("??l??3??? ??aNh???p /clan quit yes ????? x??c nh???n");
                        break;
                    case "admin":
                        // TODO
                        break;
                    case "motd":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_SET_MOTD)) {
                            $sender->sendMessage("??l??3??? ??aB???n kh??ng c?? quy???n ch???nh s???a MOTD c???a clan.");
                            return true;
                        }
                        if (!isset($altArgs[0])) {
                            $sender->sendMessage("??l??3??? ??cUsage: /clan motd <message>");
                            return true;
                        }
                        $this->clans[$profile->get("clan")]["motd"] = $altArgs[0];
                        $this->save();
                        $sender->sendMessage("??l??3??? ??a???? ch???nh s???a MOTD.");
                        break;
                    case "welcome":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false) {
                            $sender->sendMessage("??l??3??? ??aB???n kh??ng ??? trong clan n??o.");
                            return true;
                        }
                        if (isset($this->welcomePending[$profile->get("clan")])) {
                            foreach ($this->welcomePending[$profile->get("clan")] as $key => $data) {
                                if (!in_array($sender->getName(), $data[1])) {
                                    $this->clanChat($sender, "??l??3??? ??aCh??o m???ng " . $data[0] . " tham gia clan nh??!");
                                    $this->welcomePending[$profile->get("clan")][$key][1][] = $sender->getName();
                                }
                            }
                        }
                        break;
                    case "setrank":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_SET_RANK)) {
                            $sender->sendMessage("??l??3??? ??aB???n kh??ng c?? quy???n ch???nh s???a rank c???a th??nh vi??n clan.");
                            return true;
                        }
                        if (!isset($altArgs[0])) {
                            $sender->sendMessage("??l??3??? ??cUsage: /clan setrank <playerName> <rankLevel>"); // TODO Rank Name
                            $mes = "??l??3??? ??aDanh s??ch rank:??d \n";
                            foreach ($this->getConfig()->get("rank") as $rank => $info) {
                                $mes .= "$rank -> " . $info["name"] . "\n";
                            }
                            $sender->sendMessage($mes);
                            return true;
                        }
                        //$clan =& $this->clans[$profile->get("clan")];
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "yes":
                                if (isset($this->clanPromotePending[$sender->getName()]) && $this->clanPromotePending[$sender->getName()][0] > time()) {
                                    $newOwner = $this->clanPromotePending[$sender->getName()][1];
                                    $this->setRank($newOwner, self::OWNER_RANK);
                                    $this->setRank($sender, 1);
                                    unset($this->clanPromotePending[$sender->getName()]);
                                    $sender->sendMessage("??l??3??? ??aB???n ???? chuy???n nh?????ng Clan cho $newOwner");
                                    $this->clanAnnounce("??l??3??? ??aClan ???? ???????c chuy???n nh?????ng to??n b??? quy???n s??? h???u cho $newOwner, h??y ch??c m???ng!", $profile->get("clan"));
                                    return true;
                                } else {
                                    $sender->sendMessage("??l??3??? ??aY??u c???u kh??ng t???n t???i.");
                                    return true;
                                }
                                break;
                            case "no":
                                if (!isset($this->clanPromotePending[$sender->getName()])) {
                                    return true;
                                } else {
                                    unset($this->clanPromotePending[$sender->getName()]);
                                    $sender->sendMessage("??l??3??? ??a???? h???y y??u c???u.");
                                    return true;
                                }
                                break;
                        }
                        if (isset($altArgs[0])) {
                            if (isset($altArgs[1])) {
                                $altArgs[1] = (int)$altArgs[1];
                                if (($found = $this->getPlayerInClan($altArgs[0], $profile->get("clan"), $sender->getName())) === null) {
                                    $sender->sendMessage("??l??3??? ??aNg?????i ch??i kh??ng c?? trong clan.");
                                    return true;
                                }
                                $targetProfile = $this->getProfile($found);
                                if ($targetProfile->get("rank") >= $profile->get("rank")) {
                                    $sender->sendMessage("??l??3??? ??aNg?????i ch??i c?? ch???c v??? cao h??n ho???c ngang b???ng b???n.");
                                    return true;
                                }
                                if ((string)((int)$altArgs[1]) !== (string)$altArgs[1]) {
                                    $sender->sendMessage("??l??3??? ??aRank ph???i l?? m???t s???.");
                                    return true;
                                }
                                $altArgs[1] = (int)$altArgs[1];
                                if ($altArgs[1] >= $profile->get("rank") && $profile->get("rank") !== self::OWNER_RANK) {
                                    $sender->sendMessage("??l??3??? ??aB???n kh??ng th??? ?????t ch???c v??? c???a ng?????i kh??c cao h??n ho???c ngang b???ng b???n.");
                                    return true;
                                } else if ($altArgs[1] === $targetProfile->get("rank")) {
                                    $sender->sendMessage("??l??3??? ??aNg?????i n??y ???? ??ang ??? ch???c n??y r???i.");
                                    return true;
                                }
                                if ($altArgs[1] === self::OWNER_RANK) {
                                    if ($profile->get("rank") !== self::OWNER_RANK) {
                                        $sender->sendMessage("??l??3??? ??aB???n kh??ng c?? quy???n chuy???n nh?????ng quy???n s??? h???u clan.");
                                        return true;
                                    }
                                    $this->clanPromotePending[$sender->getName()] = [time() + 50, $found];
                                    $sender->sendMessage("??l??3??? ??aB???n c?? CH???C CH???N mu???n chuy???n quy???n s??? h???u Clan cho $found?");
                                    $sender->sendMessage("??l??3??? ??aB???n s??? kh??ng c??n quy???n s??? h???u Clan n??y v?? tr??? v??? rank " . $this->getRankName(1) . ".");
                                    $sender->sendMessage("??l??3??? ??aG?? /clan setrank yes ????? x??c nh???n. Y??u c???u chuy???n nh?????ng s??? b??? h???y sau 50 gi??y.");
                                    return true;
                                }
                                if (!isset($this->getConfig()->get("rank")[$altArgs[1]])) {
                                    $sender->sendMessage("??l??3??? ??cKh??ng c?? rank n??y.");
                                    return true;
                                }
                                $promote = $altArgs[1] > $targetProfile->get("rank");
                                $this->setRank($found, $altArgs[1]);
                                $sender->sendMessage("??l??3??? ??a?????t ch???c v??? th??nh c??ng.");
                                $this->clanAnnounce($found . " ???? ???????c " . ($promote ? "n??ng" : "h???") . " ch???c " . $this->getRankName($altArgs[1]) . " b???i " . $sender->getName(), $profile->get("clan"));
                            }
                        }
                        break;
                    case "lookup":
                        if (count($altArgs) <> 1) {
                            $sender->sendMessage("??l??3??? ??cUsage: /clan lookup <playerName>");
                            return true;
                        }
                        $sender->sendMessage("??l??3??? ??aNg?????i ch??i " . $altArgs[0] . " " . ($this->haveClan($altArgs[0]) ? "thu???c clan " . $this->getClanName($altArgs[0]) : "kh??ng thu???c clan n??o."));
                        break;
                    case "reload":
                        if (!($sender->isOp())) return true;
                        $this->saveDefaultConfig();
                        $this->reloadConfig();
                        $sender->sendMessage("??l??3??? ??a???? reload config.yml");
                        break;
                    case "top":
                        $mes = "??l??3??? ??a????y l?? top 3 Clan xu???t s???c nh???t to??n Server:\n";
                        for ($i = 0; $i < 3; $i++) {
                            $mes .= "??e Top " . ($i + 1) . ":??6 " . (isset($this->topClan[$i]) ? $this->topClan[$i] : "??aCh??a c???p nh???t") . "\n";
                        }
                        if ($sender instanceof Player && $this->haveClan($sender)) {
                            $mes .= "??l??3??? ??eClan b???n ??ang x???p th???: ??a" . (isset(array_flip($this->topClan)[$this->getClanName($sender)]) ? array_flip($this->topClan)[$this->getClanName($sender)] + 1 : "Ch??a c???p nh???t") . "\n";
                        }
                        $mes .= "??l??3??? ??dTop Clan s??? ???????c c???p nh???t m???i 30 ph??t.";
                        $sender->sendMessage($mes);
                        break;
                    case "levelup":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_LEVEL_UP)) {
                            $sender->sendMessage("??l??3??? ??aB???n kh??ng c?? quy???n n??ng c???p clan.");
                            return true;
                        }
                        $clan = $this->getClan($profile->get("clan"));
                        $nextLevel = $clan["level"] + 1;
                        if ($nextLevel > $this->getConfig()->get("max-clan-level")) {
                            $sender->sendMessage("??l??3??? ??aClan b???n ???? ?????t level t???i ??a!");
                            return true;
                        }
                        $requiredPoint = $this->getSetting(self::SETTING_REQUIRED_POINT, $nextLevel);
                        $requiredMoney = $this->getSetting(self::SETTING_REQUIRED_MONEY, $nextLevel);
                        $eco = EconomyAPI::getInstance();
                        if (isset($altArgs[0]) && $altArgs[0] === "up") {
                            if ($clan["point"] < $requiredPoint) {
                                $sender->sendMessage("??l??3??? ??aClan b???n kh??ng c?? ????? ??i???m Clan ????? n??ng c???p (C???n " . $requiredPoint . ")");
                                return true;
                            }
                            if ($eco->myMoney($sender) < $requiredMoney) {
                                $sender->sendMessage("??l??3??? ??aB???n kh??ng c?? ????? ti???n ????? n??ng c???p Clan (C???n " . $eco->getMonetaryUnit() . $requiredMoney . ")");
                                return true;
                            }
                            $this->clans[$profile->get("clan")]["point"] -= $requiredPoint;
                            $eco->reduceMoney($sender, $requiredMoney);
                            $this->clans[$profile->get("clan")]["level"] = $nextLevel;
                            $this->save();
                            //$eco->save();
                            $sender->sendMessage("??l??3??? ??aN??ng c???p clan th??nh c??ng!");
                            $this->clanAnnounce("??l??3??? ??aClan ???? ???????c n??ng c???p l??n c???p $nextLevel. Xin ch??c m???ng!", $profile->get("clan"));
                            return true;
                        }
                        $sender->sendMessage("??l??3??? ??a????? n??ng c???p clan l??n level $nextLevel c???n ti??u hao $requiredPoint ??i???m clan v?? " . $eco->getMonetaryUnit() . "$requiredMoney.");
                        $sender->sendMessage("??l??3??? ??c??i???m Clan c?? th??? ki???m ???????c b???ng c??ch gi???t ng?????i ch??i clan kh??c (+2) ho???c ng?????i ch??i b??nh th?????ng (+1)");
                        $sender->sendMessage("??l??3??? ??dG?? /clan levelup up ????? x??c nh???n n??ng c???p clan.");
                        break;
                    case "join":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") !== false) {
                            $sender->sendMessage("??l??3??? ??aB???n ???? c?? clan.");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "cancel":
                                if (!isset($altArgs[1])) {
                                    $sender->sendMessage("??l??3??? ??cUsage: /clan join cancel <clanName>");
                                    return true;
                                }
                                if (!isset($this->clans[$altArgs[1]])) {
                                    $sender->sendMessage("??l??3??? ??aClan kh??ng t???n t???i.");
                                    return true;
                                }
                                $clan =& $this->clans[$altArgs[1]];
                                if (!in_array($sender->getName(), $clan["request"])) {
                                    $sender->sendMessage("??l??3??? ??aB???n ch??a y??u c???u v??o clan n??y.");
                                    return true;
                                }
                                unset($clan["request"][array_search($sender->getName(), $clan["request"])]);
                                $this->save();
                                $sender->sendMessage("??l??3??? ??aH???y ????n th??nh c??ng!");
                                break;
                        }
                        if (!isset($altArgs[0])) {
                            $sender->sendMessage("??l??3??? ??cUsage: /clan join <clanName>");
                            $sender->sendMessage("??l??3??? ??cUsage: /clan join cancel <clanName>");
                            return true;
                        }
                        if (!isset($this->clans[$altArgs[0]])) {
                            $sender->sendMessage("??l??3??? ??aClan " . $altArgs[0] . " kh??ng t???n t???i.");
                            return true;
                        }
                        if (in_array(strtolower($sender->getName()), $this->clans[$altArgs[0]]["request"])) {
                            $sender->sendMessage("??l??3??? ??aB???n ???? xin v??o clan n??y r???i.");
                            return true;
                        }
                        $this->clans[$altArgs[0]]["request"][] = strtolower($sender->getName());
                        $this->save();
                        $sender->sendMessage("??l??3??? ??aXin v??o clan " . $altArgs[0] . " th??nh c??ng. B???n c?? th??? h???y ????n xin v??o 1 clan b???ng l???nh /clan join cancel");
                        break;
                    case "donate":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false) {
                            $sender->sendMessage("??l??3??? ??aB???n kh??ng ??? trong clan n??o.");
                            return true;
                        }
                        $perPointCost = $this->getConfig()->get("point-cost");
                        $eco = EconomyAPI::getInstance();
                        if (!isset($altArgs[0]) || (string)(int)$altArgs[0] !== $altArgs[0]) {
                            $sender->sendMessage("??l??3??? ??cUsage: /clan donate <point>\n1 point = " . $eco->getMonetaryUnit() . $perPointCost);
                            return true;
                        }
                        $point = (int)$altArgs[0];
                        if ($point <= 0) {
                            $sender->sendMessage("??l??3??? ??a??i???m donate ph???i l?? m???t s??? d????ng.");
                            return true;
                        }
                        if ($eco->myMoney($sender) - $point * $perPointCost < 0) {
                            $sender->sendMessage("??l??3??? ??aB???n kh??ng ????? ti???n (C???n " . ($point * $perPointCost) . ", b???n c?? " . $eco->myMoney($sender) . ")");
                            return true;
                        }
                        $this->addPoint($point, $this->getClanName($sender));
                        $eco->reduceMoney($sender, $point * $perPointCost);
                        $this->save();
                        $sender->sendMessage("??l??3??? ??a???? ????ng g??p $point ??i???m.");
                        $this->clanAnnounce($sender->getName() . " ???? ????ng g??p $point ??i???m.", $this->getClanName($sender));
                        break;
                    default:
                        $sender->sendMessage("??l??3??? ??aKh??ng t??m th???y l???nh. G?? ??c/clan help ??a????? xem chi ti???t.");
                        return true;
                }

                return true;
            case "c":
                if (!($sender instanceof Player)) {
                    $sender->sendMessage("??c H??y ch???y l???nh n??y trong game");
                    return true;
                }
                $profile = $this->getProfile($sender);
                if ($profile->get("clan") === false) {
                    $sender->sendMessage("??l??3??? ??aB???n kh??ng ??? trong clan n??o.");
                    return true;
                }
                if (!isset($args[0])) {
                    $sender->sendMessage("??l??3??? ??cUsage: /c <clan chat message>");
                    return true;
                }
                $this->clanChat($sender, implode(" ", $args));
                return true;
            default:
                return false;
        }
    }

    /**
     * @param Player|string $player
     * @return Config
     */
    public function getProfile($player)
    {
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        $player = strtolower($player);
        $dir = $this->getDataFolder() . "/profiles/" . substr($player, 0, 1) . "/";
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $cfg = new Config($dir . "$player.yml", Config::YAML, ["clan" => false, "rank" => 0]);
        return $cfg;
    }

    /**
     * @param int $rank
     * @return string
     */
    public function getRankName(int $rank)
    {
        return $this->getConfig()->get("rank")[$rank]["name"];
    }

    public function save()
    {
        $data = new Config($this->getDataFolder() . "clans.yml", Config::YAML);
        $data->setAll($this->clans);
        $data->save();
    }

    /**
     * @param Player|string $player
     * @param int $action
     * @return bool|null
     */
    public function canDo($player, int $action)
    {
        $profile = $this->getProfile($player);
        if ($profile === null) return null;
        if (($actionString = $this->actionToString($action)) === null) return null;
        return $this->getConfig()->get("rank")[$profile->get("rank")]["control"][$actionString];
    }

    public function actionToString(int $action)
    {
        switch ($action) {
            case self::ACTION_INVITE:
                return "invite";
            case self::ACTION_KICK:
                return "kick";
            case self::ACTION_REQUEST_CONTROL:
                return "requestcontrol";
            case self::ACTION_SET_MOTD:
                return "setmotd";
            case self::ACTION_SET_RANK:
                return "setrank";
            case self::ACTION_LEVEL_UP:
                return "levelup";
        }
        return null;
    }

    /**
     * @param Player|string $player
     * @param string $clanName
     * @return bool
     */
    public function addPlayerToClan($player, string $clanName)
    {
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        $profile = $this->getProfile($player);
        if (!isset($this->clans[$clanName])) return false;
        $clan =& $this->clans[$clanName];
        if (count($clan["members"]) >= $this->getSetting(self::SETTING_MAX_PLAYERS, $clan["level"])) return false;
        $clan["members"][strtolower($player)] = 1;
        $this->save();
        $profile->set("clan", $clanName);
        $profile->set("rank", 1);
        $profile->save();
        $this->welcomePending[$clanName][] = [$player, []];
        if (($tmp = $this->getServer()->getPlayerExact($player)) !== null) {
            $this->getServer()->getPluginManager()->callEvent(new ClanChangeEvent($tmp, $clanName));
        }
        $this->clanAnnounce($player . " ???? v??o clan! G?? /clan welcome ????? ch??o m???ng!", $clanName);
        return true;
    }

    /**
     * @param Player|string $player
     * @return bool
     */
    public function removePlayerFromClan($player)
    {
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        $profile = $this->getProfile($player);
        $clanName = $profile->get("clan");
        try {
            unset($this->clans[$clanName]["members"][strtolower($player)]);
        } catch (\Exception $exception) {
            $this->getLogger()->error("Invalid Clan Name");
            return false;
        }
        $this->save();
        $profile->set("clan", false);
        $profile->set("rank", 0);
        $profile->save();
        if (($tmp = $this->getServer()->getPlayerExact($player)) !== null) {
            $this->getServer()->getPluginManager()->callEvent(new ClanChangeEvent($tmp, $clanName));
            $tmp->sendMessage("??l??3??? ??aB???n ???? b??? kick kh???i clan.");
        }
        $this->clanAnnounce($player . " ???? r???i clan.", $clanName);
        return true;
    }

    public function clanAnnounce(string $message, string $clanName)
    {
        $clan = $this->clans[$clanName];
        foreach ($clan["members"] as $name => $rank) {
            $member = $this->getServer()->getPlayerExact($name);
            if (!($member instanceof Player)) continue;
            $member->sendMessage("??l??3?????d " . $message);
        }
    }

    public function clanChat(Player $player, string $message)
    {
        $profile = $this->getProfile($player);
        $clan = $this->clans[$profile->get("clan")];

        /** @var PureChat $pureChat */
        $pureChat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
        if ($pureChat === null) return;
        $format = $this->getConfig()->get("clan-chat-format");
        $format .= $message;
        $mes = $pureChat->applyPCTags($format,$player,"",null);
        foreach ($clan["members"] as $name => $rank) {
            $member = $this->getServer()->getPlayerExact($name);
            if (!($member instanceof Player)) continue;
            $member->sendMessage($mes);
        }
    }

    public function setRank($player, int $rank)
    {
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        $profile = $this->getProfile($player);
        $clan =& $this->clans[$profile->get("clan")];
        $clan["members"][strtolower($player)] = $rank;
        $this->save();
        $profile->set("rank", $rank);
        $profile->save();
    }

    public function haveClan($player)
    {
        if ($player instanceof Player) $player = $player->getName();
        $profile = $this->getProfile($player);
        return $profile->get("clan") !== false;
    }

    public function getClanName($player)
    {
        $profile = $this->getProfile($player);
        return $profile->get("clan");
    }

    public function getClanTag($player)
    {
        /** @var string $style */
        $style = $this->getSetting(self::SETTING_CLAN_TAG, $this->getClan($this->getClanName($player))["level"]);
        $style = str_replace("%s", $this->getClanName($player), $style);
        return $style;
    }

    public function getClan(string $clanName)
    {
        return $this->clans[$clanName];
    }

    public function isClanFull(string $clanName)
    {
        $clan = $this->getClan($clanName);
        return count($clan["members"]) >= $this->getSetting(self::SETTING_MAX_PLAYERS, $clan["level"]);
    }

    public function getSetting(int $settingId, int $clanLevel)
    {
        switch ($settingId) {
            case self::SETTING_MAX_PLAYERS:
                $key = "max_players";
                break;
            case self::SETTING_REQUIRED_POINT:
                $key = "required_points";
                break;
            case self::SETTING_REQUIRED_MONEY:
                $key = "required_money";
                break;
            case self::SETTING_CLAN_TAG:
                $key = "clan_tag";
                break;
            default:
                return null;
        }
        return $this->settings[$key][$clanLevel - 1];
    }

    public function updateTopClan()
    {
        $topClan = array_keys($this->clans);
        for ($i = 0; $i <= count($topClan) - 2; $i++) {
            for ($j = $i + 1; $j <= count($topClan) - 1; $j++) {
                if ($this->clans[$topClan[$j]]["level"] > $this->clans[$topClan[$i]]["level"] ||
                    ($this->clans[$topClan[$j]]["level"] === $this->clans[$topClan[$i]]["level"] && $this->clans[$topClan[$j]]["point"] > $this->clans[$topClan[$i]]["point"])
                ) {
                    $t = $topClan[$j];
                    $topClan[$j] = $topClan[$i];
                    $topClan[$i] = $t;
                }
            }
        }
        $this->topClan = $topClan;
        $this->getServer()->broadcastMessage("??l??l??3??? ??aTop Clan ???? ???????c c???p nh???t.");
    }

    public function addPoint(int $point, string $clanName)
    {
        $clan =& $this->clans[$clanName];
        $clan["point"] += $point;
    }

    /**
     * @param string $needle
     * @param string $clanName
     * @param string $except
     * @return null|string
     */
    public function getPlayerInClan(string $needle, string $clanName, string $except = null)
    {
        $clan = $this->getClan($clanName);
        $found = null;
        $needle = strtolower($needle);
        $delta = PHP_INT_MAX;
        foreach ($clan["members"] as $name => $rank) {
            if ($except !== null) {
                if ($name === $except) continue;
            }
            if (stripos($name, $needle) === 0) {
                $curDelta = strlen($name) - strlen($needle);
                if ($curDelta < $delta) {
                    $found = $name;
                    $delta = $curDelta;
                }
                if ($curDelta === 0) {
                    break;
                }
            }
        }

        return $found;
    }

    /**
     * @param string $needle
     * @param string $clanName
     * @param string $except
     * @return null|string
     */
    public function getPlayerInRequestList(string $needle, string $clanName, string $except = null)
    {
        $clan = $this->getClan($clanName);
        $found = null;
        $needle = strtolower($needle);
        $delta = PHP_INT_MAX;
        foreach ($clan["request"] as $name) {
            if ($except !== null) {
                if ($name === $except) continue;
            }
            if (stripos($name, $needle) === 0) {
                $curDelta = strlen($name) - strlen($needle);
                if ($curDelta < $delta) {
                    $found = $name;
                    $delta = $curDelta;
                }
                if ($curDelta === 0) {
                    break;
                }
            }
        }

        return $found;
    }

}
