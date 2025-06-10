# CoreAPI Scoreboard System

A stable and modern scoreboard API designed for PocketMine-MP plugins. The system supports multiple plugins, automatic updates, and follows CoreAPI's established patterns.

## Features

- **Multi-plugin Support**: Multiple plugins can register and manage scoreboards
- **Automatic Display**: Scoreboards automatically display to new players based on priority
- **Form-Based Management**: Intuitive UI for players to manage their scoreboards
- **Command-Line Interface**: Full CLI support for power users and console
- **Priority System**: Higher priority scoreboards take precedence
- **Automatic Updates**: Configurable auto-update intervals
- **Session Integration**: Seamlessly integrates with CoreAPI's session system
- **Tag System**: Dynamic content replacement using tags
- **Factory Pattern**: Easy scoreboard creation with predefined configurations
- **Clean Architecture**: Follows CoreAPI's manager and component patterns

## Quick Start

### Basic Usage

```php
use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\scoreboard\factory\ScoreboardFactory;

// Get the scoreboard manager
$scoreboardManager = CoreAPI::getInstance()->getScoreboardManager();

// Create a basic scoreboard
$scoreboard = ScoreboardFactory::createPlayerInfo(
    "my_scoreboard",
    "§6My Server",
    "MyPlugin"
);

// Add some lines
$scoreboard->addLine(new ScoreboardLine("§7Player: §a{player}", 10));
$scoreboard->addLine(new ScoreboardLine("§7Health: §c{health}", 9));
$scoreboard->addLine(new ScoreboardLine("§7Level: §e{level}", 8));

// Register the scoreboard (will automatically display to new players if autoDisplay is true)
$scoreboardManager->registerScoreboard($scoreboard);

// Manual display to a specific player (optional)
$scoreboardManager->displayScoreboard($player, "my_scoreboard");
```

### Using the Factory

```php
use JonasWindmann\CoreAPI\scoreboard\factory\ScoreboardFactory;

// Create a player info scoreboard with common tags
$playerBoard = ScoreboardFactory::createPlayerInfo(
    "player_info",
    "§6Player Stats",
    "MyPlugin"
);

// Create a server info scoreboard
$serverBoard = ScoreboardFactory::createServerInfo(
    "server_info", 
    "§6Server Info",
    "MyPlugin"
);

// Create with predefined lines
$customBoard = ScoreboardFactory::createWithLines(
    "custom_board",
    "§6Custom Board",
    "MyPlugin",
    [
        "§7Welcome to the server!",
        "§7Player: §a{player}",
        "§7Online: §e{online}",
        "§7TPS: §c{tps}"
    ]
);
```

## Core Components

### Scoreboard

The main scoreboard class that implements `Manageable`:

```php
use JonasWindmann\CoreAPI\scoreboard\Scoreboard;
use JonasWindmann\CoreAPI\scoreboard\ScoreboardLine;

$scoreboard = new Scoreboard(
    "my_board",           // Unique ID
    "§6My Scoreboard",    // Title
    "MyPlugin",           // Owner plugin
    100,                  // Priority (higher = more important)
    true,                 // Auto-update enabled
    20,                   // Update interval in ticks
    true                  // Auto-display to new players
);
```

### ScoreboardLine

Represents individual lines in the scoreboard:

```php
use JonasWindmann\CoreAPI\scoreboard\ScoreboardLine;

$line = new ScoreboardLine(
    "§7Player: §a{player}",  // Template with tags
    10,                      // Score value
    true                     // Visible
);

$scoreboard->addLine($line);
```

### ScoreboardTag

Dynamic content replacement system:

```php
use JonasWindmann\CoreAPI\scoreboard\ScoreboardTag;

// Create a custom tag
$tag = new ScoreboardTag("custom", function($player) {
    return "Custom value for " . $player->getName();
}, "Custom tag description");

$scoreboard->addTag($tag);
```

## Built-in Management Commands

