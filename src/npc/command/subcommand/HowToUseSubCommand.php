<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\npc\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to show how to use the NPC system
 */
class HowToUseSubCommand extends SubCommand {

    public function __construct() {
        parent::__construct(
            "howtouse",
            "Learn how to use the NPC system",
            "/corenpc howtouse",
            0,
            0,
            "coreapi.npc.use"
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        $sender->sendMessage(TextFormat::GREEN . "§l§6CoreAPI NPC System - How to Use");
        $sender->sendMessage("");
        
        // Basic commands
        $sender->sendMessage(TextFormat::YELLOW . "§lBasic Commands:");
        $sender->sendMessage(TextFormat::AQUA . "/corenpc list" . TextFormat::GRAY . " - List all active NPCs");
        $sender->sendMessage(TextFormat::AQUA . "/corenpc info <id>" . TextFormat::GRAY . " - Get detailed information about an NPC");
        $sender->sendMessage(TextFormat::AQUA . "/corenpc infonear" . TextFormat::GRAY . " - Get information about NPCs near you");
        $sender->sendMessage(TextFormat::AQUA . "/corenpc listtypes" . TextFormat::GRAY . " - List all available NPC types");
        $sender->sendMessage("");
        
        // Creating NPCs
        $sender->sendMessage(TextFormat::YELLOW . "§lCreating NPCs:");
        $sender->sendMessage(TextFormat::AQUA . "/corenpc create <type> [name]" . TextFormat::GRAY . " - Create a new NPC at your location");
        $sender->sendMessage(TextFormat::GRAY . "Example: " . TextFormat::WHITE . "/corenpc create GreeterNpc \"Welcome NPC\"");
        $sender->sendMessage("");
        
        // Managing NPCs
        $sender->sendMessage(TextFormat::YELLOW . "§lManaging NPCs:");
        $sender->sendMessage(TextFormat::AQUA . "/corenpc remove <id>" . TextFormat::GRAY . " - Remove an NPC by its ID");
        $sender->sendMessage("");
        
        // Interacting with NPCs
        $sender->sendMessage(TextFormat::YELLOW . "§lInteracting with NPCs:");
        $sender->sendMessage(TextFormat::GRAY . "Simply click (tap) on an NPC to interact with it.");
        $sender->sendMessage(TextFormat::GRAY . "Each NPC type has its own behavior when clicked.");
        $sender->sendMessage("");
        
        // Tips
        $sender->sendMessage(TextFormat::YELLOW . "§lTips:");
        $sender->sendMessage(TextFormat::GRAY . "• NPCs are saved automatically and will persist across server restarts");
        $sender->sendMessage(TextFormat::GRAY . "• NPCs will automatically look at nearby players");
        $sender->sendMessage(TextFormat::GRAY . "• Use " . TextFormat::AQUA . "/corenpc infonear" . TextFormat::GRAY . " to find NPCs if you're not sure of their IDs");
    }
}