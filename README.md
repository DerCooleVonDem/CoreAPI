# CoreAPI

CoreAPI is a utility plugin that provides a set of APIs for PocketMine-MP plugin developers. It aims to simplify common tasks and provide a consistent way to implement features across plugins.

## Features

### Command System

The command system provides a simple and flexible way to create commands with subcommands. It includes:

- **NEW**: Fluent `CommandBuilder` and `SubCommandBuilder` for easy command creation
- **IMPROVED**: Better error messages with suggestions and available commands
- A base command class that handles subcommand routing
- A subcommand class that handles command execution
- A command manager that handles command registration

For more information, see the [Command System Documentation](src/command/README.md).

### Manager System

The manager system provides a simple and flexible way to create managers for collections of objects. It includes:

- **NEW**: Generic `TypedManager<T>` for type-safe manager implementations
- **IMPROVED**: Optional persistence methods with default implementations
- A manageable interface that objects must implement to be managed
- A base manager class that provides common functionality for managers

For more information, see the [Manager System Documentation](src/manager/README.md).

### Player Session API

The Player Session API provides a way to manage player-specific data and functionality through components. It includes:

- **NEW**: Factory pattern for component creation (no more cloning confusion)
- **IMPROVED**: Proper event listener registration scope
- Automatic session creation and cleanup when players join and leave
- Component-based architecture for modular functionality
- Components are automatically registered as event listeners
- Easy access to the player from any component

For more information, see the [Player Session API Documentation](src/session/README.md).

### Form API

The Form API provides a clean and modern way to create and manage forms in PocketMine-MP. It includes:

- **NEW**: Type-safe `ModalForm`, `SimpleForm`, and `CustomForm` classes
- **IMPROVED**: Clear image handling with `ImageType` enum
- **IMPROVED**: Better callback composition with button-specific callbacks
- Support for all form types: modal, simple, and custom
- A fluent interface for creating and configuring forms
- Automatic validation of form responses
- Convenient helper methods for common form types

For more information, see the [Form API Documentation](src/form/README.md).

### Scoreboard API

The Scoreboard API provides a comprehensive system for managing and displaying scoreboards to players. It includes:

- **NEW**: Automatic display system - scoreboards show to new players based on priority
- **NEW**: Form-based management interface with intuitive UI
- **NEW**: Command-line interface for power users
- **NEW**: Real-time updates with configurable intervals
- Multi-plugin support with priority-based display
- Dynamic tag system for live content replacement
- Session integration for per-player scoreboard management
- Factory pattern for easy scoreboard creation

For more information, see the [Scoreboard API Documentation](src/scoreboard/README.md).

## Installation

1. Download the latest release from the [Releases](https://github.com/JonasWindmann/CoreAPI/releases) page
2. Place the plugin in your server's `plugins` directory
3. Restart your server

## Usage

To use CoreAPI in your plugin, add it as a dependency in your `plugin.yml`:

```yaml
depend: [CoreAPI]
```

Then, in your plugin code, you can access the CoreAPI instance:

```php
use JonasWindmann\CoreAPI\CoreAPI;

$coreAPI = CoreAPI::getInstance();
```

## Quick Start Examples

### Creating Commands (New Fluent API)

```php
use JonasWindmann\CoreAPI\command\CommandBuilder;
use JonasWindmann\CoreAPI\command\SubCommandBuilder;

// Create a command with fluent interface
CommandBuilder::create("mycommand")
    ->description("My awesome command")
    ->aliases("mc", "mycmd")
    ->subCommands(
        SubCommandBuilder::create("hello")
            ->description("Say hello")
            ->executes(function($sender, $args) {
                $sender->sendMessage("Hello!");
            })
            ->build()
    )
    ->build(); // Automatically registered!
```

### Creating Forms (New Type-Safe API)

```php
use JonasWindmann\CoreAPI\form\ModalForm;
use JonasWindmann\CoreAPI\form\SimpleForm;

// Type-safe modal form
$modal = new ModalForm("Confirm", "Are you sure?", "Yes", "No",
    function($player, $response) {
        $player->sendMessage($response ? "Confirmed!" : "Cancelled");
    });

// Type-safe simple form with clear image handling
$simple = new SimpleForm("Menu", "Choose an option:")
    ->buttonWithImage("Teleport", "textures/ui/teleport.png")
    ->button("Settings", null, function($player) {
        $player->sendMessage("Opening settings...");
    });
```

### Session Components (New Factory Pattern)

```php
use JonasWindmann\CoreAPI\session\SimpleComponentFactory;

$sessionManager = CoreAPI::getInstance()->getSessionManager();
$sessionManager->registerComponentFactory(
    SimpleComponentFactory::createFactory("mycomponent", function() {
        return new MyComponent();
    })
);
```

### Scoreboards (New Automatic System)

```php
use JonasWindmann\CoreAPI\scoreboard\factory\ScoreboardFactory;

// Create a scoreboard that automatically displays to new players
$scoreboard = ScoreboardFactory::createServerInfo(
    "server_info",
    "§6My Server",
    "MyPlugin",
    true // Auto-display enabled
);

$scoreboard->setPriority(100); // High priority = shows by default
CoreAPI::getInstance()->getScoreboardManager()->registerScoreboard($scoreboard);

// Players can manage scoreboards with: /coresb manage
```

## License

This plugin is licensed under the MIT License. See the [LICENSE](LICENSE) file for more information.
