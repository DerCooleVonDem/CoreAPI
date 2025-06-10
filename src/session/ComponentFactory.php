<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\session;

/**
 * Factory interface for creating session components
 * Addresses UX issue 3.a - Component Cloning Confusion
 */
interface ComponentFactory {
    /**
     * Create a new instance of the component
     * 
     * @return PlayerSessionComponent
     */
    public function create(): PlayerSessionComponent;

    /**
     * Get the component ID
     * 
     * @return string
     */
    public function getId(): string;
}
