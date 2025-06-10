# CoreAPI Form API Documentation

## Introduction

The CoreAPI Form API provides a powerful, flexible, and user-friendly way to create and manage forms in PocketMine-MP plugins. Forms are graphical user interfaces that allow players to interact with your plugin through buttons, input fields, toggles, and other UI elements.

This API simplifies the process of creating forms, handling responses, and implementing navigation between different forms, allowing you to focus on your plugin's functionality rather than UI implementation details.

## Table of Contents

1. [Key Features](#key-features)
2. [Getting Started](#getting-started)
   - [Accessing the Form Manager](#accessing-the-form-manager)
   - [Form Types Overview](#form-types-overview)
3. [Creating Forms](#creating-forms)
   - [Using FormType](#using-formtype)
   - [Using Convenience Methods](#using-convenience-methods)
   - [Setting Callbacks](#setting-callbacks)
4. [Form Types in Detail](#form-types-in-detail)
   - [Modal Forms](#modal-forms)
   - [Simple Forms](#simple-forms)
   - [Custom Forms](#custom-forms)
5. [Advanced Features](#advanced-features)
   - [Predefined Buttons](#predefined-buttons)
   - [Form Navigation](#form-navigation)
   - [Convenience Dialogs](#convenience-dialogs)
6. [Response Handling](#response-handling)
   - [Modal Form Responses](#modal-form-responses)
   - [Simple Form Responses](#simple-form-responses)
   - [Custom Form Responses](#custom-form-responses)
7. [Best Practices](#best-practices)
8. [Examples](#examples)

## Key Features

- **Unified API**: Create any type of form (modal, simple, or custom) using a consistent API
- **Type Safety**: Automatic validation of form responses with appropriate error handling
- **Fluent Interface**: Method chaining for concise and readable code
- **Form Navigation**: Built-in support for navigating between forms with back buttons
- **Predefined Buttons**: Common button types with built-in behaviors
- **Convenience Methods**: Shorthand methods for creating common form types
- **Comprehensive Documentation**: Detailed explanations and examples for all features

## Getting Started

### Accessing the Form Manager

The FormManager is the central point for creating and managing forms. Access it through the CoreAPI instance:

```php
use JonasWindmann\CoreAPI\CoreAPI;

// Get the form manager
$formManager = CoreAPI::getInstance()->getFormManager();
```

### Form Types Overview

The Form API supports three types of forms:

1. **Modal Forms**: Simple dialogs with two buttons (yes/no, confirm/cancel)
2. **Simple Forms**: Forms with a list of buttons for selection
3. **Custom Forms**: Complex forms with various input elements (text fields, toggles, sliders, etc.)

## Creating Forms

### Using FormType

You can create forms directly using the Form class with a FormType parameter:

```php
use JonasWindmann\CoreAPI\form\Form;
use JonasWindmann\CoreAPI\form\FormType;
use pocketmine\player\Player;

// Create a simple form
$form = new Form(FormType::SIMPLE, function(Player $player, $data) {
    // Handle form response
});

// Create a modal form
$form = new Form(FormType::MODAL, function(Player $player, $data) {
    // Handle form response
});

// Create a custom form
$form = new Form(FormType::CUSTOM, function(Player $player, $data) {
    // Handle form response
});
```

### Using Convenience Methods

The FormManager provides convenience methods for creating each type of form:

```php
// Create a modal form
$modalForm = $formManager->createModalForm(function(Player $player, $data) {
    // Handle form response
});

// Create a simple form
$simpleForm = $formManager->createSimpleForm(function(Player $player, $data) {
    // Handle form response
});

// Create a custom form
$customForm = $formManager->createCustomForm(function(Player $player, $data) {
    // Handle form response
});
```

### Setting Callbacks

You can set or change the callback function after creating a form:

```php
// Create a form without a callback
$form = new Form(FormType::SIMPLE);

// Configure the form
$form->setTitle("Example Form")
    ->setContent("This is an example form")
    ->addButton("Click Me");

// Set the callback later
$form->setCallable(function(Player $player, $data) {
    $player->sendMessage("You clicked button $data");
});
```

## Form Types in Detail

### Modal Forms

Modal forms are simple dialogs with two buttons, typically used for yes/no questions or confirmations.

```php
// Create a modal form
$form = $formManager->createModalForm(function(Player $player, bool $response) {
    if ($response) {
        $player->sendMessage("You confirmed the action!");
    } else {
        $player->sendMessage("You cancelled the action.");
    }
});

// Configure the form
$form->setTitle("Confirmation")
    ->setContent("Are you sure you want to proceed with this action?")
    ->setButton1("Confirm")  // Clicking this returns true
    ->setButton2("Cancel");  // Clicking this returns false

// Send the form to a player
$form->sendToPlayer($player);
```

**Properties:**
- `title`: The form title
- `content`: The message or question
- `button1`: Text for the first button (returns `true` when clicked)
- `button2`: Text for the second button (returns `false` when clicked)

**Response:**
- `true` if Button 1 was clicked
- `false` if Button 2 was clicked
- `null` if the form was closed without clicking a button

### Simple Forms

Simple forms have a list of buttons that the player can click to make a selection.

```php
// Create a simple form
$form = $formManager->createSimpleForm(function(Player $player, $data) {
    if ($data === null) {
        $player->sendMessage("You closed the form without making a selection.");
        return;
    }

    // Handle the selection based on button index or label
    switch ($data) {
        case "teleport":
            $player->sendMessage("Teleporting you to spawn...");
            // Teleport logic here
            break;
        case "shop":
            $player->sendMessage("Opening shop...");
            // Shop logic here
            break;
        case "settings":
            $player->sendMessage("Opening settings...");
            // Settings logic here
            break;
    }
});

// Configure the form
$form->setTitle("Main Menu")
    ->setContent("Welcome! Please select an option:")
    ->addButton("Teleport to Spawn", -1, "", "teleport")  // Text, image type, image path, label
    ->addButton("Shop", -1, "", "shop")
    ->addButton("Settings", -1, "", "settings");

// Send the form to a player
$form->sendToPlayer($player);
```

**Properties:**
- `title`: The form title
- `content`: The message or instructions
- `buttons`: A list of buttons, each with:
  - `text`: The button text
  - `image`: Optional image (type and path)
  - `label`: Optional label for identifying the button in the response

**Response:**
- The index of the clicked button (0-based) or the button's label if provided
- `null` if the form was closed without clicking a button

### Custom Forms

Custom forms can have various input elements for collecting user input.

```php
// Create a custom form
$form = $formManager->createCustomForm(function(Player $player, ?array $data) {
    if ($data === null) {
        $player->sendMessage("You closed the form without submitting.");
        return;
    }

    // Access the input values by their labels
    $name = $data["name"];
    $age = $data["age"];
    $notifications = $data["notifications"] ? "enabled" : "disabled";
    $color = $data["color"];

    $player->sendMessage("Profile updated: Name: $name, Age: $age");
    $player->sendMessage("Notifications: $notifications, Favorite color: $color");
});

// Configure the form
$form->setTitle("User Profile")
    ->addLabel("Please fill out your profile information:")
    ->addInput("Name:", "Enter your name", $player->getName(), "name")
    ->addSlider("Age:", 1, 100, 1, 25, "age")
    ->addToggle("Enable notifications", true, "notifications")
    ->addDropdown("Favorite color:", ["Red", "Green", "Blue", "Yellow"], 0, "color");

// Send the form to a player
$form->sendToPlayer($player);
```

**Available Elements:**
- `Label`: Display text (no input)
- `Input`: Text field
- `Toggle`: On/off switch (checkbox)
- `Slider`: Numeric slider with min/max values
- `StepSlider`: Selection from a list of options with a slider
- `Dropdown`: Selection from a dropdown list

**Response:**
- An associative array with element labels as keys and their values
- `null` if the form was closed without submitting

## Advanced Features

### Predefined Buttons

The Form API provides predefined buttons with specific behaviors:

```php
use JonasWindmann\CoreAPI\form\ButtonType;

// Create a simple form
$form = new Form(FormType::SIMPLE);
$form->setTitle("Predefined Buttons Example");

// Add predefined buttons
$form->addPredefinedButton(ButtonType::BACK);     // Goes back to the previous form
$form->addPredefinedButton(ButtonType::CLOSE);    // Closes the form
$form->addPredefinedButton(ButtonType::HOME);     // A "Home" button
$form->addPredefinedButton(ButtonType::REFRESH);  // A "Refresh" button
$form->addPredefinedButton(ButtonType::SETTINGS); // A "Settings" button
$form->addPredefinedButton(ButtonType::CONFIRM);  // A "Confirm" button
$form->addPredefinedButton(ButtonType::CANCEL);   // A "Cancel" button

// Open another form when clicked
$anotherForm = new Form(FormType::SIMPLE);
$anotherForm->setTitle("Another Form");
$form->addPredefinedButton(ButtonType::OPEN_FORM, $anotherForm);
```

### Form Navigation

The Form API supports navigation between forms with a built-in history system:

```php
// Create a main menu form
$mainMenu = new Form(FormType::SIMPLE);
$mainMenu->setTitle("Main Menu")
    ->setContent("Select a category:");

// Create sub-menu forms
$profileForm = new Form(FormType::SIMPLE);
$profileForm->setTitle("Profile")
    ->setContent("Your profile settings:")
    ->addBackButton();  // Adds a button to return to the previous form

$settingsForm = new Form(FormType::SIMPLE);
$settingsForm->setTitle("Settings")
    ->setContent("Game settings:")
    ->addBackButton();  // Adds a button to return to the previous form

// Add buttons to open the sub-menus
$mainMenu->addOpenFormButton($profileForm, "Profile");  // Opens the profile form
$mainMenu->addOpenFormButton($settingsForm, "Settings"); // Opens the settings form

// Send the main menu to the player
$mainMenu->sendToPlayer($player);
```

### Convenience Dialogs

The FormManager provides convenience methods for common dialog types:

```php
// Create a confirmation dialog
$form = $formManager->createConfirmationDialog(
    "Delete Item",
    "Are you sure you want to delete this item? This action cannot be undone.",
    function(Player $player, bool $response) {
        if ($response) {
            $player->sendMessage("Item deleted successfully!");
            // Delete item logic here
        } else {
            $player->sendMessage("Deletion cancelled.");
        }
    },
    "Delete",  // Text for the confirm button
    "Cancel"   // Text for the cancel button
);

// Create a message dialog
$form = $formManager->createMessageDialog(
    "Information",
    "Your account has been updated successfully!",
    function(Player $player, $data) {
        // This will be called when the player clicks the button
    },
    "OK"  // Text for the button
);
```

## Response Handling

### Modal Form Responses

Modal forms return a boolean value:

```php
$form = $formManager->createModalForm(function(Player $player, ?bool $response) {
    if ($response === null) {
        // Form was closed without clicking a button
        return;
    }

    if ($response === true) {
        // Button 1 was clicked
    } else {
        // Button 2 was clicked
    }
});
```

### Simple Form Responses

Simple forms return the button index or label:

```php
$form = $formManager->createSimpleForm(function(Player $player, $data) {
    if ($data === null) {
        // Form was closed without clicking a button
        return;
    }

    // If you used labels when adding buttons
    if ($data === "teleport") {
        // Teleport button was clicked
    }

    // If you didn't use labels
    if ($data === 0) {
        // First button was clicked
    }
});
```

### Custom Form Responses

Custom forms return an associative array with element labels as keys:

```php
$form = $formManager->createCustomForm(function(Player $player, ?array $data) {
    if ($data === null) {
        // Form was closed without submitting
        return;
    }

    // Access values by their labels
    $name = $data["name"];                // Input field value
    $age = $data["age"];                  // Slider value
    $notifications = $data["notifications"]; // Toggle value (boolean)
    $color = $data["color"];              // Dropdown selected index

    // If you didn't provide labels, the indices will be numeric
    $name = $data[0];      // First element
    $age = $data[1];       // Second element
    // etc.
});
```

## Best Practices

1. **Always Handle Null Responses**: Check for `null` responses, which indicate that the player closed the form without submitting.

2. **Use Descriptive Titles and Labels**: Make forms and elements have clear, descriptive titles and labels.

3. **Validate User Input**: Even though the Form API validates the type of responses, validate the content of user input.

4. **Use Method Chaining**: Take advantage of method chaining for concise and readable code.

5. **Provide Element Labels**: Use string labels for form elements to make response data more readable and maintainable.

6. **Keep Forms Simple**: Avoid overwhelming players with too many elements or options in a single form.

7. **Provide Feedback**: Always provide feedback to players after they submit a form.

8. **Organize Complex UIs**: For complex UIs, use multiple forms with navigation rather than one large form.

9. **Handle Errors Gracefully**: Catch and handle exceptions that might occur during form processing.

10. **Test on Different Devices**: Forms may display differently on different devices, so test on various screen sizes.