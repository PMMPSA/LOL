<?php

namespace Shin\task;

use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use Shin\sbLevel;
class TopTask extends AsyncTask
{
    private $page, $data;
    private $max = 0;
    private $topList;

    public function __construct(array $data,int $page)
    {
        $this->data = $data;
        $this->page = $page;
    }

    public function onRun() : void
    {
        $this->topList = serialize((array)$this->getTopList());
    }

    private function getTopList()
    {
		
        $data = (array)$this->data;
        arsort($data);
        $ret = [];
        $n = 1;
        $this->max = ceil(count($data) / 5);
        $this->page = (int)min($this->max, max(1, $this->page));
        foreach ($data as $p => $m) {
            $p = strtolower($p);
            $current = (int)ceil($n / 5);
            if ($current === $this->page) {
                $ret[$n] = [$p, $m];
            } elseif ($current > $this->page) {
                break;
            }
            ++$n;
        }
        return $ret;
    }

    public function onCompletion(Server $server) : void
    {
        $output = "";
        $message = "§c§l↣ §f[§cTOP %1§f] §r§b%2:§a %3 lv\n";
        foreach (unserialize($this->topList) as $n => $list) {
            $output .= str_replace(["%1", "%2", "%3"], [$n, $list[0], $list[1]], $message);
        }
        $output = trim($output);
		$plugin = sbLevel::getInstance();
		$cfg = new Config($plugin->getDataFolder() . 'config.yml', Config::YAML, []);
		$cfg->set('text',  $output);
		$cfg->save();
		$plugin->updateParticles();
		$plugin->sendParticles();
		

    }
}