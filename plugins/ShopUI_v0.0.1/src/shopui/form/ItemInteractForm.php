<?php


namespace shopui\form;


use onebone\economyapi\EconomyAPI;
use pocketmine\form\CustomForm;
use pocketmine\form\element\Label;
use pocketmine\form\element\Slider;
use pocketmine\form\element\StepSlider;

use pocketmine\form\Form;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use shopui\ShopUI;

class ItemInteractForm extends CustomForm
{
    // TODO check if can work with public variables

    public static $users = [];
    private $category;

    public function __construct(array $itemData, Player $player, array $preset = [], string $category = null)
    {
        $this->category = $category;
        $elements = [];
        $elements[] = new Label($preset["message"] ?? "");
        $unit = EconomyAPI::getInstance()->getMonetaryUnit();
        $elements[] = new Label(ShopUI::_("text3") . ": " . $unit . EconomyAPI::getInstance()->myMoney($player));
        $elements[] = new Label(ShopUI::_("text6") . ": " . $unit . $itemData["buyPrice"]);
        $elements[] = new Label(ShopUI::_("text7") . ": " . $unit . $itemData["sellPrice"]);
        $elements[] = new StepSlider(ShopUI::_("text11"), [ShopUI::_("text8"), ShopUI::_("text9"), ShopUI::_("text10")], $preset["mode"] ?? 0);
        /** @var Item $item */
        $item = $itemData["item"];
        $elements[] = new Slider(ShopUI::_("text5"), 1.0, (float)$item->getMaxStackSize(), 1.0, $preset["quantity"] ?? 1.0);
        parent::__construct($item->getName(), $elements);
        self::$users[$player->getLowerCaseName()] = $itemData;
    }

    public function onSubmit(Player $player): ?Form
    {
        if (isset(self::$users[$player->getLowerCaseName()])) {
            $itemData = self::$users[$player->getLowerCaseName()];
            unset(self::$users[$player->getLowerCaseName()]);
        } else return null;
        /** @var StepSlider $stepSlider */
        $stepSlider = $this->getElement(4);
        /** @var Slider $quantity */
        $quantity = $this->getElement(5);
        $money = EconomyAPI::getInstance()->myMoney($player);
        switch ($stepSlider->getValue()) {
            case 0: // Buy
                $price = (float)($quantity->getValue() * (float)$itemData["buyPrice"]);
                if ($money < $price) {
                    $player->sendForm(new ItemInteractForm($itemData, $player, ["message" => ShopUI::_("notEnoughMoney"), "mode" => $stepSlider->getValue(), "quantity" => $quantity->getValue()]));
                } else {
                    switch (EconomyAPI::getInstance()->reduceMoney($player, $price)) {
                        case EconomyAPI::RET_SUCCESS:
                            /** @var Item $item */
                            $item = clone $itemData["item"];
                            $item->setCount($quantity->getValue());
                            $player->getInventory()->addItem($item);
                            $player->save();
                            $player->sendForm(new ItemInteractForm($itemData, $player, ["message" => ShopUI::_("transactionSuccess"), "mode" => $stepSlider->getValue(), "quantity" => $quantity->getValue()]));
                            break;
                        default:
                            $player->sendForm(new ItemInteractForm($itemData, $player, ["message" => ShopUI::_("transactionFailed"), "mode" => $stepSlider->getValue(), "quantity" => $quantity->getValue()]));
                    }
                }
                break;
            case 1: // Sell
                /** @var Item $item */
                $item = clone $itemData["item"];
                $availableItemCount = 0;
                foreach ($player->getInventory()->getContents() as $inventoryItem) {
                    if ($inventoryItem->equals($item, true, false)) {
                        $availableItemCount += $inventoryItem->getCount();
                    }
                }
                if ($availableItemCount < $quantity->getValue()) {
                    $player->sendForm(new ItemInteractForm($itemData, $player, ["message" => ShopUI::_("notEnoughItem"), "mode" => $stepSlider->getValue(), "quantity" => $quantity->getValue()]));
                } else {
                    $newContent = $player->getInventory()->getContents();
                    $neededItemCount = $quantity->getValue();
                    foreach ($newContent as $index => $inventoryItem) {
                        if ($inventoryItem->equals($item, true, false)) {
                            if ($neededItemCount - $inventoryItem->getCount() > 0) {
                                $newContent[$index] = ItemFactory::get(Item::AIR, 0, 0);
                                $neededItemCount -= $inventoryItem->getCount();
                            } else {
                                $newContent[$index] = $inventoryItem->setCount(abs($neededItemCount - $inventoryItem->getCount()));
                                break;
                            }
                        }
                    }
                    $player->getInventory()->setContents($newContent);
                    $player->save();
                    EconomyAPI::getInstance()->addMoney($player, (float)$quantity->getValue() * (float)$itemData["sellPrice"]);
                    $player->sendForm(new ItemInteractForm($itemData, $player, ["message" => ShopUI::_("transactionSuccess"), "mode" => $stepSlider->getValue(), "quantity" => $quantity->getValue()]));
                }
                break;
            case 2: // Sell all
                /** @var Item $item */
                $item = clone $itemData["item"];
                $availableItemCount = 0;
                foreach ($player->getInventory()->getContents() as $inventoryItem) {
                    if ($inventoryItem->equals($item, true, false)) {
                        $availableItemCount += $inventoryItem->getCount();
                    }
                }
                $newContent = $player->getInventory()->getContents();
                $neededItemCount = $availableItemCount;
                foreach ($newContent as $index => $inventoryItem) {
                    if ($inventoryItem->equals($item, true, false)) {
                        if ($neededItemCount - $inventoryItem->getCount() > 0) {
                            $newContent[$index] = ItemFactory::get(Item::AIR, 0, 0);
                            $neededItemCount -= $inventoryItem->getCount();
                        } else {
                            $newContent[$index] = $inventoryItem->setCount(abs($neededItemCount - $inventoryItem->getCount()));
                            break;
                        }
                    }
                }
                $player->getInventory()->setContents($newContent);
                $player->save();
                EconomyAPI::getInstance()->addMoney($player, $availableItemCount * (float)$itemData["sellPrice"]);
                $player->sendForm(new ItemInteractForm($itemData, $player, ["message" => ShopUI::_("transactionSuccess"), "mode" => $stepSlider->getValue(), "quantity" => $quantity->getValue()]));
                break;
        }
        return null;
    }

    public function onClose(Player $player): ?Form
    {
        if ($this->category !== null) {
            $player->sendForm(new SelectItemForm($this->category, $player));
        } else $player->sendForm(new MainShopForm($player));
        return null;
    }
}