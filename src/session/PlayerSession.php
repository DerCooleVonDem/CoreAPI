<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\session;

use JonasWindmann\CoreAPI\manager\Manageable;
use pocketmine\player\Player;

/**
 * Represents a session for a player
 * Created when a player joins and removed when they leave
 */
class PlayerSession implements Manageable {
    /** @var Player */
    private Player $player;
    
    /** @var PlayerSessionComponent[] */
    private array $components = [];
    
    /**
     * PlayerSession constructor
     * 
     * @param Player $player The player this session belongs to
     */
    public function __construct(Player $player) {
        $this->player = $player;
    }
    
    /**
     * Get the player this session belongs to
     * 
     * @return Player
     */
    public function getPlayer(): Player {
        return $this->player;
    }
    
    /**
     * Get the unique identifier for this session
     * 
     * @return string The player's unique ID
     */
    public function getId(): string {
        return $this->player->getUniqueId()->toString();
    }
    
    /**
     * Add a component to this session
     * 
     * @param PlayerSessionComponent $component The component to add
     * @return bool True if the component was added, false if it already exists
     */
    public function addComponent(PlayerSessionComponent $component): bool {
        $id = $component->getId();
        if (isset($this->components[$id])) {
            return false;
        }
        
        $this->components[$id] = $component;
        $component->setSession($this);
        $component->onCreate();
        return true;
    }
    
    /**
     * Get a component by ID
     * 
     * @param string $id The ID of the component
     * @return PlayerSessionComponent|null The component if found, null otherwise
     */
    public function getComponent(string $id): ?PlayerSessionComponent {
        return $this->components[$id] ?? null;
    }
    
    /**
     * Get all components
     * 
     * @return PlayerSessionComponent[]
     */
    public function getComponents(): array {
        return $this->components;
    }
    
    /**
     * Called when the session is being destroyed
     * Calls onRemove on all components
     */
    public function destroy(): void {
        foreach ($this->components as $component) {
            $component->onRemove();
        }
        $this->components = [];
    }

    public function removeComponent(string $string)
    {
        if (isset($this->components[$string])) {
            $this->components[$string]->onRemove();
            unset($this->components[$string]);
        }

        return false;
    }

    public function hasComponent(string $string)
    {
        return isset($this->components[$string]);
    }
}