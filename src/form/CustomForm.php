<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\form;

use pocketmine\form\Form as IForm;
use pocketmine\player\Player;

/**
 * Type-safe Custom Form implementation
 * Addresses UX issue 2.a - Confusing Form Type Handling
 */
class CustomForm implements IForm {
    private array $data = [];
    private array $elementMap = [];
    private ?\Closure $callback = null;

    /**
     * CustomForm constructor
     * 
     * @param string $title The form title
     * @param \Closure|null $callback Callback function (Player $player, ?array $response): void
     */
    public function __construct(string $title = "", ?\Closure $callback = null) {
        $this->data = [
            "type" => "custom_form",
            "title" => $title,
            "content" => []
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
     * Add a label element
     * 
     * @param string $text
     * @return $this
     */
    public function label(string $text): self {
        $this->data["content"][] = [
            "type" => "label",
            "text" => $text
        ];
        return $this;
    }

    /**
     * Add an input element
     * 
     * @param string $text The label text
     * @param string $placeholder The placeholder text
     * @param string $default The default value
     * @param string|null $key Optional key for response mapping
     * @return $this
     */
    public function input(string $text, string $placeholder = "", string $default = "", ?string $key = null): self {
        $index = count($this->data["content"]);
        $this->data["content"][] = [
            "type" => "input",
            "text" => $text,
            "placeholder" => $placeholder,
            "default" => $default
        ];

        if ($key !== null) {
            $this->elementMap[$key] = $index;
        }

        return $this;
    }

    /**
     * Add a toggle element
     * 
     * @param string $text The label text
     * @param bool $default The default value
     * @param string|null $key Optional key for response mapping
     * @return $this
     */
    public function toggle(string $text, bool $default = false, ?string $key = null): self {
        $index = count($this->data["content"]);
        $this->data["content"][] = [
            "type" => "toggle",
            "text" => $text,
            "default" => $default
        ];

        if ($key !== null) {
            $this->elementMap[$key] = $index;
        }

        return $this;
    }

    /**
     * Add a slider element
     * 
     * @param string $text The label text
     * @param float $min The minimum value
     * @param float $max The maximum value
     * @param float $step The step value
     * @param float $default The default value
     * @param string|null $key Optional key for response mapping
     * @return $this
     */
    public function slider(string $text, float $min, float $max, float $step = 1.0, float $default = 0.0, ?string $key = null): self {
        $index = count($this->data["content"]);
        $this->data["content"][] = [
            "type" => "slider",
            "text" => $text,
            "min" => $min,
            "max" => $max,
            "step" => $step,
            "default" => $default
        ];

        if ($key !== null) {
            $this->elementMap[$key] = $index;
        }

        return $this;
    }

    /**
     * Add a step slider element
     * 
     * @param string $text The label text
     * @param array $steps The available steps
     * @param int $default The default step index
     * @param string|null $key Optional key for response mapping
     * @return $this
     */
    public function stepSlider(string $text, array $steps, int $default = 0, ?string $key = null): self {
        $index = count($this->data["content"]);
        $this->data["content"][] = [
            "type" => "step_slider",
            "text" => $text,
            "steps" => $steps,
            "default" => $default
        ];

        if ($key !== null) {
            $this->elementMap[$key] = $index;
        }

        return $this;
    }

    /**
     * Add a dropdown element
     * 
     * @param string $text The label text
     * @param array $options The available options
     * @param int $default The default option index
     * @param string|null $key Optional key for response mapping
     * @return $this
     */
    public function dropdown(string $text, array $options, int $default = 0, ?string $key = null): self {
        $index = count($this->data["content"]);
        $this->data["content"][] = [
            "type" => "dropdown",
            "text" => $text,
            "options" => $options,
            "default" => $default
        ];

        if ($key !== null) {
            $this->elementMap[$key] = $index;
        }

        return $this;
    }

    /**
     * Set the callback function
     * 
     * @param \Closure $callback Function (Player $player, ?array $response): void
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
        if ($this->callback === null) {
            return;
        }

        if ($data === null) {
            // Form was closed without submission
            ($this->callback)($player, null);
            return;
        }

        // Map response data using element keys
        $mappedResponse = [];
        foreach ($this->elementMap as $key => $index) {
            if (isset($data[$index])) {
                $mappedResponse[$key] = $data[$index];
            }
        }

        // If no keys were used, return raw data
        if (empty($mappedResponse)) {
            ($this->callback)($player, $data);
        } else {
            ($this->callback)($player, $mappedResponse);
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
     * @return CustomForm
     */
    public static function create(string $title = ""): self {
        return new self($title);
    }
}
