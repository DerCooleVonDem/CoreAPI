# CoreAPI

CoreAPI is a utility plugin that provides a set of APIs for PocketMine-MP plugin developers. It aims to simplify common tasks and provide a consistent way to implement features across plugins.

## Features

### Command System

The command system provides a simple and flexible way to create commands with subcommands. It includes:

- A base command class that handles subcommand routing
- A subcommand class that handles command execution
- A command manager that handles command registration

For more information, see the [Command System Documentation](src/command/README.md).

### Manager System

The manager system provides a simple and flexible way to create managers for collections of objects. It includes:

- A manageable interface that objects must implement to be managed
- A base manager class that provides common functionality for managers

For more information, see the [Manager System Documentation](src/manager/README.md).

### Player Session API

The Player Session API provides a way to manage player-specific data and functionality through components. It includes:

- Automatic session creation and cleanup when players join and leave
- Component-based architecture for modular functionality
- Components are automatically registered as event listeners
- Easy access to the player from any component

For more information, see the [Player Session API Documentation](src/session/README.md).

### Form API

The Form API provides a clean and modern way to create and manage forms in PocketMine-MP. It includes:

- Support for all form types: modal, simple, and custom
- A fluent interface for creating and configuring forms
- Automatic validation of form responses
- Convenient helper methods for common form types

For more information, see the [Form API Documentation](src/form/README.md).

### Examples

The plugin includes examples of how to use the APIs. These examples are automatically registered when the plugin is running in a development environment.

For more information, see the [Examples Documentation](src/example/README.md).

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

## License

This plugin is licensed under the MIT License. See the [LICENSE](LICENSE) file for more information.
