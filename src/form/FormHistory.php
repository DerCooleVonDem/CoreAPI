<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\form;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

/**
 * Class for tracking form navigation history
 * Used to implement back button functionality
 */
class FormHistory {
    use SingletonTrait;

    /** @var array<string, Form[]> Map of player UUIDs to form history stacks */
    private array $history = [];

    /**
     * Add a form to a player's history
     * 
     * @param Player $player The player
     * @param Form $form The form to add
     */
    public function addForm(Player $player, Form $form): void {
        $uuid = $player->getUniqueId()->toString();
        if (!isset($this->history[$uuid])) {
            $this->history[$uuid] = [];
        }
        $this->history[$uuid][] = $form;
    }

    /**
     * Get the previous form for a player
     * 
     * @param Player $player The player
     * @return Form|null The previous form, or null if there is no previous form
     */
    public function getPreviousForm(Player $player): ?Form {
        $uuid = $player->getUniqueId()->toString();
        if (!isset($this->history[$uuid]) || count($this->history[$uuid]) < 2) {
            return null;
        }
        
        // Remove the current form from the stack
        array_pop($this->history[$uuid]);
        
        // Return the previous form (now the top of the stack)
        return end($this->history[$uuid]);
    }

    /**
     * Clear a player's form history
     * 
     * @param Player $player The player
     */
    public function clearHistory(Player $player): void {
        $uuid = $player->getUniqueId()->toString();
        unset($this->history[$uuid]);
    }

    /**
     * Check if a player has a previous form
     * 
     * @param Player $player The player
     * @return bool True if the player has a previous form, false otherwise
     */
    public function hasPreviousForm(Player $player): bool {
        $uuid = $player->getUniqueId()->toString();
        return isset($this->history[$uuid]) && count($this->history[$uuid]) > 1;
    }
}