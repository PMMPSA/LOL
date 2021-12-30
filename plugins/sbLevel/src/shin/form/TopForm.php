<?php


namespace Shin\form;


use Shin\sbLevel;
use pocketmine\form\CustomForm;
use pocketmine\form\element\Input;
use pocketmine\form\element\Label;
use pocketmine\form\Form;
use pocketmine\Player;

class TopForm extends CustomForm
{
    private $closeForm;

    public function __construct(Player $player, int $currentPage, int $maxPage, array $content, Form $closeForm = null)
    {
        $elements = [
            new Label("§c§l↣ §eLevel Đảo của bạn:§f " . sbLevel::getInstance()->getCurrentLevel($player)),
            new Label("§c§l↣ §bTrang $currentPage/$maxPage"),
        ];
        foreach ($content as $string) {
            $elements[] = new Label($string);
        }
        $elements[] = new Input("§aĐến trang");
        parent::__construct("§f⚫§l §9Top Đảo §r§f⚫ ", $elements);
        $this->closeForm = $closeForm;
    }

    public function onSubmit(Player $player): ?Form
    {
        /** @var Input $pageInput */
        $elements = $this->getAllElements();
        $pageInput = $elements[count($elements) - 1];
        $page = (int)$pageInput->getValue();
		$data = sbLevel::getInstance()->getAll();
        sbLevel::sendTop($player, $data, $page, $this->closeForm);
        return null;
    }

    public function onClose(Player $player): ?Form
    {
        if ($this->closeForm instanceof Form) $player->sendForm($this->closeForm);
        return null;
    }
}