<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\manager;

use pocketmine\plugin\Plugin;

/**
 * Generic type-safe manager implementation
 * Addresses UX issue 4.b - No Type Safety for Managed Items
 * 
 * @template T of Manageable
 */
abstract class TypedManager {
    /** @var array<string|int, T> */
    protected array $items = [];
    
    /** @var Plugin */
    protected Plugin $plugin;
    
    /**
     * TypedManager constructor
     * 
     * @param Plugin $plugin The plugin instance
     */
    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
    }
    
    /**
     * Add an item to the manager
     * 
     * @param T $item The item to add
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
     * @return T|null The item if found, null otherwise
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
     * @return array<string|int, T>
     */
    public function getItems(): array {
        return $this->items;
    }
    
    /**
     * Check if an item exists
     * 
     * @param string|int $id The ID of the item
     * @return bool True if the item exists, false otherwise
     */
    public function hasItem(string|int $id): bool {
        return isset($this->items[$id]);
    }
    
    /**
     * Get the number of items in the manager
     * 
     * @return int
     */
    public function count(): int {
        return count($this->items);
    }
    
    /**
     * Clear all items from the manager
     */
    public function clear(): void {
        $this->items = [];
    }
    
    /**
     * Get items that match a predicate
     * 
     * @param callable $predicate Function that takes (T $item): bool
     * @return array<string|int, T>
     */
    public function filter(callable $predicate): array {
        return array_filter($this->items, $predicate);
    }
    
    /**
     * Find the first item that matches a predicate
     * 
     * @param callable $predicate Function that takes (T $item): bool
     * @return T|null The first matching item or null
     */
    public function find(callable $predicate): ?Manageable {
        foreach ($this->items as $item) {
            if ($predicate($item)) {
                return $item;
            }
        }
        return null;
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
     * Override this method if your manager needs to load items from persistent storage
     * 
     * @return void
     */
    public function loadItems(): void {
        // Default implementation does nothing
        // Override this method if persistence is needed
    }
    
    /**
     * Save items to storage
     * Override this method if your manager needs to save items to persistent storage
     * 
     * @return void
     */
    public function saveItems(): void {
        // Default implementation does nothing
        // Override this method if persistence is needed
    }
}
