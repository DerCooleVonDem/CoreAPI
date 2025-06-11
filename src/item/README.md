# CoreAPI Custom Item Manager

The CustomItemManager is a comprehensive system for creating, managing, and using custom items in PocketMine-MP plugins. It follows CoreAPI's established patterns and provides a robust, type-safe API for custom item management.

## Features

### 🎯 **Core Features**
- **Custom Item Creation**: Create items with custom NBT data, lore, and metadata
- **Registry System**: Persistent storage and management of custom item types
- **Type Safety**: Full type hints and validation throughout the system
- **Namespace Support**: Avoid conflicts with other plugins using custom namespaces
- **Command Interface**: Complete CLI management with subcommands
- **Import/Export**: Backup and share custom item configurations

### 🏗️ **Architecture**

The system follows CoreAPI's manager pattern with these components:

#### **CustomItem Class**
- Implements `Manageable` interface for BaseManager compatibility
- Stores item metadata (ID, name, type, base item, custom data, lore)
- Provides NBT serialization and item creation methods
- Static utility methods for item identification

#### **CustomItemManager Class**
- Extends `BaseManager` for consistent API patterns
- Manages custom item lifecycle (create, register, remove)
- Provides player interaction methods (give items, check items)
- Handles configuration and validation

#### **CustomItemRegistry Class**
- Persistent storage using YAML configuration
- Type validation and duplicate checking
- Import/export functionality
- Statistics and reporting

## Usage Examples

### Basic Usage

```php
use JonasWindmann\CoreAPI\CoreAPI;

// Get the manager
$customItemManager = CoreAPI::getInstance()->getCustomItemManager();

// Create a custom item
$customItem = $customItemManager->createAndRegisterCustomItem(
    "magic_sword",           // ID
    "§cMagic Sword",        // Display name
    "weapon",               // Type/category
    VanillaItems::IRON_SWORD(), // Base item
    [                       // Custom data
        "damage" => "15",
        "element" => "fire"
    ],
    [                       // Lore
        "§7A powerful magical weapon",
        "§7Deals extra fire damage"
    ]
);

// Give item to player
$customItemManager->giveCustomItem($player, "magic_sword", 1);
```

### Advanced Usage

```php
// Check if an item is a custom item
if ($customItemManager->isCustomItem($item)) {
    $customId = $customItemManager->getCustomItemId($item);
    $damage = $customItemManager->getCustomData($item, "damage");
}

// Get all items of a specific type
$weapons = array_filter(
    $customItemManager->getAllCustomItems(),
    fn($item) => $item->getType() === "weapon"
);

// Export all items
$exportData = $customItemManager->exportAll();
file_put_contents("backup.json", json_encode($exportData, JSON_PRETTY_PRINT));
```

## Command System

The CustomItemManager includes a comprehensive command system:

### `/customitem create <id> <name> [type] [base_item]`
Create a new custom item.

**Examples:**
```
/customitem create magic_wand "§bMagic Wand" tool minecraft:stick
/customitem create healing_potion "§aHealing Potion" consumable
```

### `/customitem give <player> <id> [count]`
Give a custom item to a player.

**Examples:**
```
/customitem give Steve magic_wand 1
/customitem give @a healing_potion 5
```

### `/customitem list [type]`
List all registered custom items, optionally filtered by type.

**Examples:**
```
/customitem list
/customitem list weapon
```

### `/customitem info <id>`
Show detailed information about a custom item.

**Example:**
```
/customitem info magic_sword
```

### `/customitem remove <id>`
Remove a custom item from the registry.

**Example:**
```
/customitem remove old_item
```

### `/customitem export [filename]`
Export all custom items to a JSON file.

**Examples:**
```
/customitem export
/customitem export my_items.json
```

### `/customitem import <filename> [overwrite]`
Import custom items from a JSON file.

**Examples:**
```
/customitem import backup.json
/customitem import shared_items.json true
```

## Configuration

### Main Configuration (`custom_items.yml`)

```yaml
custom_items:
  # Maximum number of custom item types
  max_types: 100
  
  # Allow duplicate display names
  allow_duplicate_names: false
  
  # Default values for new items
  defaults:
    base_item: "minecraft:stick"
    lore:
      - "§7Custom Item"
      - "§8Created with CoreAPI"

# Example items (optional)
examples:
  enabled: false
  items:
    - id: "example_sword"
      name: "§cFlaming Sword"
      type: "weapon"
      base_item: "minecraft:iron_sword"
      custom_data:
        damage: "10"
        element: "fire"
      lore:
        - "§7A powerful sword"
        - "§7Deals extra fire damage"
```

