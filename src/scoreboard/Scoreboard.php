<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard;

use JonasWindmann\CoreAPI\manager\Manageable;
use pocketmine\player\Player;

/**
 * Represents a scoreboard that can be displayed to players
 * Implements Manageable to work with the manager system
 */
class Scoreboard implements Manageable {
    
    /** @var string */
    private string $id;
    
    /** @var string */
    private string $title;
    
    /** @var ScoreboardLine[] */
    private array $lines = [];
    
    /** @var ScoreboardTag[] */
    private array $tags = [];
    
    /** @var string */
    private string $ownerPlugin;
    
    /** @var int */
    private int $priority;
    
    /** @var bool */
    private bool $autoUpdate;
    
    /** @var int */
    private int $updateInterval;

    /** @var bool */
    private bool $autoDisplay;

    /**
     * Scoreboard constructor
     * 
     * @param string $id Unique identifier for this scoreboard
     * @param string $title The scoreboard title
     * @param string $ownerPlugin The plugin that owns this scoreboard
     * @param int $priority Priority for display (higher = more important)
     * @param bool $autoUpdate Whether to automatically update this scoreboard
     * @param int $updateInterval Update interval in ticks (only used if autoUpdate is true)
     * @param bool $autoDisplay Whether to automatically display this scoreboard to new players
     */
    public function __construct(
        string $id,
        string $title,
        string $ownerPlugin,
        int $priority = 0,
        bool $autoUpdate = true,
        int $updateInterval = 20,
        bool $autoDisplay = true
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->ownerPlugin = $ownerPlugin;
        $this->priority = $priority;
        $this->autoUpdate = $autoUpdate;
        $this->updateInterval = $updateInterval;
        $this->autoDisplay = $autoDisplay;
    }
    
    /**
     * Get the unique identifier
     * 
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }
    
    /**
     * Get the scoreboard title
     * 
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }
    
    /**
     * Set the scoreboard title
     * 
     * @param string $title
     */
    public function setTitle(string $title): void {
        $this->title = $title;
    }
    
    /**
     * Get the owner plugin name
     * 
     * @return string
     */
    public function getOwnerPlugin(): string {
        return $this->ownerPlugin;
    }
    
    /**
     * Get the priority
     * 
     * @return int
     */
    public function getPriority(): int {
        return $this->priority;
    }
    
    /**
     * Set the priority
     * 
     * @param int $priority
     */
    public function setPriority(int $priority): void {
        $this->priority = $priority;
    }
    
    /**
     * Check if auto-update is enabled
     * 
     * @return bool
     */
    public function isAutoUpdate(): bool {
        return $this->autoUpdate;
    }
    
    /**
     * Set auto-update status
     * 
     * @param bool $autoUpdate
     */
    public function setAutoUpdate(bool $autoUpdate): void {
        $this->autoUpdate = $autoUpdate;
    }
    
    /**
     * Get the update interval in ticks
     * 
     * @return int
     */
    public function getUpdateInterval(): int {
        return $this->updateInterval;
    }
    
    /**
     * Set the update interval in ticks
     *
     * @param int $updateInterval
     */
    public function setUpdateInterval(int $updateInterval): void {
        $this->updateInterval = $updateInterval;
    }

    /**
     * Check if auto-display is enabled
     *
     * @return bool
     */
    public function isAutoDisplay(): bool {
        return $this->autoDisplay;
    }

    /**
     * Set auto-display status
     *
     * @param bool $autoDisplay
     */
    public function setAutoDisplay(bool $autoDisplay): void {
        $this->autoDisplay = $autoDisplay;
    }
    
    /**
     * Add a line to the scoreboard
     * 
     * @param ScoreboardLine $line
     */
    public function addLine(ScoreboardLine $line): void {
        $this->lines[] = $line;
    }
    
    /**
     * Get all lines
     * 
     * @return ScoreboardLine[]
     */
    public function getLines(): array {
        return $this->lines;
    }
    
    /**
     * Set all lines
     * 
     * @param ScoreboardLine[] $lines
     */
    public function setLines(array $lines): void {
        $this->lines = $lines;
    }
    
    /**
     * Add a tag to the scoreboard
     * 
     * @param ScoreboardTag $tag
     */
    public function addTag(ScoreboardTag $tag): void {
        $this->tags[$tag->getName()] = $tag;
    }
    
    /**
     * Get all tags
     * 
     * @return ScoreboardTag[]
     */
    public function getTags(): array {
        return $this->tags;
    }
    
    /**
     * Get a specific tag by name
     * 
     * @param string $name
     * @return ScoreboardTag|null
     */
    public function getTag(string $name): ?ScoreboardTag {
        return $this->tags[$name] ?? null;
    }
    
    /**
     * Remove a tag by name
     * 
     * @param string $name
     * @return bool True if the tag was removed, false if it didn't exist
     */
    public function removeTag(string $name): bool {
        if (isset($this->tags[$name])) {
            unset($this->tags[$name]);
            return true;
        }
        return false;
    }
    
    /**
     * Render the scoreboard for a specific player
     * 
     * @param Player $player The player to render for
     * @return array Array of rendered lines with their scores
     */
    public function render(Player $player): array {
        $renderedLines = [];
        
        foreach ($this->lines as $line) {
            if ($line->isVisible()) {
                $renderedLines[] = [
                    'text' => $line->render($this->tags, $player),
                    'score' => $line->getScore()
                ];
            }
        }
        
        return $renderedLines;
    }
}
