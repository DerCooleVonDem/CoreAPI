<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\npc;

use pocketmine\player\Player;

/**
 * A simple NPC that greets players when clicked
 */
class GreeterNpc extends CoreNpc {
    /**
     * Initialize NPC-specific properties
     */
    protected function initNpc(): void {
        $this->setNpcName("§aGreeter");
        $this->lookDistance = 10.0; // Look at players up to 10 blocks away
    }
    
    /**
     * Called when a player clicks on the NPC
     */
    public function onClick(Player $player): void {
        // Get custom message if set, otherwise use default
        $message = $this->getData("message", "§aHello, " . $player->getName() . "!");
        
        // Send the message to the player
        $player->sendMessage($message);
    }
}