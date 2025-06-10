# CoreAPI Command System

This directory contains the command system for the CoreAPI plugin. It provides a simple and flexible way to create commands with subcommands.

## Usage

### Creating a Command

To create a command, extend the `BaseCommand` class:

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
