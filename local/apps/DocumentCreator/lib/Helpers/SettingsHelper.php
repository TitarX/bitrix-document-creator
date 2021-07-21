<?php
namespace InfoExpert\DocumentCreator\Helpers;

class SettingsHelper
{
    private static $instance = null;

    private $appDirPath = null;
    private $docsDirPath = null;
    private $logsDirPath = null;
    private $setPriceCoefficient1 = null;
    private $setPriceCoefficient2 = null;
    private $arProductItemWildcardValues = null;
    private $arProductSetWildcardValues = null;
    private $doPdfTransform = null;
    private $doOldFilesDelete = null;

    private function __construct()
    {
        $this->doPdfTransform = false;
    }

    public static function getInstance() : SettingsHelper
    {
        if(empty(self::$instance)) {
            self::$instance = new SettingsHelper();
        }

        return self::$instance;
    }

    // Директория приложения
    public function setAppDirPath(string $value) : void
    {
        $this->appDirPath = $value;
    }
    public function getAppDirPath() : string
    {
        return $this->appDirPath;
    }

    // Директория документов
    public function setDocsDirPath(string $value) : void
    {
        $this->docsDirPath = $value;
    }
    public function getDocsDirPath() : string
    {
        return $this->docsDirPath;
    }

    // Директория логов
    public function setLogsDirPath(string $value) : void
    {
        $this->logsDirPath = $value;
    }
    public function getLogsDirPath() : string
    {
        return $this->logsDirPath;
    }

    // Первый коэффициент для расчёта цены комплекта
    public function setSetPriceCoefficient1(float $value) : void
    {
        $this->setPriceCoefficient1 = $value;
    }
    public function getSetPriceCoefficient1() : float
    {
        return $this->setPriceCoefficient1;
    }

    // Второй коэффициент для расчёта цены комплекта
    public function setSetPriceCoefficient2(float $value) : void
    {
        $this->setPriceCoefficient2 = $value;
    }
    public function getSetPriceCoefficient2() : float
    {
        return $this->setPriceCoefficient2;
    }

    // Массив подстановочных значений для генерации документа продукта
    public function setArProductItemWildcardValues(array $value) : void
    {
        $this->arProductItemWildcardValues = $value;
    }
    public function getArProductItemWildcardValues() : array
    {
        return $this->arProductItemWildcardValues;
    }

    // Массив подстановочных значений для генерации документа комплекта
    public function setArProductSetWildcardValues(array $value) : void
    {
        $this->arProductSetWildcardValues = $value;
    }
    public function getArProductSetWildcardValues() : array
    {
        return $this->arProductSetWildcardValues;
    }

    // Нужно ли конвертировать сформированный документ в PDF?
    public function setDoPdfTransform(bool $value) : void
    {
        $this->doPdfTransform = $value;
    }
    public function getDoPdfTransform() : bool
    {
        return $this->doPdfTransform;
    }

    // Нужно ли удалять старые фалы документов и опустевшие директории?
    public function setDoOldFilesDelete(bool $value) : void
    {
        $this->doOldFilesDelete = $value;
    }
    public function getDoOldFilesDelete() : bool
    {
        return $this->doOldFilesDelete;
    }
}
?>