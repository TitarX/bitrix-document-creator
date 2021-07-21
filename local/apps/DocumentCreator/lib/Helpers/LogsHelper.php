<?php
namespace InfoExpert\DocumentCreator\Helpers;

use \InfoExpert\DocumentCreator\Helpers\SettingsHelper;

class LogsHelper
{
    private const MAX_LOG_FILE_SIZE = 1048576;

    private static function checkLogsFileForClear(string $filePath) : void
    {
        if(file_exists($filePath) && is_file($filePath)) {
            if(filesize($filePath) >= self::MAX_LOG_FILE_SIZE) {
                unlink($filePath);
            }
        }
    }

    public static function addLog(string $messageSourceFilePath, string $message, string $logFileName) : void
    {
        $settingsHelper = SettingsHelper::getInstance();
        $logFilePath = $settingsHelper->getLogsDirPath();
        if(!empty($logFilePath)) {
            $logFilePath = "{$logFilePath}/{$logFileName}";
    
            self::checkLogsFileForClear($logFilePath);
    
            $currentDateTimeString = date('Y.m.d H:i:s', time());
            $logMessage = "[{$currentDateTimeString}] - [{$messageSourceFilePath}] - [{$message}]";
    
            file_put_contents($logFilePath, $logMessage, FILE_APPEND);
            file_put_contents($logFilePath, PHP_EOL, FILE_APPEND);
            file_put_contents($logFilePath, PHP_EOL, FILE_APPEND);
        }
    }
}
?>