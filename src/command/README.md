# CoreAPI Command System

This directory contains the command system for the CoreAPI plugin. It provides a simple and flexible way to create commands with subcommands.

**🎉 NEW**: Fluent builders for easier command creation with better error messages and automatic registration!

## Quick Start (Recommended - New Fluent API)

### Creating Commands with CommandBuilder

The easiest way to create commands is using the new fluent `CommandBuilder`:

```php
use JonasWindmann\CoreAPI\command\CommandBuilder;
use JonasWindmann\CoreAPI\command\SubCommandBuilder;

CommandBuilder::create("mycommand")
    ->description("My awesome command")
    ->aliases("mc", "mycmd")
    ->permission("mycommand.use")
    ->subCommands(
        SubCommandBuilder::create("hello")
            ->description("Say hello to a player")
            ->usage("/mycommand hello [player]")
            ->args(0, 1) // min 0, max 1 arguments
            ->executes(function($sender, $args) {
                if (empty($args)) {
                    $sender->sendMessage("Hello, " . $sender->getName() . "!");
                } else {
                    $sender->sendMessage("Hello, " . $args[0] . "!");
                }
            })
            ->build(),

        SubCommandBuilder::create("info")
            ->description("Show information")
            ->permission("mycommand.info")
            ->exactArgs(0) // exactly 0 arguments
            ->executes(function($sender, $args) {
                $sender->sendMessage("This is my command!");
            })
            ->build()
    )
    ->build(); // Automatically registered with CoreAPI!
```

### Benefits of the New API

- **Automatic Registration**: No need to manually register commands
- **Better Error Messages**: Users get helpful suggestions when they make mistakes
- **Fluent Interface**: Easy to read and write
- **Type Safety**: Less prone to errors
- **Sensible Defaults**: Less boilerplate code

## Advanced Usage (Traditional API)

### Creating a Command (Traditional Way)

To create a command using the traditional approach, extend the `BaseCommand` class:

```php
use JonasWindmann\CoreAPI\command\BaseCommand;
use JonasWindmann\CoreAPI\CoreAPI;

class MyCommand extends BaseCommand {
    public function __construct() {
        parent::__construct(
            "mycommand",                  // Command name
            "Description of my command",  // Description
            "/mycommand <subcommand>",    // Usage
            ["mc", "mycmd"]               // Aliases (optional)
        );

        // Register subcommands
        $this->registerSubCommands([
            new MySubCommand1(),
            new MySubCommand2()
        ]);

        // Register the command to the server
        CoreAPI::getInstance()->getCommandManager()->registerCommand($this);
    }
}
```

### Creating a Subcommand

To create a subcommand, extend the `SubCommand` class:

```php
use JonasWindmann\CoreAPI\command\SubCommand;
use pocketmine\command\CommandSender;

class MySubCommand extends SubCommand {
    public function __construct() {
        parent::__construct(
            "mysubcommand",                  // Subcommand name
            "Description of my subcommand",  // Description
            "/mycommand mysubcommand [arg]", // Usage
            0,                               // Minimum arguments
            1,                               // Maximum arguments (-1 for unlimited)
            "mycommand.mysubcommand"         // Permission (optional)
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        // Implement your subcommand logic here
        $sender->sendMessage("You executed mysubcommand!");
    }
}
```

### Automatic Help Subcommand

By default, all commands that extend `BaseCommand` will have a help subcommand automatically registered. This subcommand displays a list of all available subcommands with their usage and description.

The help subcommand is automatically executed when a user runs the command without any arguments, making it easier for users to discover available subcommands.

#### Disabling the Automatic Help Subcommand

If you don't want the automatic help subcommand, you can disable it by passing `false` as the fifth parameter to the `BaseCommand` constructor:

```php
public function __construct() {
    parent::__construct(
        "mycommand",                  // Command name
        "Description of my command",  // Description
        "/mycommand <subcommand>",    // Usage
        ["mc", "mycmd"],              // Aliases (optional)
        false                         // Disable automatic help subcommand
    );
}
```

#### Overriding the Default Help Subcommand

You can also override the default help subcommand by registering your own subcommand with the name "help":

```php
$this->registerSubCommand(new MyCustomHelpSubCommand());
```

### Registering Commands

You can register commands using the CommandManager:

```php
// Get the command manager
$commandManager = CoreAPI::getInstance()->getCommandManager();

// Register a single command
$commandManager->registerCommand(new MyCommand());

// Register multiple commands
$commandManager->registerCommands([
    new MyCommand1(),
    new MyCommand2()
]);
```

### Unregistering Commands

You can unregister commands using the CommandManager:

```php
// Unregister a command
$commandManager->unregisterCommand("mycommand");
```

## Built-in CoreAPI Commands

CoreAPI includes several built-in commands for managing its features:

### Scoreboard Commands

- **`/coresb` (aliases: `/csb`, `/scoreboard`)** - Main scoreboard management command
  - `/coresb list` - List all available scoreboards with details
  - `/coresb show <id>` - Display a specific scoreboard
  - `/coresb hide` - Hide your current scoreboard
  - `/coresb manage` - Open form-based management interface
  - `/coresb info [id]` - Show detailed information about a scoreboard

### Test Commands (Debug)

- **`/testscoreboard`** - Debug command for testing scoreboard functionality
  - Manually trigger scoreboard updates
  - View current scoreboard status
  - Debug auto-update functionality

### Permissions

- `coreapi.scoreboard.use` - Basic scoreboard usage
- `coreapi.scoreboard.list` - List scoreboards
- `coreapi.scoreboard.show` - Display scoreboards
- `coreapi.scoreboard.hide` - Hide scoreboards
- `coreapi.scoreboard.manage` - Access form management
- `coreapi.scoreboard.info` - View scoreboard information
- `coreapi.admin` - Administrative commands (test commands)
