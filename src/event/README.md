# Closure Event API

The Closure Event API allows plugins to register, trigger, and handle events using closures. This provides a flexible and lightweight alternative to traditional event listeners.

## Usage

### Registering an Event

```php
// Get the event manager
$eventManager = CoreAPI::getInstance()->getEventManager();

// Register a new event
$event = $eventManager->registerEvent("my_plugin.my_event");
```

### Registering a Handler

```php
// Register a handler for an event
$handlerId = $eventManager->registerHandler("my_plugin.my_event", function($param1, $param2) {
    // Handle the event
    return "Result";
});
```

### Triggering an Event

```php
// Trigger an event with parameters
$results = $eventManager->triggerEvent("my_plugin.my_event", $param1, $param2);

// $results will be an array of results from all handlers, indexed by handler ID
```

### Unregistering a Handler

```php
// Unregister a handler
$eventManager->unregisterHandler("my_plugin.my_event", $handlerId);
```

### Unregistering an Event

```php
// Unregister an event (this will also unregister all its handlers)
$eventManager->unregisterEvent("my_plugin.my_event");
```

### Checking if an Event Has Handlers

```php
// Check if an event has any handlers
$hasHandlers = $eventManager->hasHandlers("my_plugin.my_event");
```

## Best Practices

1. **Event Naming**: Use a consistent naming convention for events, such as `pluginName.eventName`.
2. **Error Handling**: Always check if an event exists before trying to trigger it or register handlers for it.
3. **Documentation**: Document the events your plugin provides, including the parameters they pass to handlers and what the handlers should return.
4. **Performance**: Be mindful of the performance impact of triggering events with many handlers.

## Example

Here's a complete example of using the Closure Event API:

```php
// Get the event manager
$eventManager = CoreAPI::getInstance()->getEventManager();

// Register a new event
$eventManager->registerEvent("my_plugin.player_action");

// Register a handler for the event
$handlerId = $eventManager->registerHandler("my_plugin.player_action", function(Player $player, string $action) {
    // Log the action
    $player->sendMessage("You performed action: $action");
    return true;
});

// Trigger the event
$results = $eventManager->triggerEvent("my_plugin.player_action", $player, "jump");

// Check the results
foreach ($results as $handlerId => $result) {
    // Do something with the result
}

// Unregister the handler when no longer needed
$eventManager->unregisterHandler("my_plugin.player_action", $handlerId);

// Unregister the event when no longer needed
$eventManager->unregisterEvent("my_plugin.player_action");
```