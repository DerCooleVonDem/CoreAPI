<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard;

use JonasWindmann\CoreAPI\manager\BaseManager;
use JonasWindmann\CoreAPI\scoreboard\session\ScoreboardComponent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;

/**
 * Manages scoreboards for the CoreAPI system
 * Handles registration, retrieval, and display of scoreboards
 * Automatically displays scoreboards to new players based on priority
 */
class ScoreboardManager extends BaseManager implements Listener {
    
    /**
     * ScoreboardManager constructor
     *
     * @param Plugin $plugin The plugin instance
     */
    public function __construct(Plugin $plugin) {
        parent::__construct($plugin);

        // Register event listeners for automatic display
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }
    
    /**
     * Register a scoreboard
     * 
     * @param Scoreboard $scoreboard The scoreboard to register
     * @return bool True if registered successfully, false if ID already exists
     */
    public function registerScoreboard(Scoreboard $scoreboard): bool {
        return $this->addItem($scoreboard);
    }
    
    /**
     * Unregister a scoreboard by ID
     * 
     * @param string $id The scoreboard ID
     * @return bool True if unregistered successfully, false if not found
     */
    public function unregisterScoreboard(string $id): bool {
        return $this->removeItem($id);
    }
    
    /**
     * Get a scoreboard by ID
     * 
     * @param string $id The scoreboard ID
     * @return Scoreboard|null The scoreboard if found, null otherwise
     */
    public function getScoreboard(string $id): ?Scoreboard {
        $item = $this->getItem($id);
        return $item instanceof Scoreboard ? $item : null;
    }
    
    /**
     * Get all registered scoreboards
     * 
     * @return Scoreboard[]
     */
    public function getScoreboards(): array {
        return array_filter($this->getItems(), function($item) {
            return $item instanceof Scoreboard;
        });
    }
    
    /**
     * Get scoreboards owned by a specific plugin
     * 
     * @param string $pluginName The plugin name
     * @return Scoreboard[]
     */
    public function getScoreboardsByPlugin(string $pluginName): array {
        return array_filter($this->getScoreboards(), function(Scoreboard $scoreboard) use ($pluginName) {
            return $scoreboard->getOwnerPlugin() === $pluginName;
        });
    }
    
    /**
     * Display a scoreboard to a player
     * 
     * @param Player $player The player to display the scoreboard to
     * @param string $scoreboardId The ID of the scoreboard to display
     * @return bool True if displayed successfully, false if scoreboard not found or player has no session
     */
    public function displayScoreboard(Player $player, string $scoreboardId): bool {
        $scoreboard = $this->getScoreboard($scoreboardId);
        if ($scoreboard === null) {
            return false;
        }
        
        return $this->displayScoreboardDirect($player, $scoreboard);
    }
    
    /**
     * Display a scoreboard object directly to a player
     *
     * @param Player $player The player to display the scoreboard to
     * @param Scoreboard $scoreboard The scoreboard to display
     * @return bool True if displayed successfully, false if player has no session
     */
    public function displayScoreboardDirect(Player $player, Scoreboard $scoreboard): bool {
        $sessionManager = $this->plugin->getServer()->getPluginManager()->getPlugin("CoreAPI")?->getSessionManager();
        if ($sessionManager === null) {
            $this->plugin->getLogger()->info("SessionManager not found for " . $player->getName());
            return false;
        }

        $session = $sessionManager->getSessionByPlayer($player);
        if ($session === null) {
            $this->plugin->getLogger()->info("No session found for " . $player->getName());
            return false;
        }

        $component = $session->getComponent("scoreboard");
        if (!$component instanceof ScoreboardComponent) {
            $this->plugin->getLogger()->info("No scoreboard component found for " . $player->getName());
            return false;
        }

        $this->plugin->getLogger()->info("Displaying scoreboard " . $scoreboard->getId() . " to " . $player->getName());
        $component->showScoreboard($scoreboard);
        return true;
    }
    
