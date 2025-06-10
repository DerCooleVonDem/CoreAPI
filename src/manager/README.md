# CoreAPI Manager System

This directory contains the manager system for the CoreAPI plugin. It provides a simple and flexible way to create managers for collections of objects.

## Overview

The manager system consists of two main components:

1. **Manageable Interface**: An interface that objects must implement to be managed by a manager.
2. **BaseManager Class**: An abstract class that provides common functionality for managers.

## Usage

### Creating a Manageable Object

To create an object that can be managed by a manager, implement the `Manageable` interface:

```php
use JonasWindmann\CoreAPI\manager\Manageable;

class MyItem implements Manageable {
    private string $id;
    private string $name;
    
    public function __construct(string $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }
    
    public function getId(): string {
        return $this->id;
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    // Add other methods as needed
}
```

### Creating a Manager

To create a manager for your objects, extend the `BaseManager` class:

```php
use JonasWindmann\CoreAPI\manager\BaseManager;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;

class MyManager extends BaseManager {
    private Config $config;
    
    public function __construct(Plugin $plugin) {
        parent::__construct($plugin);
        
        // Initialize config
        $this->config = new Config($plugin->getDataFolder() . "my_items.yml", Config::YAML);
        
        // Load items from config
        $this->loadItems();
    }
    
    // Implement abstract methods
    
    public function loadItems(): void {
        // Load items from config or database
        $data = $this->config->getAll();
        
        foreach ($data as $id => $itemData) {
            $item = new MyItem($id, $itemData['name']);
            $this->items[$id] = $item;
        }
    }
    
    public function saveItems(): void {
        // Save items to config or database
        $data = [];
        
        foreach ($this->items as $id => $item) {
            if ($item instanceof MyItem) {
                $data[$id] = [
                    'name' => $item->getName()
                ];
            }
        }
        
        $this->config->setAll($data);
        $this->config->save();
    }
    
    // Add custom methods
    
    public function createItem(string $id, string $name): ?MyItem {
        // Check if an item with this ID already exists
        if ($this->getItem($id) !== null) {
            return null;
        }
        
        // Create the item
        $item = new MyItem($id, $name);
        
        // Add the item to the manager
        $this->addItem($item);
        
        // Save items to config
        $this->saveItems();
        
        return $item;
    }
    
    // Add other methods as needed
}
```

### Using a Manager

To use a manager in your plugin:

```php
use pocketmine\plugin\PluginBase;

class MyPlugin extends PluginBase {
    private MyManager $myManager;
    
    protected function onEnable(): void {
        // Create the manager
        $this->myManager = new MyManager($this);
        
        // Use the manager
        $item = $this->myManager->createItem("item1", "My First Item");
        
        if ($item !== null) {
            $this->getLogger()->info("Created item: " . $item->getName());
        }
    }
    
    public function getMyManager(): MyManager {
        return $this->myManager;
    }
}
```

## Example

See the `example/manager` directory for a complete example of how to use the manager system.

## Best Practices

1. **Use Descriptive Names**: Choose clear and descriptive names for your managers and methods.
2. **Handle Errors Gracefully**: Include proper error handling and logging in your manager methods.
3. **Type Safety**: Use type hints and return types to ensure type safety.
4. **Documentation**: Document your manager classes and methods with PHPDoc comments.
5. **Persistence**: Implement robust loading and saving mechanisms for your managed objects.
6. **Validation**: Validate input data before creating or updating objects.
7. **Singleton Pattern**: Consider using the SingletonTrait for managers that should be globally accessible.