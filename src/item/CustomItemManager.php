<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\item;

use JonasWindmann\CoreAPI\manager\BaseManager;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;

/**
 * Manager for custom items in CoreAPI
 * Follows CoreAPI patterns and extends BaseManager
 */
class CustomItemManager extends BaseManager
{
    private Config $config;
    private CustomItemRegistry $registry;
    private string $namespace;

    public function __construct(Plugin $plugin)
    {
        parent::__construct($plugin);
        $this->namespace = "CoreAPI";
        $this->initializeConfig();
        $this->registry = new CustomItemRegistry($this->config, $this->namespace);
        $this->loadItems();
    }

    /**
     * Initialize configuration
     */
    private function initializeConfig(): void
    {
        $this->plugin->saveResource("custom_items.yml");
        $this->config = new Config($this->plugin->getDataFolder() . "custom_items.yml", Config::YAML, [
            'custom_items' => [
                'max_types' => 100,
                'allow_duplicate_names' => false,
                'defaults' => [
                    'base_item' => 'minecraft:stick',
                    'lore' => ['§7Custom Item']
                ]
            ]
        ]);
    }

    /**
     * Load items from storage
     */
    public function loadItems(): void
    {
        $this->registry->loadRegisteredTypes();
        $registeredItems = $this->registry->getAllTypes();

        $this->items = [];
        foreach ($registeredItems as $customItem) {
            $this->items[$customItem->getId()] = $customItem;
        }

        // Load example items if enabled
        $this->loadExampleItems();

        $this->plugin->getLogger()->info("Loaded " . count($this->items) . " custom items.");
    }

    /**
     * Save items to storage
     */
    public function saveItems(): void
    {
        $this->registry->saveRegisteredTypes();
    }

