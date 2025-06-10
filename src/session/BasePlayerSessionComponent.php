<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\session;

/**
 * Base implementation of PlayerSessionComponent
 * Provides common functionality for session components
 */
abstract class BasePlayerSessionComponent implements PlayerSessionComponent {
    /** @var PlayerSession|null */
    protected ?PlayerSession $session = null;
    
    /**
     * Set the session this component belongs to
     * 
     * @param PlayerSession $session The session
     */
    public function setSession(PlayerSession $session): void {
        $this->session = $session;
    }
    
    /**
     * Get the session this component belongs to
     * 
     * @return PlayerSession The session
     * @throws \RuntimeException If the session is not set
     */
    public function getSession(): PlayerSession {
        if ($this->session === null) {
            throw new \RuntimeException("Session not set for component " . $this->getId());
        }
        return $this->session;
    }
    
    /**
     * Get the player this component belongs to
     * Shorthand for getSession()->getPlayer()
     * 
     * @return \pocketmine\player\Player
     */
    public function getPlayer(): \pocketmine\player\Player {
        return $this->getSession()->getPlayer();
    }
    
    /**
     * Called when the component is added to a session
     * Override this method to implement custom initialization
     */
    public function onCreate(): void {
        // Default implementation does nothing
    }
    
    /**
     * Called when the component is removed from a session
     * Override this method to implement custom cleanup
     */
    public function onRemove(): void {
        // Default implementation does nothing
    }
}