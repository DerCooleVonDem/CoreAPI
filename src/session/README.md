# Player Session API

The Player Session API provides a way to manage player-specific data and functionality through components. Each player that joins the server gets a session object, which can have components attached to it.

## Features

- Automatic session creation and cleanup when players join and leave
- Component-based architecture for modular functionality
- Components are automatically registered as event listeners
- Easy access to the player from any component

## Usage

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

### Registering a Component

Register your component with the session manager to add it to all current and future player sessions:

```php
// Create an instance of your component
$component = new ExampleComponent();

// Register it with the session manager
$sessionManager->registerComponent($component);
```

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