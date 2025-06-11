<?php

namespace JonasWindmann\CoreAPI\utils;

use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\generator\Flat;
use pocketmine\world\WorldCreationOptions;
use pocketmine\utils\TextFormat;

class WorldUtils
{
    public static function getAllWorldFolders(): array
    {
        $worlds = [];
        $worldPath = Server::getInstance()->getDataPath() . "worlds";
        $worlds = glob($worldPath . "/*", GLOB_ONLYDIR);
        return $worlds;
    }

    public static function generateWorld(string $worldName, string $generatorClass = Flat::class)
    {
        $options = new WorldCreationOptions();
        $options->setGeneratorClass($generatorClass);
        $options->setSpawnPosition(new Vector3(0, 5, 0));
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $options);
        return Server::getInstance()->getWorldManager()->loadWorld($worldName);
    }

    public static function loadWorld(string $worldName)
    {
        return Server::getInstance()->getWorldManager()->loadWorld($worldName);
    }

    public static function unloadWorld(string $worldName): bool
    {
        try {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
            if ($world === null) {
                Server::getInstance()->getLogger()->warning(TextFormat::YELLOW . "World '$worldName' not found for unloading");
                return true; // Consider it successful if world doesn't exist
            }

            return Server::getInstance()->getWorldManager()->unloadWorld($world);
        } catch (\Exception $e) {
            Server::getInstance()->getLogger()->error(TextFormat::RED . "Error unloading world '$worldName': " . $e->getMessage());
            return false;
        }
    }

    public static function deleteWorld(string $worldName): bool
    {
        try {
            // loaded check
            if (Server::getInstance()->getWorldManager()->isWorldLoaded($worldName)) {
                if (!self::unloadWorld($worldName)) {
                    Server::getInstance()->getLogger()->error(TextFormat::RED . "Failed to unload world: " . $worldName);
                    return false;
                }

                // Give the system time to release file handles
                usleep(100000); // 100ms delay
            }

            // delete folder
            $worldPath = Server::getInstance()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $worldName;
            if (is_dir($worldPath)) {
                return self::deleteDir($worldPath);
            }

            return true;
        } catch (\Exception $e) {
            Server::getInstance()->getLogger()->error(TextFormat::RED . "Error deleting world '$worldName': " . $e->getMessage());
            return false;
        }
    }

    private static function deleteDir(string $dirPath): bool
    {
        if (!is_dir($dirPath)) {
            Server::getInstance()->getLogger()->warning(TextFormat::YELLOW . "Directory does not exist: " . $dirPath);
            return true; // Consider it successful if it doesn't exist
        }

        // Normalize directory path
        $dirPath = rtrim($dirPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        try {
            // Get all files and directories
            $items = scandir($dirPath);
            if ($items === false) {
                Server::getInstance()->getLogger()->error(TextFormat::RED . "Cannot read directory: " . $dirPath);
                return false;
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $itemPath = $dirPath . $item;

                if (is_dir($itemPath)) {
                    // Recursively delete subdirectory
                    if (!self::deleteDir($itemPath)) {
                        return false;
                    }
                } else {
                    // Delete file with retry logic for Windows
                    if (!self::deleteFile($itemPath)) {
                        return false;
                    }
                }
            }

            // Finally, remove the directory itself
            return self::removeDirectory($dirPath);

        } catch (\Exception $e) {
            Server::getInstance()->getLogger()->error(TextFormat::RED . "Error deleting directory '$dirPath': " . $e->getMessage());
            return false;
        }
    }

    public static function getWorldByName(string $worldName)
    {
        return Server::getInstance()->getWorldManager()->getWorldByName($worldName);
    }

    /**
     * Delete a file with retry logic for Windows file locking issues
     *
     * @param string $filePath The file path to delete
     * @return bool True if successful, false otherwise
     */
    private static function deleteFile(string $filePath): bool
    {
        $maxRetries = 3;
        $retryDelay = 50000; // 50ms in microseconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Check if file is writable before attempting deletion
                if (!is_writable($filePath)) {
                    // Try to make it writable
                    if (!chmod($filePath, 0666)) {
                        Server::getInstance()->getLogger()->warning(TextFormat::YELLOW . "Cannot make file writable: " . $filePath);
                    }
                }

                if (unlink($filePath)) {
                    return true;
                }
            } catch (\Exception $e) {
                if ($attempt === $maxRetries) {
                    Server::getInstance()->getLogger()->error(TextFormat::RED . "Failed to delete file '$filePath' after $maxRetries attempts: " . $e->getMessage());
                    return false;
                }

                // Wait before retrying
                usleep($retryDelay);
                $retryDelay *= 2; // Exponential backoff
            }
        }

        Server::getInstance()->getLogger()->error(TextFormat::RED . "Failed to delete file: " . $filePath);
        return false;
    }

    /**
     * Remove a directory with retry logic
     *
     * @param string $dirPath The directory path to remove
     * @return bool True if successful, false otherwise
     */
    private static function removeDirectory(string $dirPath): bool
    {
        $maxRetries = 3;
        $retryDelay = 100000; // 100ms in microseconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                if (rmdir($dirPath)) {
                    return true;
                }
            } catch (\Exception $e) {
                if ($attempt === $maxRetries) {
                    Server::getInstance()->getLogger()->error(TextFormat::RED . "Failed to remove directory '$dirPath' after $maxRetries attempts: " . $e->getMessage());
                    return false;
                }

                // Wait before retrying
                usleep($retryDelay);
                $retryDelay *= 2; // Exponential backoff
            }
        }

        Server::getInstance()->getLogger()->error(TextFormat::RED . "Failed to remove directory: " . $dirPath);
        return false;
    }
}