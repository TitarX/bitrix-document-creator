<?php

namespace InfoExpert\DocumentCreator\Entities;

class ProductItem extends Product
{
    public function saveProduct(): void
    {
        $this->saveProductDoc();
    }
}

?>