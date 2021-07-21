<?php

namespace InfoExpert\DocumentCreator\Entities;

use \InfoExpert\DocumentCreator\Entities\Product;
use \InfoExpert\DocumentCreator\Entities\ProductItem;
use \InfoExpert\DocumentCreator\Helpers\SettingsHelper;

class ProductItemFactory
{
    use ProductFactoryTrait;

    public static function createProduct(int $productId, float $productPrice): ?Product
    {
        $product = new ProductItem($productId);

        $product = self::fillIblockElementData($product, $productPrice);

        if (isset($product)) {
            $settingsHelper = SettingsHelper::getInstance();
            $arProductItemWildcardValues = $settingsHelper->getArProductItemWildcardValues();
            if (is_array($arProductItemWildcardValues)) {
                $product->setWildcardValues($arProductItemWildcardValues);
            } else {
                $product = null;
            }
        }

        return $product;
    }
}

?>