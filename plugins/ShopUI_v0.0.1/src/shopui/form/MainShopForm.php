<?php


namespace shopui\form;


use onebone\economyapi\EconomyAPI;
use pocketmine\form\Form;
use pocketmine\form\FormIcon;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use shopui\ShopUI;
use shopui\util\ShopProvider;

class MainShopForm extends MenuForm
{
    public function __construct(Player $player)
    {
        $options = [];
        foreach (ShopProvider::getShop() as $categoryName => $categoryData) {
            $icon = (isset($categoryData["icon"]) & $categoryData["icon"] !== null) ? new FormIcon($categoryData["icon"]) : null;
            $options[] = new MenuOption($categoryName, $icon);
        }
        parent::__construct(ShopUI::_("title1"), ShopUI::_("text3") . ": " . EconomyAPI::getInstance()->getMonetaryUnit() . EconomyAPI::getInstance()->myMoney($player), $options);
    }

    public function onSubmit(Player $player): ?Form
    {
        $selectedCategoryName = $this->getSelectedOption()->getText();
        $player->sendForm(new SelectItemForm($selectedCategoryName, $player));
        return null;
    }
}