# CoreAPI NPC API

The NPC API provides a simple and powerful way to create and manage NPCs (Non-Player Characters) in your PocketMine-MP server.

## Features

- Create and manage NPCs with custom behaviors
- NPCs automatically look at nearby players
- Persistent storage of NPCs across server restarts
- Standalone NPC system with no external dependencies
- Customizable NPC properties

## Getting Started

### Accessing the NPC API

```php
use JonasWindmann\CoreAPI\CoreAPI;

// Get the NPC Manager
$npcManager = CoreAPI::getInstance()->getNpcManager();
```

### Creating a Custom NPC Type

To create a custom NPC type, extend the `CoreNpc` class:

```php
use JonasWindmann\CoreAPI\npc\CoreNpc;
use pocketmine\player\Player;

class GreeterNpc extends CoreNpc {
    protected function initNpc(): void {
        $this->setNpcName("§aGreeter");
        $this->lookDistance = 10.0; // Look at players up to 10 blocks away
    }

    public function onClick(Player $player): void {
        $player->sendMessage("§aHello, " . $player->getName() . "!");
    }
}
```

### Registering an NPC Type

Register your NPC type with the NPC Manager:

```php
$npcManager->registerNpcType(GreeterNpc::class);
```

### Creating an NPC

Create an NPC at a player's location:

```php
$npc = $npcManager->createNpc("GreeterNpc", $player);
```

You can also provide additional data:

```php
$npc = $npcManager->createNpc("GreeterNpc", $player, [
    "message" => "Welcome to our server!"
]);
```

### Removing an NPC

Remove an NPC by its entity ID:

```php
$npcManager->removeNpc($npc->getId());
```

### Getting NPCs

Get an NPC by its entity ID:

```php
$npc = $npcManager->getNpcById($entityId);
```

Get all active NPCs:

```php
$npcs = $npcManager->getActiveNpcs();
```


## Configuration

The NPC API uses a configuration file located at `plugin_data/CoreAPI/npc_config.yml`. This file contains settings for NPCs and is used to store NPC data.

### Default Configuration

```yaml
# NPCs saved by the system
npcs: []

# General settings
settings:
  # Default look distance for NPCs (in blocks)
  default_look_distance: 8.0

  # Whether to automatically save NPCs when they are created or removed
  auto_save: true

  # Default skin settings (future use)
  default_skin:
    enabled: false
    path: "skins/default.png"
    geometry: "geometry.humanoid.custom"
```

## API Reference

### CoreNpc

The base class for all NPCs in CoreAPI.

#### Properties

- `$npcType`: The type of the NPC
- `$data`: Additional data for the NPC
- `$lookAtPlayer`: Whether the NPC should look at nearby players
- `$lookDistance`: The maximum distance at which the NPC will look at players

#### Methods

- `initNpc()`: Initialize NPC-specific properties
- `onClick(Player $player)`: Called when a player clicks on the NPC
- `setData(string $key, $value)`: Set a data value
- `getData(string $key, $default = null)`: Get a data value
- `getAllData()`: Get all data
- `getNpcType()`: Get the NPC type
- `setNpcName(string $name)`: Set the NPC name/nametag
- `getNpcName()`: Get the NPC name
- `lookAt(Vector3 $target)`: Make the NPC look at a position

### NpcManager

The manager class for NPCs in CoreAPI.

#### Methods

- `registerNpcType(string $className)`: Register an NPC type
- `getRegisteredNpcTypes()`: Get all registered NPC types
- `createNpc(string $type, Player $creator, array $data = [])`: Create a new NPC
- `removeNpc(int $entityId)`: Remove an NPC
- `getNpcById(int $entityId)`: Get an NPC by entity ID
- `getActiveNpcs()`: Get all active NPCs
- `loadItems()`: Load NPCs from storage
- `saveItems()`: Save NPCs to storage
