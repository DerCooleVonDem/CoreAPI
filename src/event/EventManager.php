<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\event;

use Closure;
use JonasWindmann\CoreAPI\manager\BaseManager;
use pocketmine\plugin\Plugin;

/**
 * Manager for closure-based events
 */
class EventManager extends BaseManager {
    /**
     * EventManager constructor
     * 
     * @param Plugin $plugin The plugin instance
     */
    public function __construct(Plugin $plugin) {
        parent::__construct($plugin);
    }
    
    /**
     * Register a new event
     * 
     * @param string $eventId The unique identifier for the event
     * @return Event The created event
     */
    public function registerEvent(string $eventId): Event {
        $event = new Event($eventId);
        $this->addItem($event);
        return $event;
    }
    
    /**
     * Unregister an event
     * 
     * @param string $eventId The ID of the event to unregister
     * @return bool True if the event was unregistered, false if it wasn't found
     */
    public function unregisterEvent(string $eventId): bool {
        return $this->removeItem($eventId);
    }
    
    /**
     * Get an event by ID
     * 
     * @param string $eventId The ID of the event
     * @return Event|null The event if found, null otherwise
     */
    public function getEvent(string $eventId): ?Event {
        $event = $this->getItem($eventId);
        return $event instanceof Event ? $event : null;
    }
    
    /**
     * Register a handler for an event
     * 
     * @param string $eventId The ID of the event
     * @param Closure $handler The handler to register
     * @return string|null The handler ID if the event exists, null otherwise
     */
    public function registerHandler(string $eventId, Closure $handler): ?string {
        $event = $this->getEvent($eventId);
        if ($event === null) {
            return null;
        }
        
        return $event->addHandler($handler);
    }
    
    /**
     * Unregister a handler from an event
     * 
     * @param string $eventId The ID of the event
     * @param string $handlerId The ID of the handler to unregister
     * @return bool True if the handler was unregistered, false if the event or handler wasn't found
     */
    public function unregisterHandler(string $eventId, string $handlerId): bool {
        $event = $this->getEvent($eventId);
        if ($event === null) {
            return false;
        }
        
        return $event->removeHandler($handlerId);
    }
    
    /**
     * Trigger an event with the given parameters
     * 
     * @param string $eventId The ID of the event to trigger
     * @param mixed ...$params The parameters to pass to the handlers
     * @return array|null The results from all handlers if the event exists, null otherwise
     */
    public function triggerEvent(string $eventId, mixed ...$params): ?array {
        $event = $this->getEvent($eventId);
        if ($event === null) {
            return null;
        }
        
        return $event->trigger(...$params);
    }
    
    /**
     * Check if an event has any handlers
     * 
     * @param string $eventId The ID of the event
     * @return bool True if the event has handlers, false otherwise or if the event wasn't found
     */
    public function hasHandlers(string $eventId): bool {
        $event = $this->getEvent($eventId);
        if ($event === null) {
            return false;
        }
        
        return $event->hasHandlers();
    }
}