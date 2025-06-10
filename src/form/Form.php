<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\form;

use InvalidArgumentException;
use pocketmine\form\Form as IForm;
use pocketmine\player\Player;

/**
 * Abstract base class for all forms
 * Implements the PocketMine-MP Form interface
 */
class Form implements IForm {
    /** @var array Form data that will be serialized to JSON */
    protected array $data = [];

    /** @var callable|null Callback function to be called when the form is submitted */
    private $callable;

    /**
     * Form constructor
     * 
     * @param FormType|null $formType The type of form to create (MODAL, SIMPLE, CUSTOM)
     * @param callable|null $callable Callback function to be called when the form is submitted
     */
    public function __construct(?FormType $formType = null, ?callable $callable = null) {
        $this->callable = $callable;

        // Initialize form data based on form type
        if ($formType !== null) {
            switch ($formType) {
                case FormType::MODAL:
                    $this->data["type"] = "modal";
                    $this->data["title"] = "";
                    $this->data["content"] = "";
                    $this->data["button1"] = "";
                    $this->data["button2"] = "";
                    break;
                case FormType::SIMPLE:
                    $this->data["type"] = "form";
                    $this->data["title"] = "";
                    $this->data["content"] = "";
                    $this->data["buttons"] = [];
                    break;
                case FormType::CUSTOM:
                    $this->data["type"] = "custom_form";
                    $this->data["title"] = "";
                    $this->data["content"] = [];
                    break;
            }
        }
    }

    /**
     * Send the form to a player
     * 
     * @param Player $player The player to send the form to
     * @throws InvalidArgumentException If the player is invalid
     */
    public function sendToPlayer(Player $player): void {
        $player->sendForm($this);
    }

    /**
     * Get the callback function
     * 
     * @return callable|null The callback function
     */
    public function getCallable(): ?callable {
        return $this->callable;
    }

    /**
     * Set the callback function
     * 
     * @param callable|null $callable The callback function
     * @return $this For method chaining
     */
    public function setCallable(?callable $callable): self {
        $this->callable = $callable;
        return $this;
    }

    /**
     * Handle the form response
     * This method is called by PocketMine-MP when the player submits the form
     * 
     * @param Player $player The player who submitted the form
     * @param mixed $data The form response data
     */
    public function handleResponse(Player $player, $data): void {
        $this->processData($data);
        $callable = $this->getCallable();
        if ($callable !== null) {
            $callable($player, $data);
        }
    }

    /**
     * Process the form response data
     * This method should be overridden by child classes to validate and process the response data
     * 
     * @param mixed $data The form response data
     */
    public function processData(&$data): void {
        // Base implementation does nothing
    }

    /**
     * Set the form title
     * 
     * @param string $title The form title
     * @return $this For method chaining
     */
    public function setTitle(string $title): self {
        $this->data["title"] = $title;
        return $this;
    }

    /**
     * Get the form title
     * 
     * @return string The form title
     */
    public function getTitle(): string {
        return $this->data["title"] ?? "";
    }

    /**
     * Set the form content/message
     * 
     * @param string $content The form content
     * @return $this For method chaining
     */
    public function setContent(string $content): self {
        $this->data["content"] = $content;
        return $this;
    }

    /**
     * Get the form content
     * 
     * @return string The form content
     */
    public function getContent(): string {
        return $this->data["content"] ?? "";
    }

    /**
     * Add a button to the form (SimpleForm only)
     * 
     * @param string $text The button text
     * @param int $imageType The image type (SimpleForm::IMAGE_TYPE_PATH or SimpleForm::IMAGE_TYPE_URL)
     * @param string $imagePath The path or URL to the image
     * @param mixed $label A label for the button (used in the response data)
     * @return $this For method chaining
     * @throws \InvalidArgumentException If the form is not a SimpleForm
     */
    public function addButton(string $text, int $imageType = -1, string $imagePath = "", $label = null): self {
        if (!isset($this->data["buttons"])) {
            throw new \InvalidArgumentException("Cannot add button to non-SimpleForm");
        }

        $button = ["text" => $text];

        if ($imageType !== -1) {
            $button["image"]["type"] = $imageType === 0 ? "path" : "url";
            $button["image"]["data"] = $imagePath;
        }

        $this->data["buttons"][] = $button;

        return $this;
    }