    /**
     * Hide the scoreboard from a player
     * 
     * @param Player $player The player to hide the scoreboard from
     * @return bool True if hidden successfully, false if player has no session
     */
    public function hideScoreboard(Player $player): bool {
        $sessionManager = $this->plugin->getServer()->getPluginManager()->getPlugin("CoreAPI")?->getSessionManager();
        if ($sessionManager === null) {
            return false;
        }
        
        $session = $sessionManager->getSessionByPlayer($player);
        if ($session === null) {
            return false;
        }
        
        $component = $session->getComponent("scoreboard");
        if (!$component instanceof ScoreboardComponent) {
            return false;
        }
        
        $component->hideScoreboard();
        return true;
    }
    
    /**
     * Get the highest priority auto-display scoreboard
     *
     * @return Scoreboard|null The highest priority auto-display scoreboard, or null if none exist
     */
    public function getHighestPriorityAutoDisplayScoreboard(): ?Scoreboard {
        $scoreboards = array_filter($this->getScoreboards(), function(Scoreboard $scoreboard) {
            return $scoreboard->isAutoDisplay();
        });

        if (empty($scoreboards)) {
            return null;
        }

        usort($scoreboards, function(Scoreboard $a, Scoreboard $b) {
            return $b->getPriority() <=> $a->getPriority();
        });

        return $scoreboards[0];
    }

    /**
     * Get the highest priority scoreboard (including non-auto-display ones)
     *
     * @return Scoreboard|null The highest priority scoreboard, or null if none exist
     */
    public function getHighestPriorityScoreboard(): ?Scoreboard {
        $scoreboards = $this->getScoreboards();
        if (empty($scoreboards)) {
            return null;
        }

        usort($scoreboards, function(Scoreboard $a, Scoreboard $b) {
            return $b->getPriority() <=> $a->getPriority();
        });

        return $scoreboards[0];
    }
    
    /**
     * Update a specific scoreboard for all players currently viewing it
     * 
     * @param string $scoreboardId The ID of the scoreboard to update
     */
    public function updateScoreboard(string $scoreboardId): void {
        $sessionManager = $this->plugin->getServer()->getPluginManager()->getPlugin("CoreAPI")?->getSessionManager();
        if ($sessionManager === null) {
            return;
        }
        
        foreach ($sessionManager->getItems() as $session) {
            $component = $session->getComponent("scoreboard");
            if ($component instanceof ScoreboardComponent) {
                $activeScoreboard = $component->getActiveScoreboard();
                if ($activeScoreboard !== null && $activeScoreboard->getId() === $scoreboardId) {
                    $component->updateScoreboard();
                }
            }
        }
    }
    
    /**
     * Automatically display the highest priority scoreboard to a player
     *
     * @param Player $player The player to display the scoreboard to
     * @return bool True if a scoreboard was displayed, false otherwise
     */
    public function autoDisplayScoreboard(Player $player): bool {
        $scoreboard = $this->getHighestPriorityAutoDisplayScoreboard();
        if ($scoreboard === null) {
            $this->plugin->getLogger()->info("No auto-display scoreboard found for " . $player->getName());
            return false;
        }

        $this->plugin->getLogger()->info("Found auto-display scoreboard: " . $scoreboard->getId() . " for " . $player->getName());
        return $this->displayScoreboardDirect($player, $scoreboard);
    }

    /**
     * Handle player join event for automatic scoreboard display
     *
     * @param PlayerJoinEvent $event
     * @priority NORMAL
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();

        // Log that player joined
        $this->plugin->getLogger()->info("Player " . $player->getName() . " joined, scheduling scoreboard display...");

        // Schedule the auto-display after a short delay to ensure session is created
        $this->plugin->getScheduler()->scheduleDelayedTask(
            new ClosureTask(function() use ($player): void {
                if ($player->isOnline()) {
                    $this->plugin->getLogger()->info("Attempting to auto-display scoreboard for " . $player->getName());
                    $result = $this->autoDisplayScoreboard($player);
                    $this->plugin->getLogger()->info("Auto-display result for " . $player->getName() . ": " . ($result ? "success" : "failed"));
                }
            }),
            20 // 1 second delay
        );
    }

    /**
     * Clean up scoreboards owned by a specific plugin
     * Useful when a plugin is disabled
     *
     * @param string $pluginName The plugin name
     */
    public function cleanupPlugin(string $pluginName): void {
        $scoreboards = $this->getScoreboardsByPlugin($pluginName);
        foreach ($scoreboards as $scoreboard) {
            $this->unregisterScoreboard($scoreboard->getId());
        }
    }
}
