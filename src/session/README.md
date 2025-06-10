# Player Session API

The Player Session API provides a way to manage player-specific data and functionality through components. Each player that joins the server gets a session object, which can have components attached to it.

**🎉 NEW**: Factory pattern for component creation eliminates cloning confusion and improves event listener scope!

## Features

- **NEW**: Factory pattern for component creation (no more cloning confusion)
- **IMPROVED**: Proper event listener registration scope
- Automatic session creation and cleanup when players join and leave
- Component-based architecture for modular functionality
- Components are automatically registered as event listeners
- Easy access to the player from any component

## Quick Start (Recommended - New Factory Pattern)

### Registering Components with Factories

The new factory pattern eliminates cloning confusion and provides better control over component creation:

```php
use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\session\SimpleComponentFactory;

$sessionManager = CoreAPI::getInstance()->getSessionManager();

// Method 1: Using closure factory (recommended for simple components)
$sessionManager->registerComponentFactory(
    SimpleComponentFactory::createFactory("mycomponent", function() {
        return new MyComponent();
    })
);

// Method 2: Using class factory (recommended for existing component classes)
$sessionManager->registerComponentFactory(
    SimpleComponentFactory::forClass(MyComponent::class)
);
```

### Benefits of Factory Pattern

- **No Cloning Confusion**: Each session gets a fresh component instance
- **Better Event Scope**: Event listeners are registered per component instance
- **Clearer Intent**: Explicit about how components are created
- **Type Safety**: Factory ensures correct component types

## Traditional Usage

### Accessing the Session Manager

```php
use JonasWindmann\CoreAPI\CoreAPI;

// Get the session manager
$sessionManager = CoreAPI::getInstance()->getSessionManager();
```

### Getting a Player's Session

```php
// Get a session for a specific player
$session = $sessionManager->getSessionByPlayer($player);

// Check if the session exists
if ($session !== null) {
    // Do something with the session
}
```

### Creating a Session Component

Create a class that extends `BasePlayerSessionComponent`:

```php
use JonasWindmann\CoreAPI\session\BasePlayerSessionComponent;
use pocketmine\event\player\PlayerMoveEvent;

class ExampleComponent extends BasePlayerSessionComponent {
    /**
     * Get the unique identifier for this component
     */
    public function getId(): string {
        return "example";
    }
    
    /**
     * Called when the component is added to a session
     */
    public function onCreate(): void {
        // Initialize the component
        $player = $this->getPlayer();
        $player->sendMessage("Example component initialized!");
    }
    
    /**
     * Called when the component is removed from a session
     */
    public function onRemove(): void {
        // Clean up the component
        $player = $this->getPlayer();
        $player->sendMessage("Example component removed!");
    }
    
    /**
     * Example event handler
     * Since components implement Listener, they can handle events
     */
    public function onPlayerMove(PlayerMoveEvent $event): void {
        // Only handle events for the player that owns this component
        if ($event->getPlayer() === $this->getPlayer()) {
            // Do something when the player moves
        }
    }
}
```

### Registering a Component (Traditional Method)

Register your component with the session manager to add it to all current and future player sessions:

```php
// Create an instance of your component
$component = new ExampleComponent();

// Register it with the session manager (deprecated - uses cloning internally)
$sessionManager->registerComponent($component);
```

**Note**: The traditional `registerComponent()` method is deprecated. Use `registerComponentFactory()` instead for better control and clarity.

### Accessing Components from a Session

```php
// Get a specific component from a player's session
$component = $session->getComponent("example");

if ($component !== null) {
    // Do something with the component
}

// Get all components
$components = $session->getComponents();
```

## Example Plugin

Here's a complete example of a plugin that uses the Player Session API:

```php
<?php

declare(strict_types=1);

namespace ExamplePlugin;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\session\BasePlayerSessionComponent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\plugin\PluginBase;

class ExamplePlugin extends PluginBase {
    protected function onEnable(): void {
        // Register our component with the session manager
        $sessionManager = CoreAPI::getInstance()->getSessionManager();
        $sessionManager->registerComponent(new ExampleComponent());
    }
}

class ExampleComponent extends BasePlayerSessionComponent {
    public function getId(): string {
        return "example";
    }
    
    public function onCreate(): void {
        $this->getPlayer()->sendMessage("Example component initialized!");
    }
    
    public function onRemove(): void {
        $this->getPlayer()->sendMessage("Example component removed!");
    }
    
    public function onPlayerMove(PlayerMoveEvent $event): void {
        if ($event->getPlayer() === $this->getPlayer()) {
            // Do something when the player moves
        }
    }
}
```

## Built-in CoreAPI Components

CoreAPI includes several built-in session components that are automatically registered:

### Scoreboard Component

The `ScoreboardComponent` manages scoreboard display for individual players:

```php
// Access the scoreboard component for a player
$session = CoreAPI::getInstance()->getSessionManager()->getSessionByPlayer($player);
$component = $session->getComponent("scoreboard");

if ($component !== null) {
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
}
```

**Features:**
- Automatic scoreboard updates based on configured intervals
- Session-based scoreboard management per player
- Automatic cleanup when players disconnect
- Integration with the ScoreboardManager for easy access

**Automatic Registration:**
The scoreboard component is automatically registered when CoreAPI loads, so you don't need to register it manually. It's available for all players as soon as they join the server.