    /**
     * Add a predefined button to the form (SimpleForm only)
     * 
     * @param ButtonType $buttonType The type of predefined button to add
     * @param mixed $data Additional data for the button (e.g., form to open for OPEN_FORM)
     * @return $this For method chaining
     * @throws \InvalidArgumentException If the form is not a SimpleForm
     */
    public function addPredefinedButton(ButtonType $buttonType, $data = null): self {
        if (!isset($this->data["buttons"])) {
            throw new \InvalidArgumentException("Cannot add button to non-SimpleForm");
        }

        switch ($buttonType) {
            case ButtonType::BACK:
                return $this->addBackButton();
            case ButtonType::CLOSE:
                return $this->addCloseButton();
            case ButtonType::OPEN_FORM:
                if (!($data instanceof Form)) {
                    throw new \InvalidArgumentException("Data for OPEN_FORM button must be a Form instance");
                }
                return $this->addOpenFormButton($data);
            case ButtonType::HOME:
                return $this->addButton("Home");
            case ButtonType::REFRESH:
                return $this->addButton("Refresh");
            case ButtonType::SETTINGS:
                return $this->addButton("Settings");
            case ButtonType::CONFIRM:
                return $this->addButton("Confirm");
            case ButtonType::CANCEL:
                return $this->addButton("Cancel");
        }

        return $this;
    }

    /**
     * Add a back button to the form (SimpleForm only)
     * When clicked, this button will open the previous form in the player's form history
     * 
     * @param string $text The button text
     * @return $this For method chaining
     * @throws \InvalidArgumentException If the form is not a SimpleForm
     */
    public function addBackButton(string $text = "Back"): self {
        if (!isset($this->data["buttons"])) {
            throw new \InvalidArgumentException("Cannot add button to non-SimpleForm");
        }

        $originalCallback = $this->callable;

        $this->callable = function(Player $player, $data) use ($originalCallback) {
            if ($data === count($this->data["buttons"]) - 1) { // If the back button was clicked
                $previousForm = FormHistory::getInstance()->getPreviousForm($player);
                if ($previousForm !== null) {
                    $previousForm->sendToPlayer($player);
                }
                return;
            }

            if ($originalCallback !== null) {
                $originalCallback($player, $data);
            }
        };

        return $this->addButton($text);
    }

    /**
     * Add a close button to the form (SimpleForm only)
     * When clicked, this button will simply close the form
     * 
     * @param string $text The button text
     * @return $this For method chaining
     * @throws \InvalidArgumentException If the form is not a SimpleForm
     */
    public function addCloseButton(string $text = "Close"): self {
        if (!isset($this->data["buttons"])) {
            throw new \InvalidArgumentException("Cannot add button to non-SimpleForm");
        }

        return $this->addButton($text);
    }

    /**
     * Add a button that opens another form (SimpleForm only)
     * When clicked, this button will open the specified form
     * 
     * @param Form $form The form to open
     * @param string $text The button text
     * @return $this For method chaining
     * @throws \InvalidArgumentException If the form is not a SimpleForm
     */
    public function addOpenFormButton(Form $form, string $text = "Next"): self {
        if (!isset($this->data["buttons"])) {
            throw new \InvalidArgumentException("Cannot add button to non-SimpleForm");
        }

        $originalCallback = $this->callable;

        $this->callable = function(Player $player, $data) use ($originalCallback, $form) {
            if ($data === count($this->data["buttons"]) - 1) { // If the open form button was clicked
                FormHistory::getInstance()->addForm($player, $this);
                $form->sendToPlayer($player);
                return;
            }

            if ($originalCallback !== null) {
                $originalCallback($player, $data);
            }
        };

        return $this->addButton($text);
    }

    /**
     * Set the text for the first button (ModalForm only)
     * 
     * @param string $text The button text
     * @return $this For method chaining
     * @throws \InvalidArgumentException If the form is not a ModalForm
     */
    public function setButton1(string $text): self {
        if (!isset($this->data["button1"])) {
            throw new \InvalidArgumentException("Cannot set button1 on non-ModalForm");
        }

        $this->data["button1"] = $text;
        return $this;
    }

    /**
     * Set the text for the second button (ModalForm only)
     * 
     * @param string $text The button text
     * @return $this For method chaining
     * @throws \InvalidArgumentException If the form is not a ModalForm
     */
    public function setButton2(string $text): self {
        if (!isset($this->data["button2"])) {
            throw new \InvalidArgumentException("Cannot set button2 on non-ModalForm");
        }

        $this->data["button2"] = $text;
        return $this;
    }

    /**
     * Serialize the form data to JSON
     * This method is called by PocketMine-MP when sending the form to a player
     * 
     * @return array The form data
     */
    public function jsonSerialize(): array {
        return $this->data;
    }
}
