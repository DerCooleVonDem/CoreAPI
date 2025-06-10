<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\form;

use pocketmine\plugin\Plugin;

/**
 * Manager class for creating and managing forms
 */
class FormManager {
    /** @var Plugin */
    private Plugin $plugin;

    /**
     * FormManager constructor
     * 
     * @param Plugin $plugin The plugin instance
     */
    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Create a new form with the specified type
     *
     * @param FormType $formType The type of form to create
     * @param callable|null $callable Callback function to be called when the form is submitted
     * @return Form The created form
     * @deprecated Use createModalForm(), createSimpleForm(), or createCustomForm() instead
     */
    public function createForm(FormType $formType, ?callable $callable = null): Form {
        return new Form($formType, $callable);
    }

    /**
     * Create a modal form
     *
     * @param string $title The form title
     * @param string $content The form content
     * @param string $button1Text The first button text (returns true)
     * @param string $button2Text The second button text (returns false)
     * @param callable|null $callable Callback function (Player $player, bool $response): void
     * @return ModalForm The created modal form
     */
    public function createModalForm(
        string $title = "",
        string $content = "",
        string $button1Text = "Yes",
        string $button2Text = "No",
        ?callable $callable = null
    ): ModalForm {
        return new ModalForm($title, $content, $button1Text, $button2Text, $callable);
    }

    /**
     * Create a simple form
     *
     * @param string $title The form title
     * @param string $content The form content
     * @param callable|null $callable Callback function (Player $player, ?int $buttonIndex): void
     * @return SimpleForm The created simple form
     */
    public function createSimpleForm(string $title = "", string $content = "", ?callable $callable = null): SimpleForm {
        return new SimpleForm($title, $content, $callable);
    }

    /**
     * Create a custom form
     *
     * @param string $title The form title
     * @param callable|null $callable Callback function (Player $player, ?array $response): void
     * @return CustomForm The created custom form
     */
    public function createCustomForm(string $title = "", ?callable $callable = null): CustomForm {
        return new CustomForm($title, $callable);
    }

    /**
     * Create a confirmation dialog
     * A convenience method for creating a modal form with yes/no buttons
     * 
     * @param string $title The dialog title
     * @param string $content The dialog content
     * @param callable|null $callable Callback function to be called when the form is submitted
     * @param string $yesText Text for the "yes" button
     * @param string $noText Text for the "no" button
     * @return Form The created form
     */
    public function createConfirmationDialog(
        string $title, 
        string $content, 
        ?callable $callable = null, 
        string $yesText = "Yes", 
        string $noText = "No"
    ): Form {
        return $this->createModalForm($callable)
            ->setTitle($title)
            ->setContent($content)
            ->setButton1($yesText)
            ->setButton2($noText);
    }

    /**
     * Create a message dialog
     * A convenience method for creating a simple form with a single "OK" button
     * 
     * @param string $title The dialog title
     * @param string $content The dialog content
     * @param callable|null $callable Callback function to be called when the form is submitted
     * @param string $buttonText Text for the button
     * @return Form The created form
     */
    public function createMessageDialog(
        string $title, 
        string $content, 
        ?callable $callable = null, 
        string $buttonText = "OK"
    ): Form {
        return $this->createSimpleForm($callable)
            ->setTitle($title)
            ->setContent($content)
            ->addButton($buttonText);
    }

    /**
     * Get the plugin instance
     * 
     * @return Plugin The plugin instance
     */
    public function getPlugin(): Plugin {
        return $this->plugin;
    }
}
