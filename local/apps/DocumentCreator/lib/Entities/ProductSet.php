<?php

namespace InfoExpert\DocumentCreator\Entities;

class ProductSet extends Product
{
    private $productPriceCoefficient1 = null;
    private $productPriceCoefficient2 = null;

    // Цена продукта на коэффициент 1
    public function getProductPriceCoefficient1(): float
    {
        return $this->productPriceCoefficient1;
    }

    public function setProductPriceCoefficient1(float $value): void
    {
        $this->productPriceCoefficient1 = $value;
    }

    // Цена продукта на коэффициент 2
    public function getProductPriceCoefficient2(): float
    {
        return $this->productPriceCoefficient2;
    }

    public function setProductPriceCoefficient2(float $value): void
    {
        $this->productPriceCoefficient2 = $value;
    }

    // Тип продукта, у которого была изменена цена
    public function getExciter(): string
    {
        return $this->exciter;
    }

    public function setExciter(string $value): void
    {
        $this->exciter = $value;
    }

    public function saveProduct(): void
    {
        $this->saveProductDoc();
    }
}

?>