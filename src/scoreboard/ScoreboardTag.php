<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard;

/**
 * Represents a dynamic tag that can be used in scoreboard lines
 * Tags are replaced with their values when the scoreboard is rendered
 */
class ScoreboardTag {
    
    /** @var string */
    private string $name;
    
    /** @var callable */
    private $valueProvider;
    
    /** @var string */
    private string $description;
    
    /**
     * ScoreboardTag constructor
     * 
     * @param string $name The tag name (without braces)
     * @param callable $valueProvider Function that returns the tag value
     * @param string $description Description of what this tag represents
     */
    public function __construct(string $name, callable $valueProvider, string $description = "") {
        $this->name = $name;
        $this->valueProvider = $valueProvider;
        $this->description = $description;
    }
    
    /**
     * Get the tag name
     * 
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * Get the tag identifier with braces
     * 
     * @return string
     */
    public function getIdentifier(): string {
        return "{" . $this->name . "}";
    }
    
    /**
     * Get the tag description
     * 
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }
    
    /**
     * Get the current value of this tag
     * 
     * @param mixed ...$args Arguments to pass to the value provider
     * @return string
     */
    public function getValue(...$args): string {
        return (string) call_user_func($this->valueProvider, ...$args);
    }
    
    /**
     * Set a new value provider for this tag
     * 
     * @param callable $valueProvider
     */
    public function setValueProvider(callable $valueProvider): void {
        $this->valueProvider = $valueProvider;
    }
}
