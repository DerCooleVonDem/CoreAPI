<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item;

use pocketmine\item\Item;
use pocketmine\utils\Config;

/**
 * Registry for managing custom item types
 * Handles persistence and validation of custom items
 */
class CustomItemRegistry
{
    private Config $config;
    private string $namespace;
    private array $registeredTypes = [];
    private string $dataPath;

    public function __construct(Config $config, string $namespace = "CoreAPI")
    {
        $this->config = $config;
        $this->namespace = $namespace;
        $this->dataPath = dirname($config->getPath()) . "/custom_items_registry.yml";
    }

    /**
     * Register a custom item type
     * 
     * @param CustomItem $customItem
     * @return bool
     */
    public function registerType(CustomItem $customItem): bool
    {
        $maxTypes = $this->config->get("custom_items.max_types", 100);

        if (count($this->registeredTypes) >= $maxTypes) {
            return false;
        }

        $allowDuplicates = $this->config->get("custom_items.allow_duplicate_names", false);
        if (!$allowDuplicates && $this->hasTypeByName($customItem->getName())) {
            return false;
        }

        $this->registeredTypes[$customItem->getId()] = $customItem;
        $this->saveRegisteredTypes();

        return true;
    }

    /**
     * Unregister a custom item type
     * 
     * @param string $id
     * @return bool
     */
    public function unregisterType(string $id): bool
    {
        if (!isset($this->registeredTypes[$id])) {
            return false;
        }

        unset($this->registeredTypes[$id]);
        $this->saveRegisteredTypes();

        return true;
    }

    /**
     * Check if a type exists by ID
     * 
     * @param string $id
     * @return bool
     */
    public function hasType(string $id): bool
    {
        return isset($this->registeredTypes[$id]);
    }

    /**
     * Check if a type exists by name
     * 
     * @param string $name
     * @return bool
     */
    public function hasTypeByName(string $name): bool
    {
        foreach ($this->registeredTypes as $customItem) {
            if ($customItem->getName() === $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get a custom item type by ID
     * 
     * @param string $id
     * @return CustomItem|null
     */
    public function getType(string $id): ?CustomItem
    {
        return $this->registeredTypes[$id] ?? null;
    }

    /**
     * Get a custom item type by name
     * 
     * @param string $name
     * @return CustomItem|null
     */
    public function getTypeByName(string $name): ?CustomItem
    {
        foreach ($this->registeredTypes as $customItem) {
            if ($customItem->getName() === $name) {
                return $customItem;
            }
        }
        return null;
    }

    /**
     * Get all registered types
     * 
     * @return CustomItem[]
     */
    public function getAllTypes(): array
    {
        return $this->registeredTypes;
    }

    /**
     * Get all type IDs
     * 
     * @return array
     */
    public function getAllTypeIds(): array
    {
        return array_keys($this->registeredTypes);
    }

    /**
     * Get all type names
     * 
     * @return array
     */
    public function getAllTypeNames(): array
    {
        return array_map(fn(CustomItem $item) => $item->getName(), $this->registeredTypes);
    }

    /**
     * Create an item from a registered type
     * 
     * @param string $id
     * @param int $count
     * @return Item|null
     */
    public function createItem(string $id, int $count = 1): ?Item
    {
        $customItem = $this->getType($id);
        if ($customItem === null) {
            return null;
        }

        return $customItem->createItem($count);
    }

    /**
     * Create an item from a registered type by name
     * 
     * @param string $name
     * @param int $count
     * @return Item|null
     */
    public function createItemByName(string $name, int $count = 1): ?Item
    {
        $customItem = $this->getTypeByName($name);
        if ($customItem === null) {
            return null;
        }

        return $customItem->createItem($count);
    }

    /**
     * Check if an item is a registered custom item
     * 
     * @param Item $item
     * @return bool
     */
    public function isRegisteredCustomItem(Item $item): bool
    {
        if (!CustomItem::isCustomItem($item, $this->namespace)) {
            return false;
        }

        $id = CustomItem::getCustomItemId($item, $this->namespace);
        return $id !== null && $this->hasType($id);
    }

    /**
     * Get the custom item type from an item
     * 
     * @param Item $item
     * @return CustomItem|null
     */
    public function getCustomItemFromItem(Item $item): ?CustomItem
    {
        if (!$this->isRegisteredCustomItem($item)) {
            return null;
        }

        $id = CustomItem::getCustomItemId($item, $this->namespace);
        return $this->getType($id);
    }

    /**
     * Get registry statistics
     * 
     * @return array
     */
    public function getStats(): array
    {
        $typeStats = [];
        foreach ($this->registeredTypes as $customItem) {
            $type = $customItem->getType();
            $typeStats[$type] = ($typeStats[$type] ?? 0) + 1;
        }

        return [
            'total_types' => count($this->registeredTypes),
            'types_by_category' => $typeStats,
            'max_types' => $this->config->get("custom_items.max_types", 100),
            'allow_duplicates' => $this->config->get("custom_items.allow_duplicate_names", false)
        ];
    }

    /**
     * Clear all registered types
     */
    public function clearAll(): void
    {
        $this->registeredTypes = [];
        $this->saveRegisteredTypes();
    }

    /**
     * Export all types to array
     * 
     * @return array
     */
    public function exportToArray(): array
    {
        $data = [];
        foreach ($this->registeredTypes as $customItem) {
            $data[] = $customItem->toArray();
        }
        return $data;
    }

    /**
     * Import types from array
     * 
     * @param array $data
     * @param bool $overwrite
     * @return int Number of items imported
     */
    public function importFromArray(array $data, bool $overwrite = false): int
    {
        $imported = 0;
        foreach ($data as $itemData) {
            $customItem = CustomItem::fromArray($itemData);
            if ($customItem === null) {
                continue;
            }

            if (!$overwrite && $this->hasType($customItem->getId())) {
                continue;
            }

            $this->registeredTypes[$customItem->getId()] = $customItem;
            $imported++;
        }

        if ($imported > 0) {
            $this->saveRegisteredTypes();
        }

        return $imported;
    }

    /**
     * Load registered types from storage
     */
    public function loadRegisteredTypes(): void
    {
        if (!file_exists($this->dataPath)) {
            $this->registeredTypes = [];
            return;
        }

        $config = new Config($this->dataPath, Config::YAML);
        $data = $config->get("registered_types", []);

        $this->registeredTypes = [];
        foreach ($data as $itemData) {
            $customItem = CustomItem::fromArray($itemData);
            if ($customItem !== null) {
                $this->registeredTypes[$customItem->getId()] = $customItem;
            }
        }
    }

    /**
     * Save registered types to storage
     */
    public function saveRegisteredTypes(): void
    {
        $data = [];
        foreach ($this->registeredTypes as $customItem) {
            $data[] = $customItem->toArray();
        }

        $config = new Config($this->dataPath, Config::YAML);
        $config->set("registered_types", $data);
        $config->save();
    }

    /**
     * Get types by category
     * 
     * @param string $type
     * @return CustomItem[]
     */
    public function getTypesByCategory(string $type): array
    {
        return array_filter($this->registeredTypes, fn(CustomItem $item) => $item->getType() === $type);
    }

    /**
     * Get the namespace
     * 
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
