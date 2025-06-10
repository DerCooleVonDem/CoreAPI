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
     */
    public function createForm(FormType $formType, ?callable $callable = null): Form {
        return new Form($formType, $callable);
    }

    /**
     * Create a new modal form
     * 
     * @param callable|null $callable Callback function to be called when the form is submitted
     * @return Form The created form
     */
    public function createModalForm(?callable $callable = null): Form {
        return $this->createForm(FormType::MODAL, $callable);
    }

    /**
     * Create a new simple form
     * 
     * @param callable|null $callable Callback function to be called when the form is submitted
     * @return Form The created form
     */
    public function createSimpleForm(?callable $callable = null): Form {
        return $this->createForm(FormType::SIMPLE, $callable);
    }

    /**
     * Create a new custom form
     * 
     * @param callable|null $callable Callback function to be called when the form is submitted
     * @return Form The created form
     */
    public function createCustomForm(?callable $callable = null): Form {
        return $this->createForm(FormType::CUSTOM, $callable);
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
