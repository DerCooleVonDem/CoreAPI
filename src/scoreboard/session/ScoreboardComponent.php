<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\session;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\scoreboard\Scoreboard;
use JonasWindmann\CoreAPI\scoreboard\ScoreboardRenderer;
use JonasWindmann\CoreAPI\session\BasePlayerSessionComponent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\scheduler\ClosureTask;

/**
 * Session component that manages scoreboard display for individual players
 * Handles automatic updates and cleanup
 */
class ScoreboardComponent extends BasePlayerSessionComponent {
    
    /** @var Scoreboard|null */
    private ?Scoreboard $activeScoreboard = null;
    
    /** @var \pocketmine\scheduler\TaskHandler|null */
    private ?\pocketmine\scheduler\TaskHandler $updateTaskHandler = null;
    
    /**
     * Get the component identifier
     * 
     * @return string
     */
    public function getId(): string {
        return "scoreboard";
    }
    
    /**
     * Called when the component is created
     */
    public function onCreate(): void {
        // Component is ready, but no scoreboard is displayed yet
    }
    
    /**
     * Called when the component is removed
     */
    public function onRemove(): void {
        $this->hideScoreboard();
    }
    
    /**
     * Display a scoreboard to the player
     * 
     * @param Scoreboard $scoreboard The scoreboard to display
     */
    public function showScoreboard(Scoreboard $scoreboard): void {
        $player = $this->getPlayer();
        
        // Hide current scoreboard if any
        if ($this->activeScoreboard !== null) {
            $this->hideScoreboard();
        }
        
        // Set the new active scoreboard
        $this->activeScoreboard = $scoreboard;
        
        // Display the scoreboard
        ScoreboardRenderer::display($player, $scoreboard);

        // Debug: Log scoreboard display
        CoreAPI::getInstance()->getLogger()->debug("Displayed scoreboard '" . $scoreboard->getId() . "' to player: " . $player->getName());

        // Start auto-update if enabled
        if ($scoreboard->isAutoUpdate()) {
            CoreAPI::getInstance()->getLogger()->debug("Auto-update is enabled for scoreboard: " . $scoreboard->getId());
            $this->startAutoUpdate();
        } else {
            CoreAPI::getInstance()->getLogger()->debug("Auto-update is disabled for scoreboard: " . $scoreboard->getId());
        }
    }
    
    /**
     * Hide the current scoreboard
     */
    public function hideScoreboard(): void {
        if ($this->activeScoreboard === null) {
            return;
        }
        
        $player = $this->getPlayer();
        
        // Stop auto-update
        $this->stopAutoUpdate();
        
        // Remove the scoreboard
        ScoreboardRenderer::remove($player);
        
        // Clear the active scoreboard
        $this->activeScoreboard = null;
    }
    
    /**
     * Update the current scoreboard display
     */
    public function updateScoreboard(): void {
        if ($this->activeScoreboard === null) {
            return;
        }

        $player = $this->getPlayer();

        $renderedLines = $this->activeScoreboard->render($player);

        ScoreboardRenderer::updateLines($player, $renderedLines, $this->activeScoreboard->getTitle());
    }
    
    /**
     * Get the currently active scoreboard
     * 
     * @return Scoreboard|null
     */
    public function getActiveScoreboard(): ?Scoreboard {
        return $this->activeScoreboard;
    }
    
    /**
     * Check if a scoreboard is currently displayed
     * 
     * @return bool
     */
    public function hasActiveScoreboard(): bool {
        return $this->activeScoreboard !== null;
    }
    
    /**
     * Start automatic updates for the current scoreboard
     */
    private function startAutoUpdate(): void {
        if ($this->activeScoreboard === null || $this->updateTaskHandler !== null) {
            return;
        }

        $plugin = CoreAPI::getInstance();
        $interval = $this->activeScoreboard->getUpdateInterval();

        $this->updateTaskHandler = $plugin->getScheduler()->scheduleRepeatingTask(
            new ClosureTask(function() use ($plugin) : void {
                if ($this->activeScoreboard !== null && $this->getPlayer()->isOnline()) {
                    $this->updateScoreboard();
                } else {
                    $this->stopAutoUpdate();
                }
            }),
            $interval
        );


    }
    
    /**
     * Stop automatic updates
     */
    private function stopAutoUpdate(): void {
        if ($this->updateTaskHandler !== null) {
            $this->updateTaskHandler->cancel();
            $this->updateTaskHandler = null;
        }
    }
    
    /**
     * Handle player quit event to clean up
     * 
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void {
        if ($event->getPlayer() === $this->getPlayer()) {
            $this->hideScoreboard();
        }
    }
}
