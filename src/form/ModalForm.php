<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\form;

use pocketmine\form\Form as IForm;
use pocketmine\player\Player;

/**
 * Type-safe Modal Form implementation
 * Addresses UX issue 2.a - Confusing Form Type Handling
 */
class ModalForm implements IForm {
    private array $data = [];
    private ?\Closure $callback = null;

    /**
     * ModalForm constructor
     * 
     * @param string $title The form title
     * @param string $content The form content/message
     * @param string $button1Text The first button text (returns true when clicked)
     * @param string $button2Text The second button text (returns false when clicked)
     * @param \Closure|null $callback Callback function (Player $player, bool $response): void
     */
    public function __construct(
        string $title = "",
        string $content = "",
        string $button1Text = "Yes",
        string $button2Text = "No",
        ?\Closure $callback = null
    ) {
        $this->data = [
            "type" => "modal",
            "title" => $title,
            "content" => $content,
            "button1" => $button1Text,
            "button2" => $button2Text
        ];
        $this->callback = $callback;
    }

    /**
     * Set the form title
     * 
     * @param string $title
     * @return $this
     */
    public function title(string $title): self {
        $this->data["title"] = $title;
        return $this;
    }

    /**
     * Set the form content
     * 
     * @param string $content
     * @return $this
     */
    public function content(string $content): self {
        $this->data["content"] = $content;
        return $this;
    }

    /**
     * Set the first button text (returns true when clicked)
     * 
     * @param string $text
     * @return $this
     */
    public function button1(string $text): self {
        $this->data["button1"] = $text;
        return $this;
    }

    /**
     * Set the second button text (returns false when clicked)
     * 
     * @param string $text
     * @return $this
     */
    public function button2(string $text): self {
        $this->data["button2"] = $text;
        return $this;
    }

    /**
     * Set the confirm button text (alias for button1)
     * 
     * @param string $text
     * @return $this
     */
    public function confirmButton(string $text): self {
        return $this->button1($text);
    }

    /**
     * Set the cancel button text (alias for button2)
     * 
     * @param string $text
     * @return $this
     */
    public function cancelButton(string $text): self {
        return $this->button2($text);
    }

    /**
     * Set the callback function
     * 
     * @param \Closure $callback Function (Player $player, bool $response): void
     * @return $this
     */
    public function onSubmit(\Closure $callback): self {
        $this->callback = $callback;
        return $this;
    }

    /**
     * Send the form to a player
     * 
     * @param Player $player
     */
    public function sendTo(Player $player): void {
        $player->sendForm($this);
    }

    /**
     * Handle the form response
     * 
     * @param Player $player
     * @param mixed $data
     */
    public function handleResponse(Player $player, $data): void {
        if ($this->callback !== null) {
            ($this->callback)($player, (bool) $data);
        }
    }

    /**
     * Serialize the form data to JSON
     * 
     * @return array
     */
    public function jsonSerialize(): array {
        return $this->data;
    }

    /**
     * Static factory method
     * 
     * @param string $title
     * @param string $content
     * @return ModalForm
     */
    public static function create(string $title = "", string $content = ""): self {
        return new self($title, $content);
    }
}
