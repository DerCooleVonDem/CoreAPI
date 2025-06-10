<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\form;

use pocketmine\form\Form as IForm;
use pocketmine\player\Player;

/**
 * Type-safe Simple Form implementation
 * Addresses UX issues 2.a and 2.b - Confusing Form Type Handling and Image Type Parameters
 */
class SimpleForm implements IForm {
    private array $data = [];
    private array $callbacks = [];
    private ?\Closure $defaultCallback = null;

    /**
     * SimpleForm constructor
     * 
     * @param string $title The form title
     * @param string $content The form content/message
     * @param \Closure|null $callback Default callback function (Player $player, ?string $buttonId): void
     */
    public function __construct(
        string $title = "",
        string $content = "",
        ?\Closure $callback = null
    ) {
        $this->data = [
            "type" => "form",
            "title" => $title,
            "content" => $content,
            "buttons" => []
        ];
        $this->defaultCallback = $callback;
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
     * Add a button to the form
     * 
     * @param string $text The button text
     * @param string|null $id Optional button ID for identification
     * @param \Closure|null $callback Optional button-specific callback
     * @param ImageType|null $imageType Optional image type
     * @param string|null $imagePath Optional image path/URL
     * @return $this
     */
    public function button(
        string $text,
        ?string $id = null,
        ?\Closure $callback = null,
        ?ImageType $imageType = null,
        ?string $imagePath = null
    ): self {
        $button = ["text" => $text];

        if ($imageType !== null && $imagePath !== null) {
            $button["image"] = [
                "type" => $imageType->value,
                "data" => $imagePath
            ];
        }

        $this->data["buttons"][] = $button;
        $buttonIndex = count($this->data["buttons"]) - 1;

        // Store callback for this button
        if ($callback !== null) {
            $this->callbacks[$buttonIndex] = $callback;
        }

        return $this;
    }

    /**
     * Add a button with a path-based image
     * 
     * @param string $text
     * @param string $imagePath
     * @param string|null $id
     * @param \Closure|null $callback
     * @return $this
     */
    public function buttonWithImage(string $text, string $imagePath, ?string $id = null, ?\Closure $callback = null): self {
        return $this->button($text, $id, $callback, ImageType::PATH, $imagePath);
    }

    /**
     * Add a button with a URL-based image
     * 
     * @param string $text
     * @param string $imageUrl
     * @param string|null $id
     * @param \Closure|null $callback
     * @return $this
     */
    public function buttonWithUrl(string $text, string $imageUrl, ?string $id = null, ?\Closure $callback = null): self {
        return $this->button($text, $id, $callback, ImageType::URL, $imageUrl);
    }

    /**
     * Add a close button
     * 
     * @param string $text
     * @return $this
     */
    public function closeButton(string $text = "Close"): self {
        return $this->button($text, "close", function(Player $player) {
            // Close button does nothing special
        });
    }

    /**
     * Set the default callback function
     * 
     * @param \Closure $callback Function (Player $player, ?int $buttonIndex): void
     * @return $this
     */
    public function onSubmit(\Closure $callback): self {
        $this->defaultCallback = $callback;
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
        if ($data === null) {
            // Form was closed without selection
            if ($this->defaultCallback !== null) {
                ($this->defaultCallback)($player, null);
            }
            return;
        }

        $buttonIndex = (int) $data;

        // Check for button-specific callback first
        if (isset($this->callbacks[$buttonIndex])) {
            ($this->callbacks[$buttonIndex])($player);
            return;
        }

        // Fall back to default callback
        if ($this->defaultCallback !== null) {
            ($this->defaultCallback)($player, $buttonIndex);
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
     * @return SimpleForm
     */
    public static function create(string $title = "", string $content = ""): self {
        return new self($title, $content);
    }
}