CoreAPI provides comprehensive scoreboard management through built-in commands:

### Command-Line Interface

- **`/coresb list`** - List all available scoreboards with details
- **`/coresb show <id>`** - Display a specific scoreboard
- **`/coresb hide`** - Hide your current scoreboard
- **`/coresb manage`** - Open form-based management interface
- **`/coresb info [id]`** - Show detailed information about a scoreboard

### Form-Based Management

Players can use `/coresb manage` to access an intuitive form interface:

- **Main Menu** - Overview with quick actions
- **Browse Scoreboards** - View all available scoreboards with details
- **Quick Selection** - Fast scoreboard switching
- **Detailed View** - Complete information about any scoreboard
- **Status Check** - View current scoreboard and settings

### Permissions

- `coreapi.scoreboard.use` - Basic scoreboard usage
- `coreapi.scoreboard.list` - List scoreboards
- `coreapi.scoreboard.show` - Display scoreboards
- `coreapi.scoreboard.hide` - Hide scoreboards
- `coreapi.scoreboard.manage` - Access form management
- `coreapi.scoreboard.info` - View scoreboard information

## Session Integration

The scoreboard system integrates with CoreAPI's session system through the `ScoreboardComponent`:

```php
// Get a player's scoreboard component
$session = CoreAPI::getInstance()->getSessionManager()->getSessionByPlayer($player);
$component = $session->getComponent("scoreboard");

// Show a scoreboard
$component->showScoreboard($scoreboard);

// Hide the current scoreboard
$component->hideScoreboard();

// Update the current scoreboard
$component->updateScoreboard();

// Check if a scoreboard is active
if ($component->hasActiveScoreboard()) {
    $activeBoard = $component->getActiveScoreboard();
}
```

## Automatic Display

The scoreboard system automatically handles displaying scoreboards to new players based on priority and auto-display settings:

```php
// Create a scoreboard that automatically displays to new players
$autoBoard = ScoreboardFactory::createServerInfo(
    "welcome_board",
    "§6Welcome!",
    "MyPlugin",
    true  // Auto-display enabled
);
$autoBoard->setPriority(100); // High priority

// Create a scoreboard that only displays manually
$manualBoard = ScoreboardFactory::createPlayerInfo(
    "stats_board",
    "§aPlayer Stats",
    "MyPlugin",
    false // Auto-display disabled
);

// Register both scoreboards
$scoreboardManager->registerScoreboard($autoBoard);
$scoreboardManager->registerScoreboard($manualBoard);

// The welcome_board will automatically display to new players
// Players can manually switch to stats_board using commands
```

### How Automatic Display Works

1. **Player Joins**: When a player joins the server, the ScoreboardManager automatically finds the highest priority scoreboard with auto-display enabled
2. **Priority-Based**: If multiple scoreboards have auto-display enabled, the one with the highest priority is shown
3. **Session Integration**: The system waits for the player's session to be created before displaying the scoreboard
4. **No Manual Handling**: You don't need to handle PlayerJoinEvent or manage display timing

## Advanced Usage

### Custom Tags

```php
// Add custom tags to your scoreboard
$scoreboard->addTag(new ScoreboardTag("money", function($player) {
    // Integrate with economy plugin
    return EconomyAPI::getInstance()->myMoney($player);
}, "Player's money"));

$scoreboard->addTag(new ScoreboardTag("rank", function($player) {
    // Integrate with rank plugin
    return RankSystem::getRank($player);
}, "Player's rank"));
```

### Priority Management

```php
// High priority scoreboard (will override others)
$importantBoard = new Scoreboard("important", "§cImportant!", "MyPlugin", 1000);

// Low priority scoreboard (will be overridden)
$normalBoard = new Scoreboard("normal", "§7Normal", "MyPlugin", 1);

// Get the highest priority scoreboard
$topBoard = $scoreboardManager->getHighestPriorityScoreboard();
```

