<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\manager;

use pocketmine\plugin\Plugin;

/**
 * Base class for managers that handle collections of objects
 */
abstract class BaseManager {
    /** @var array<string|int, Manageable> */
    protected array $items = [];
    
    /** @var Plugin */
    protected Plugin $plugin;
    
    /**
     * BaseManager constructor
     * 
     * @param Plugin $plugin The plugin instance
     */
    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
    }
    
    /**
     * Add an item to the manager
     * 
     * @param Manageable $item The item to add
     * @return bool True if the item was added, false if it already exists
     */
    public function addItem(Manageable $item): bool {
        $id = $item->getId();
        if (isset($this->items[$id])) {
            return false;
        }
        
        $this->items[$id] = $item;
        return true;
    }
    
    /**
     * Get an item by ID
     * 
     * @param string|int $id The ID of the item
     * @return Manageable|null The item if found, null otherwise
     */
    public function getItem(string|int $id): ?Manageable {
        return $this->items[$id] ?? null;
    }
    
    /**
     * Remove an item from the manager
     * 
     * @param string|int $id The ID of the item to remove
     * @return bool True if the item was removed, false if it wasn't found
     */
    public function removeItem(string|int $id): bool {
        if (!isset($this->items[$id])) {
            return false;
        }
        
        unset($this->items[$id]);
        return true;
    }
    
    /**
     * Get all items managed by this manager
     * 
     * @return array<string|int, Manageable>
     */
    public function getItems(): array {
        return $this->items;
    }
    
    /**
     * Get the plugin instance
     * 
     * @return Plugin
     */
    public function getPlugin(): Plugin {
        return $this->plugin;
    }
    
    /**
     * Load items from storage
     * 
     * @return void
     */
    abstract public function loadItems(): void;
    
    /**
     * Save items to storage
     * 
     * @return void
     */
    abstract public function saveItems(): void;
}