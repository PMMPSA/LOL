<?php


namespace shopui\util;


use pocketmine\item\Item;

class ShopProvider
{
    private static $shop = [];

    public static function init(array $shop)
    {
        foreach ($shop as $categoryName => $categoryData) {
            self::$shop[$categoryName] = [];
            self::$shop[$categoryName]["icon"] = $categoryData["icon"] ?? null;
            self::$shop[$categoryName]["items"] = [];
            foreach ($categoryData["items"] as $itemData) {
                $item = Item::get($itemData["id"], $itemData["meta"]);
                self::$shop[$categoryName]["items"][] = [
                    "item" => $item,
                    "icon" => "http://fcasspe.vn/items/{$item->getId()}-{$item->getDamage()}.png",
                    "buyPrice" => (float)$itemData["buyPrice"],
                    "sellPrice" => (float)$itemData["sellPrice"]
                ];
            }
        }
    }

    public static function getShop()
    {
        return self::$shop;
    }

    public static function getCategoryNames()
    {
        return array_keys(self::$shop);
    }

    public static function getCategoryItems(string $categoryName)
    {
        return self::$shop[$categoryName]["items"] ?? [];
    }


}