### Plugin Management

```php
// Get all scoreboards from a specific plugin
$myScoreboards = $scoreboardManager->getScoreboardsByPlugin("MyPlugin");

// Clean up when plugin disables
$scoreboardManager->cleanupPlugin("MyPlugin");
```

## Built-in Tags

### Player Tags
- `{player}` - Player name
- `{health}` - Player health
- `{food}` - Player food level
- `{level}` - Player experience level
- `{world}` - Current world name
- `{x}`, `{y}`, `{z}` - Player coordinates

### Server Tags
- `{online}` - Online player count
- `{max_players}` - Maximum player count
- `{tps}` - Server TPS
- `{load}` - Server load percentage
- `{time}` - Current time (H:i:s)
- `{date}` - Current date (Y-m-d)

## Best Practices

1. **Use Unique IDs**: Always use unique scoreboard IDs to avoid conflicts
2. **Set Appropriate Priorities**: Use priority system to manage multiple scoreboards
3. **Clean Up**: Remove scoreboards when your plugin disables
4. **Optimize Updates**: Use reasonable update intervals to avoid performance issues
5. **Handle Offline Players**: Always check if players are online before updating

## Examples

### Complete Plugin Example

```php
<?php

namespace MyPlugin;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\scoreboard\factory\ScoreboardFactory;
use pocketmine\plugin\PluginBase;

class MyPlugin extends PluginBase {

    protected function onEnable(): void {
        $scoreboardManager = CoreAPI::getInstance()->getScoreboardManager();

        // Create a server info scoreboard with auto-display
        $scoreboard = ScoreboardFactory::createServerInfo(
            "server_info",
            "§6My Server",
            $this->getName(),
            true // Auto-display enabled
        );

        // Set high priority so it displays by default
        $scoreboard->setPriority(100);

        // Add custom lines
        $scoreboard->addLine(new ScoreboardLine("§7Welcome!", 15));
        $scoreboard->addLine(new ScoreboardLine("§7Players: §a{online}", 14));
        $scoreboard->addLine(new ScoreboardLine("§7TPS: §e{tps}", 13));

        // Register the scoreboard - it will automatically display to new players!
        $scoreboardManager->registerScoreboard($scoreboard);

        // No need to manually handle PlayerJoinEvent or display to existing players
        // The API handles everything automatically!
    }
    
    protected function onDisable(): void {
        // Clean up our scoreboards
        CoreAPI::getInstance()->getScoreboardManager()->cleanupPlugin($this->getName());
    }
}
```

## Form Integration

The scoreboard system includes comprehensive form-based management:

```php
use JonasWindmann\CoreAPI\scoreboard\form\ScoreboardManagementForm;

// Open the main management interface
$form = new ScoreboardManagementForm();
$form->sendTo($player);
```

**Available Forms:**
- `ScoreboardManagementForm` - Main menu with all options
- `ScoreboardListForm` - Browse all available scoreboards
- `ScoreboardSelectionForm` - Quick selection for display
- `ScoreboardDetailForm` - Detailed information view
- `ScoreboardStatusForm` - Current player status
- `ScoreboardContentForm` - View lines and tags

## Summary

This scoreboard system provides a robust, flexible, and easy-to-use API for managing scoreboards in PocketMine-MP plugins. It features:

✅ **Automatic Display** - No manual PlayerJoinEvent handling required
✅ **Form-Based UI** - Beautiful, intuitive interface for players
✅ **Command-Line Support** - Full CLI for power users and console
✅ **Multi-Plugin Support** - Multiple plugins can coexist peacefully
✅ **Real-Time Updates** - Dynamic content with configurable intervals
✅ **Priority System** - Smart display management based on importance
✅ **Session Integration** - Seamless integration with CoreAPI's architecture

The system maintains compatibility with multiple plugins while following CoreAPI's established patterns and providing both developer-friendly APIs and user-friendly interfaces.
