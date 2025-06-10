<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\factory;

use JonasWindmann\CoreAPI\scoreboard\Scoreboard;
use JonasWindmann\CoreAPI\scoreboard\ScoreboardLine;
use JonasWindmann\CoreAPI\scoreboard\ScoreboardTag;

/**
 * Factory class for creating scoreboards with common configurations
 * Provides convenient methods for creating different types of scoreboards
 */
class ScoreboardFactory {
    
    /**
     * Create a basic scoreboard with default settings
     *
     * @param string $id Unique identifier
     * @param string $title Scoreboard title
     * @param string $ownerPlugin Owner plugin name
     * @param bool $autoDisplay Whether to automatically display to new players
     * @return Scoreboard
     */
    public static function createBasic(string $id, string $title, string $ownerPlugin, bool $autoDisplay = true): Scoreboard {
        return new Scoreboard($id, $title, $ownerPlugin, 0, true, 20, $autoDisplay);
    }
    
    /**
     * Create a scoreboard with custom priority and update settings
     *
     * @param string $id Unique identifier
     * @param string $title Scoreboard title
     * @param string $ownerPlugin Owner plugin name
     * @param int $priority Display priority
     * @param bool $autoUpdate Whether to auto-update
     * @param int $updateInterval Update interval in ticks
     * @param bool $autoDisplay Whether to automatically display to new players
     * @return Scoreboard
     */
    public static function createCustom(
        string $id,
        string $title,
        string $ownerPlugin,
        int $priority = 0,
        bool $autoUpdate = true,
        int $updateInterval = 20,
        bool $autoDisplay = true
    ): Scoreboard {
        return new Scoreboard($id, $title, $ownerPlugin, $priority, $autoUpdate, $updateInterval, $autoDisplay);
    }
    
    /**
     * Create a scoreboard with predefined lines
     *
     * @param string $id Unique identifier
     * @param string $title Scoreboard title
     * @param string $ownerPlugin Owner plugin name
     * @param array $lineTemplates Array of line templates
     * @param bool $autoDisplay Whether to automatically display to new players
     * @return Scoreboard
     */
    public static function createWithLines(string $id, string $title, string $ownerPlugin, array $lineTemplates, bool $autoDisplay = true): Scoreboard {
        $scoreboard = new Scoreboard($id, $title, $ownerPlugin, 0, true, 20, $autoDisplay);

        $score = count($lineTemplates);
        foreach ($lineTemplates as $template) {
            $scoreboard->addLine(new ScoreboardLine($template, $score--));
        }

        return $scoreboard;
    }
    
    /**
     * Create a player information scoreboard with common tags
     *
     * @param string $id Unique identifier
     * @param string $title Scoreboard title
     * @param string $ownerPlugin Owner plugin name
     * @param bool $autoDisplay Whether to automatically display to new players
     * @return Scoreboard
     */
    public static function createPlayerInfo(string $id, string $title, string $ownerPlugin, bool $autoDisplay = true): Scoreboard {
        $scoreboard = new Scoreboard($id, $title, $ownerPlugin, 0, true, 20, $autoDisplay);
        
        // Add common player tags
        $scoreboard->addTag(new ScoreboardTag("player", function($player) {
            return $player->getName();
        }, "Player name"));
        
        $scoreboard->addTag(new ScoreboardTag("health", function($player) {
            return (string) round($player->getHealth(), 1);
        }, "Player health"));
        
        $scoreboard->addTag(new ScoreboardTag("food", function($player) {
            return (string) $player->getHungerManager()->getFood();
        }, "Player food level"));
        
        $scoreboard->addTag(new ScoreboardTag("level", function($player) {
            return (string) $player->getXpManager()->getXpLevel();
        }, "Player experience level"));
        
        $scoreboard->addTag(new ScoreboardTag("world", function($player) {
            return $player->getWorld()->getFolderName();
        }, "Current world name"));
        
        $scoreboard->addTag(new ScoreboardTag("x", function($player) {
            return (string) (int) $player->getPosition()->getX();
        }, "Player X coordinate"));
        
        $scoreboard->addTag(new ScoreboardTag("y", function($player) {
            return (string) (int) $player->getPosition()->getY();
        }, "Player Y coordinate"));
        
        $scoreboard->addTag(new ScoreboardTag("z", function($player) {
            return (string) (int) $player->getPosition()->getZ();
        }, "Player Z coordinate"));
        
        return $scoreboard;
    }
    
    /**
     * Create a server information scoreboard with common server tags
     *
     * @param string $id Unique identifier
     * @param string $title Scoreboard title
     * @param string $ownerPlugin Owner plugin name
     * @param bool $autoDisplay Whether to automatically display to new players
     * @return Scoreboard
     */
    public static function createServerInfo(string $id, string $title, string $ownerPlugin, bool $autoDisplay = true): Scoreboard {
        $scoreboard = new Scoreboard($id, $title, $ownerPlugin, 0, true, 20, $autoDisplay);
        
        // Add common server tags
        $scoreboard->addTag(new ScoreboardTag("online", function() {
            return (string) count(\pocketmine\Server::getInstance()->getOnlinePlayers());
        }, "Online player count"));
        
        $scoreboard->addTag(new ScoreboardTag("max_players", function() {
            return (string) \pocketmine\Server::getInstance()->getMaxPlayers();
        }, "Maximum player count"));
        
        $scoreboard->addTag(new ScoreboardTag("tps", function() {
            return (string) round(\pocketmine\Server::getInstance()->getTicksPerSecond(), 1);
        }, "Server TPS"));
        
        $scoreboard->addTag(new ScoreboardTag("load", function() {
            return (string) round(\pocketmine\Server::getInstance()->getTickUsage(), 1);
        }, "Server load percentage"));
        
        $scoreboard->addTag(new ScoreboardTag("time", function() {
            return date("H:i:s");
        }, "Current time"));
        
        $scoreboard->addTag(new ScoreboardTag("date", function() {
            return date("Y-m-d");
        }, "Current date"));
        
        return $scoreboard;
    }
}
