<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\session;

use pocketmine\event\Listener;

/**
 * Interface for components that can be added to a player session
 */
interface PlayerSessionComponent extends Listener {
    /**
     * Get the unique identifier for this component
     * 
     * @return string The component ID
     */
    public function getId(): string;
    
    /**
     * Set the session this component belongs to
     * 
     * @param PlayerSession $session The session
     */
    public function setSession(PlayerSession $session): void;
    
    /**
     * Get the session this component belongs to
     * 
     * @return PlayerSession The session
     */
    public function getSession(): PlayerSession;
    
    /**
     * Called when the component is added to a session
     */
    public function onCreate(): void;
    
    /**
     * Called when the component is removed from a session
     */
    public function onRemove(): void;
}