<?php


namespace shopui\form;


use onebone\economyapi\EconomyAPI;
use pocketmine\form\Form;
use pocketmine\form\FormIcon;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\item\Item;
use pocketmine\Player;
use shopui\ShopUI;
use shopui\util\ShopProvider;

class SelectItemForm extends MenuForm
{
    public function __construct(string $categoryName, Player $player)
    {
        $items = ShopProvider::getCategoryItems($categoryName);
        $options = [];
        foreach ($items as $itemData) {
            /** @var Item $item */
            $item = $itemData["item"];
            $options[] = new MenuOption($item->getName(), new FormIcon($itemData["icon"]));
        }
        parent::__construct($categoryName, ShopUI::_("text3") . ": " . EconomyAPI::getInstance()->getMonetaryUnit() . EconomyAPI::getInstance()->myMoney($player), $options);
    }

    public function onSubmit(Player $player): ?Form
    {
        $selectedIndex = $this->getSelectedOptionIndex();
        $categoryName = $this->getTitle();
        $items = ShopProvider::getCategoryItems($categoryName);
        $itemData = $items[$selectedIndex];
        $player->sendForm(new ItemInteractForm($itemData, $player, [], $categoryName));
        return null;
    }

    public function onClose(Player $player): ?Form
    {
        $player->sendForm(new MainShopForm($player));
        return null;
    }
}