### Registry Storage (`custom_items_registry.yml`)

Automatically managed file storing all registered custom items.

## API Reference

### CustomItemManager Methods

#### Creation and Registration
- `createCustomItem()` - Create a custom item instance
- `registerCustomItem()` - Register an existing custom item
- `createAndRegisterCustomItem()` - Create and register in one step
- `unregisterCustomItem()` - Remove a custom item

#### Retrieval
- `getCustomItem()` - Get custom item by ID
- `getAllCustomItems()` - Get all registered items
- `getAllCustomItemIds()` - Get all item IDs
- `getAllCustomItemNames()` - Get all item names

#### Item Operations
- `createItem()` - Create Item instance from custom item ID
- `createItemByName()` - Create Item instance from custom item name
- `giveCustomItem()` - Give custom item to player
- `giveCustomItemByName()` - Give custom item to player by name

#### Validation
- `isCustomItem()` - Check if Item is a custom item
- `getCustomItemId()` - Get custom item ID from Item
- `getCustomData()` - Get custom data from Item

#### Management
- `exportAll()` - Export all items to array
- `importCustomItems()` - Import items from array
- `getStats()` - Get registry statistics
- `clearAll()` - Remove all custom items

### CustomItem Methods

#### Properties
- `getId()` - Get unique identifier
- `getName()` - Get display name
- `getType()` - Get type/category
- `getBaseItem()` - Get base Item instance
- `getCustomData()` - Get custom data array
- `getLore()` - Get lore array
- `getNamespace()` - Get namespace

#### Operations
- `createItem()` - Create Item instance with NBT
- `toArray()` - Serialize to array
- `fromArray()` - Deserialize from array

#### Static Utilities
- `isCustomItem()` - Check if Item is custom
- `getCustomItemId()` - Extract ID from Item
- `getCustomItemType()` - Extract type from Item
- `getCustomDataFromItem()` - Extract custom data from Item

## Permissions

All permissions default to `op`:

- `coreapi.customitem.use` - Use custom item commands
- `coreapi.customitem.create` - Create custom items
- `coreapi.customitem.give` - Give items to players
- `coreapi.customitem.list` - List custom items
- `coreapi.customitem.remove` - Remove custom items
- `coreapi.customitem.info` - View item information
- `coreapi.customitem.export` - Export custom items
- `coreapi.customitem.import` - Import custom items

## Integration with Other Systems

The CustomItemManager integrates seamlessly with other CoreAPI systems:

### Session System
```php
// Store custom item data in player sessions
$session = CoreAPI::getInstance()->getSessionManager()->getSessionByPlayer($player);
$session->setData("equipped_weapon", $customItemId);
```

### Command System
```php
// Custom items work with CoreAPI's command system
class MyCustomCommand extends BaseCommand {
    public function execute(CommandSender $sender, array $args): void {
        $customItemManager = CoreAPI::getInstance()->getCustomItemManager();
        // Use custom items in your commands
    }
}
```

### Form System
```php
// Create forms for custom item management
$form = new SimpleForm("Custom Items", "Select an item:");
foreach ($customItemManager->getAllCustomItems() as $item) {
    $form->addButton($item->getName());
}
```

## Best Practices

1. **Use Descriptive IDs**: Use clear, unique identifiers like `"magic_sword_fire"` instead of `"item1"`

2. **Organize by Type**: Use consistent type categories like `"weapon"`, `"tool"`, `"consumable"`

3. **Validate Input**: Always check if items exist before using them

4. **Handle Inventory Space**: Check if players can receive items before giving them

5. **Use Namespaces**: For plugins that create many items, consider using custom namespaces

6. **Backup Regularly**: Use the export feature to backup your custom items

7. **Document Custom Data**: Keep track of what custom data keys mean in your plugin

## Troubleshooting

### Common Issues

**Item not found**: Check if the item ID is correct and the item is registered
**Permission denied**: Ensure the player has the required permissions
**Inventory full**: Check if the player's inventory has space before giving items
**Invalid base item**: Verify the base item string is valid (e.g., `"minecraft:stick"`)

### Debug Information

Use the info command to debug custom items:
```
/customitem info <id>
```

Check registry statistics:
```php
$stats = $customItemManager->getStats();
var_dump($stats);
```

This comprehensive CustomItemManager provides all the features needed for robust custom item management while maintaining CoreAPI's high standards for code quality and architectural consistency.