    /**
     * Load example items from configuration if enabled
     */
    private function loadExampleItems(): void
    {
        if (!$this->config->getNested("examples.enabled", false)) {
            $this->plugin->getLogger()->debug("Example items are disabled in configuration");
            return;
        }

        $exampleItems = $this->config->getNested("examples.items", []);
        if (empty($exampleItems)) {
            $this->plugin->getLogger()->warning("No example items found in configuration");
            return;
        }

        $this->plugin->getLogger()->info("Loading " . count($exampleItems) . " example items...");
        $loadedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($exampleItems as $index => $itemData) {
            if (!is_array($itemData)) {
                $this->plugin->getLogger()->warning("Example item at index $index is not an array");
                $errorCount++;
                continue;
            }

            // Validate required fields
            if (!isset($itemData['id'], $itemData['name'], $itemData['type'], $itemData['base_item'])) {
                $this->plugin->getLogger()->warning("Example item at index $index is missing required fields: " . json_encode($itemData));
                $errorCount++;
                continue;
            }

            // Ensure namespace is set for CoreAPI
            $itemData['namespace'] = $this->namespace;

            $this->plugin->getLogger()->debug("Processing example item: " . json_encode($itemData));

            $customItem = CustomItem::fromArray($itemData);
            if ($customItem === null) {
                $this->plugin->getLogger()->warning("Failed to create example item from data: " . json_encode($itemData));
                $errorCount++;
                continue;
            }

            // Check if item already exists (don't overwrite existing items)
            if ($this->getCustomItem($customItem->getId()) !== null) {
                $this->plugin->getLogger()->debug("Example item '{$customItem->getId()}' already exists, skipping");
                $skippedCount++;
                continue;
            }

            // Try to register the item
            try {
                if ($this->registerCustomItem($customItem)) {
                    $this->plugin->getLogger()->debug("Successfully registered example item: " . $customItem->getId());
                    $loadedCount++;
                } else {
                    $this->plugin->getLogger()->warning("Failed to register example item: " . $customItem->getId() . " (registration returned false)");
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $this->plugin->getLogger()->warning("Exception while registering example item " . $customItem->getId() . ": " . $e->getMessage());
                $errorCount++;
            }
        }

        // Log summary
        $totalProcessed = $loadedCount + $skippedCount + $errorCount;
        $this->plugin->getLogger()->info("Example items summary: $loadedCount loaded, $skippedCount skipped, $errorCount errors (total: $totalProcessed)");

        if ($loadedCount > 0) {
            $this->plugin->getLogger()->info("Successfully loaded $loadedCount example custom items.");
        } elseif ($errorCount > 0) {
            $this->plugin->getLogger()->warning("No example items were successfully loaded due to errors.");
        } elseif ($skippedCount > 0) {
            $this->plugin->getLogger()->info("All example items were skipped (already exist).");
        }
    }

    /**
     * Create a new custom item
     * 
     * @param string $id
     * @param string $name
     * @param string $type
     * @param Item|null $baseItem
     * @param array $customData
     * @param array $lore
     * @return CustomItem
     */
    public function createCustomItem(
        string $id,
        string $name,
        string $type = "generic",
        ?Item $baseItem = null,
        array $customData = [],
        array $lore = []
    ): CustomItem {
        if ($baseItem === null) {
            $defaultBaseItem = $this->config->get("custom_items.defaults.base_item", "minecraft:stick");
            try {
                $baseItem = StringToItemParser::getInstance()->parse($defaultBaseItem);
                if ($baseItem === null) {
                    $baseItem = VanillaItems::STICK();
                }
            } catch (\Exception $e) {
                $baseItem = VanillaItems::STICK();
            }
        }

        if (empty($lore)) {
            $lore = $this->config->get("custom_items.defaults.lore", []);
        }

        return new CustomItem($id, $name, $type, $baseItem, $customData, $lore, $this->namespace);
    }

    /**
     * Register a custom item
     * 
     * @param CustomItem $customItem
     * @return bool
     */
    public function registerCustomItem(CustomItem $customItem): bool
    {
        if ($this->addItem($customItem)) {
            if ($this->registry->registerType($customItem)) {
                $this->plugin->getLogger()->info("Registered custom item: " . $customItem->getId());
                return true;
            } else {
                // Remove from items if registry failed
                $this->removeItem($customItem->getId());
            }
        }
        return false;
    }

    /**
     * Create and register a custom item in one step
     * 
     * @param string $id
     * @param string $name
     * @param string $type
     * @param Item|null $baseItem
     * @param array $customData
     * @param array $lore
     * @return CustomItem|null
     */
    public function createAndRegisterCustomItem(
        string $id,
        string $name,
        string $type = "generic",
        ?Item $baseItem = null,
        array $customData = [],
        array $lore = []
    ): ?CustomItem {
        $customItem = $this->createCustomItem($id, $name, $type, $baseItem, $customData, $lore);

        if ($this->registerCustomItem($customItem)) {
            return $customItem;
        }

        return null;
    }

    /**
     * Unregister a custom item
     * 
     * @param string $id
     * @return bool
     */
    public function unregisterCustomItem(string $id): bool
    {
        if ($this->removeItem($id)) {
            if ($this->registry->unregisterType($id)) {
                $this->plugin->getLogger()->info("Unregistered custom item: $id");
                return true;
            }
        }
        return false;
    }

    /**
     * Get a custom item by ID
     * 
     * @param string $id
     * @return CustomItem|null
     */
    public function getCustomItem(string $id): ?CustomItem
    {
        $item = $this->getItem($id);
        return $item instanceof CustomItem ? $item : null;
    }

    /**
     * Get all custom items
     * 
     * @return CustomItem[]
     */
    public function getAllCustomItems(): array
    {
        return array_filter($this->items, fn($item) => $item instanceof CustomItem);
    }

    /**
     * Create an item instance from a custom item ID
     * 
     * @param string $id
     * @param int $count
     * @return Item|null
     */
    public function createItem(string $id, int $count = 1): ?Item
    {
        return $this->registry->createItem($id, $count);
    }

    /**
     * Create an item instance from a custom item name
     * 
     * @param string $name
     * @param int $count
     * @return Item|null
     */
    public function createItemByName(string $name, int $count = 1): ?Item
    {
        return $this->registry->createItemByName($name, $count);
    }

    /**
     * Give a custom item to a player
     * 
     * @param Player $player
     * @param string $id
     * @param int $count
     * @return bool
     */
    public function giveCustomItem(Player $player, string $id, int $count = 1): bool
    {
        $item = $this->createItem($id, $count);
        if ($item === null) {
            return false;
        }

        if (!$player->getInventory()->canAddItem($item)) {
            return false;
        }

        $player->getInventory()->addItem($item);
        return true;
    }

    /**
     * Give a custom item to a player by name
     * 
     * @param Player $player
     * @param string $name
     * @param int $count
     * @return bool
     */
    public function giveCustomItemByName(Player $player, string $name, int $count = 1): bool
    {
        $item = $this->createItemByName($name, $count);
        if ($item === null) {
            return false;
        }

        if (!$player->getInventory()->canAddItem($item)) {
            return false;
        }

        $player->getInventory()->addItem($item);
        return true;
    }

    /**
     * Check if an item is a custom item
     * 
     * @param Item $item
     * @return bool
     */
    public function isCustomItem(Item $item): bool
    {
        return CustomItem::isCustomItem($item, $this->namespace);
    }

    /**
     * Get custom item ID from an item
     * 
     * @param Item $item
     * @return string|null
     */
    public function getCustomItemId(Item $item): ?string
    {
        return CustomItem::getCustomItemId($item, $this->namespace);
    }

    /**
     * Get custom data from an item
     *
     * @param Item $item
     * @param string $key
     * @return mixed
     */
    public function getCustomData(Item $item, string $key): mixed
    {
        return CustomItem::getCustomDataFromItem($item, $key, $this->namespace);
    }

    /**
     * Get all custom item IDs
     * 
     * @return array
     */
    public function getAllCustomItemIds(): array
    {
        return array_keys($this->getAllCustomItems());
    }

    /**
     * Get all custom item names
     * 
     * @return array
     */
    public function getAllCustomItemNames(): array
    {
        return array_map(fn(CustomItem $item) => $item->getName(), $this->getAllCustomItems());
    }

    /**
     * Get registry statistics
     * 
     * @return array
     */
    public function getStats(): array
    {
        return [
            'total_items' => count($this->getAllCustomItems()),
            'registry_stats' => $this->registry->getStats()
        ];
    }

    /**
     * Clear all custom items
     */
    public function clearAll(): void
    {
        $this->items = [];
        $this->registry->clearAll();
    }

    /**
     * Export all custom items
     * 
     * @return array
     */
    public function exportAll(): array
    {
        return $this->registry->exportToArray();
    }

    /**
     * Import custom items
     * 
     * @param array $data
     * @param bool $overwrite
     * @return int
     */
    public function importCustomItems(array $data, bool $overwrite = false): int
    {
        $imported = $this->registry->importFromArray($data, $overwrite);
        $this->loadItems(); // Reload items after import
        return $imported;
    }

    /**
     * Get the registry instance
     * 
     * @return CustomItemRegistry
     */
    public function getRegistry(): CustomItemRegistry
    {
        return $this->registry;
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
