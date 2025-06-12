# CoreAPI Command System

The CoreAPI Command System provides a simple and flexible framework for creating commands with subcommands and automatic help generation.

## Features

- **Subcommand Support**: Organize complex commands into logical subcommands
- **Automatic Help**: Auto-generated help commands with detailed information
- **Permission System**: Fine-grained permission control for commands and subcommands
- **Fluent Builder API**: Easy command creation with method chaining
- **Error Handling**: Comprehensive error messages and suggestions

## Quick Start

### Creating a Simple SubCommand

```php
use JonasWindmann\CoreAPI\command\SubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class HelloSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct(
            "hello",
            "Say hello to a player",
            "/mycommand hello [player]",
            0, // minimum arguments
            1, // maximum arguments
            "mycommand.hello" // permission
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        if (empty($args)) {
            $sender->sendMessage("Hello, " . $sender->getName() . "!");
        } else {
            $targetName = $args[0];
            $target = $sender->getServer()->getPlayerByPrefix($targetName);

            if ($target instanceof Player) {
                $target->sendMessage("Hello from " . $sender->getName() . "!");
                $sender->sendMessage("Said hello to " . $target->getName());
            } else {
                $sender->sendMessage("§cPlayer not found: " . $targetName);
            }
        }
    }
}
```

### Creating Commands with CommandBuilder

You can also use the fluent `CommandBuilder` for simpler commands:

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

## Creating a Command (Traditional Way)

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

        if (!empty($args)) {
            $sender->sendMessage("With argument: " . $args[0]);
        }
    }
}
```

### Automatic Help Subcommand

By default, all commands that extend `BaseCommand` will have a help subcommand automatically registered. This subcommand displays a list of all available subcommands with their usage and description.

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

## Built-in CoreAPI Commands

CoreAPI includes several built-in commands for managing its features:

### Scoreboard Commands

- **`/coresb` (aliases: `/csb`, `/scoreboard`)** - Main scoreboard management command
  - `/coresb list` - List all available scoreboards
  - `/coresb show <id>` - Display a specific scoreboard
  - `/coresb hide` - Hide your current scoreboard
  - `/coresb manage` - Open form-based management interface

### Custom Item Commands

- **`/customitem` (aliases: `/citem`, `/ci`)** - Custom item management system
  - `/customitem create <id> <name> <type> <base_item>` - Create new custom item
  - `/customitem give <player> <id> [amount]` - Give custom item to player
  - `/customitem list` - List all custom items
  - `/customitem remove <id>` - Remove a custom item
  - `/customitem info <id>` - Show detailed custom item information
