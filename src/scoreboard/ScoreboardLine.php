<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard;

/**
 * Represents a single line in a scoreboard
 * Can contain static text and dynamic tags
 */
class ScoreboardLine {
    
    /** @var string */
    private string $template;
    
    /** @var int */
    private int $score;
    
    /** @var bool */
    private bool $visible;
    
    /**
     * ScoreboardLine constructor
     * 
     * @param string $template The line template (can contain tags like {player})
     * @param int $score The score value for this line
     * @param bool $visible Whether this line is visible
     */
    public function __construct(string $template, int $score, bool $visible = true) {
        $this->template = $template;
        $this->score = $score;
        $this->visible = $visible;
    }
    
    /**
     * Get the line template
     * 
     * @return string
     */
    public function getTemplate(): string {
        return $this->template;
    }
    
    /**
     * Set the line template
     * 
     * @param string $template
     */
    public function setTemplate(string $template): void {
        $this->template = $template;
    }
    
    /**
     * Get the score value
     * 
     * @return int
     */
    public function getScore(): int {
        return $this->score;
    }
    
    /**
     * Set the score value
     * 
     * @param int $score
     */
    public function setScore(int $score): void {
        $this->score = $score;
    }
    
    /**
     * Check if the line is visible
     * 
     * @return bool
     */
    public function isVisible(): bool {
        return $this->visible;
    }
    
    /**
     * Set line visibility
     * 
     * @param bool $visible
     */
    public function setVisible(bool $visible): void {
        $this->visible = $visible;
    }
    
    /**
     * Render the line with tag replacements
     * 
     * @param ScoreboardTag[] $tags Available tags for replacement
     * @param mixed ...$args Arguments to pass to tag value providers
     * @return string
     */
    public function render(array $tags, ...$args): string {
        $text = $this->template;
        
        foreach ($tags as $tag) {
            $text = str_replace($tag->getIdentifier(), $tag->getValue(...$args), $text);
        }
        
        return $text;
    }
}
