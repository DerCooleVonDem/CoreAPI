<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\event;

use Closure;
use JonasWindmann\CoreAPI\manager\Manageable;

/**
 * Represents an event that can have closure handlers attached to it
 */
class Event implements Manageable {
    /** @var string */
    private string $id;
    
    /** @var Closure[] */
    private array $handlers = [];
    
    /**
     * Event constructor
     * 
     * @param string $id The unique identifier for this event
     */
    public function __construct(string $id) {
        $this->id = $id;
    }
    
    /**
     * Get the unique identifier for this event
     * 
     * @return string The unique identifier
     */
    public function getId(): string {
        return $this->id;
    }
    
    /**
     * Add a handler for this event
     * 
     * @param Closure $handler The handler to add
     * @return string The handler ID
     */
    public function addHandler(Closure $handler): string {
        $handlerId = uniqid('handler_');
        $this->handlers[$handlerId] = $handler;
        return $handlerId;
    }
    
    /**
     * Remove a handler from this event
     * 
     * @param string $handlerId The ID of the handler to remove
     * @return bool True if the handler was removed, false if it wasn't found
     */
    public function removeHandler(string $handlerId): bool {
        if (!isset($this->handlers[$handlerId])) {
            return false;
        }
        
        unset($this->handlers[$handlerId]);
        return true;
    }
    
    /**
     * Trigger this event with the given parameters
     * 
     * @param mixed ...$params The parameters to pass to the handlers
     * @return array The results from all handlers
     */
    public function trigger(mixed ...$params): array {
        $results = [];
        
        foreach ($this->handlers as $handlerId => $handler) {
            $results[$handlerId] = $handler(...$params);
        }
        
        return $results;
    }
    
    /**
     * Get all handlers for this event
     * 
     * @return Closure[]
     */
    public function getHandlers(): array {
        return $this->handlers;
    }
    
    /**
     * Check if this event has any handlers
     * 
     * @return bool True if the event has handlers, false otherwise
     */
    public function hasHandlers(): bool {
        return !empty($this->handlers);
    }
}