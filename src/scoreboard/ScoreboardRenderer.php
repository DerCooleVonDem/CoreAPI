<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;

/**
 * Handles the rendering of scoreboards to players using network packets
 * This class manages the low-level packet sending for scoreboard display
 */
class ScoreboardRenderer {
    
    /** @var string */
    private const OBJECTIVE_NAME = "coreapi_scoreboard";
    
    /** @var string */
    private const DISPLAY_SLOT = "sidebar";
    
    /** @var array<string, bool> */
    private static array $activeScoreboards = [];
    
    /**
     * Display a scoreboard to a player
     * 
     * @param Player $player The player to display the scoreboard to
     * @param Scoreboard $scoreboard The scoreboard to display
     */
    public static function display(Player $player, Scoreboard $scoreboard): void {
        $playerId = $player->getUniqueId()->toString();
        
        // Remove existing scoreboard if present
        if (isset(self::$activeScoreboards[$playerId])) {
            self::remove($player);
        }
        
        // Create and send the objective
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = self::DISPLAY_SLOT;
        $pk->objectiveName = self::OBJECTIVE_NAME;
        $pk->displayName = $scoreboard->getTitle();
        $pk->criteriaName = "dummy";
        $pk->sortOrder = SetDisplayObjectivePacket::SORT_ORDER_ASCENDING;
        
        $player->getNetworkSession()->sendDataPacket($pk);
        
        // Render and send the lines
        $renderedLines = $scoreboard->render($player);
        self::updateLines($player, $renderedLines, $scoreboard->getTitle());
        
        // Mark as active
        self::$activeScoreboards[$playerId] = true;
    }
    
    /**
     * Update the lines of an active scoreboard
     *
     * @param Player $player The player whose scoreboard to update
     * @param array $renderedLines Array of rendered lines with text and score
     * @param string $title The scoreboard title
     */
    public static function updateLines(Player $player, array $renderedLines, string $title = "Scoreboard"): void {
        $playerId = $player->getUniqueId()->toString();

        if (!isset(self::$activeScoreboards[$playerId])) {
            return; // No active scoreboard
        }



        // Remove the old objective
        $removePk = new RemoveObjectivePacket();
        $removePk->objectiveName = self::OBJECTIVE_NAME;
        $player->getNetworkSession()->sendDataPacket($removePk);

        // Create a new objective with the same name
        $setPk = new SetDisplayObjectivePacket();
        $setPk->displaySlot = self::DISPLAY_SLOT;
        $setPk->objectiveName = self::OBJECTIVE_NAME;
        $setPk->displayName = $title;
        $setPk->criteriaName = "dummy";
        $setPk->sortOrder = SetDisplayObjectivePacket::SORT_ORDER_ASCENDING;
        $player->getNetworkSession()->sendDataPacket($setPk);

        // Add the new entries
        $entries = [];
        $lineNumber = 1;

        // Process lines in reverse order (bottom to top)
        $reversedLines = array_reverse($renderedLines);

        foreach ($reversedLines as $lineData) {
            if ($lineNumber > 15) {
                break; // Maximum 15 lines
            }

            $entry = new ScorePacketEntry();
            $entry->objectiveName = self::OBJECTIVE_NAME;
            $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entry->customName = $lineData['text'];
            $entry->score = $lineData['score'];
            $entry->scoreboardId = $lineNumber;

            $entries[] = $entry;
            $lineNumber++;
        }

        if (!empty($entries)) {
            $pk = new SetScorePacket();
            $pk->type = SetScorePacket::TYPE_CHANGE;
            $pk->entries = $entries;

            $player->getNetworkSession()->sendDataPacket($pk);


        }
    }
    
    /**
     * Remove the scoreboard from a player
     * 
     * @param Player $player The player to remove the scoreboard from
     */
    public static function remove(Player $player): void {
        $playerId = $player->getUniqueId()->toString();
        
        if (!isset(self::$activeScoreboards[$playerId])) {
            return; // No active scoreboard
        }
        
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = self::OBJECTIVE_NAME;
        
        $player->getNetworkSession()->sendDataPacket($pk);
        
        unset(self::$activeScoreboards[$playerId]);
    }
    
    /**
     * Check if a player has an active scoreboard
     * 
     * @param Player $player The player to check
     * @return bool True if the player has an active scoreboard
     */
    public static function hasActiveScoreboard(Player $player): bool {
        return isset(self::$activeScoreboards[$player->getUniqueId()->toString()]);
    }
    
    /**
     * Remove all active scoreboards (useful for cleanup)
     */
    public static function removeAll(): void {
        self::$activeScoreboards = [];
    }
}
