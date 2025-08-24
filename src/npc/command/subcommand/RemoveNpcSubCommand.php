<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\npc\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to remove an NPC
 */
class RemoveNpcSubCommand extends SubCommand {

    public function __construct() {
        parent::__construct(
            "remove",
            "Remove an NPC",
            "/corenpc remove <id>",
            1,
            1,
            "coreapi.npc.remove"
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        $npcManager = CoreAPI::getInstance()->getNpcManager();
        $id = (int) $args[0];

        // Check if the NPC exists
        $npc = $npcManager->getNpcById($id);
        if ($npc === null) {
            $sender->sendMessage(TextFormat::RED . "No NPC found with ID: " . $id);
            return;
        }

        // Get NPC info before removing
        $type = $npc->getNpcType();
        $name = $npc->getNpcName();

        // Remove the NPC
        if (!$npcManager->removeNpc($id)) {
            $sender->sendMessage(TextFormat::RED . "Failed to remove NPC with ID: " . $id);
            return;
        }

        $sender->sendMessage(TextFormat::GREEN . "Successfully removed " . TextFormat::YELLOW . $type . TextFormat::GREEN . " NPC (" . TextFormat::RESET . $name . TextFormat::GREEN . ") with ID " . TextFormat::YELLOW . $id);
        $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/corenpc list" . TextFormat::GRAY . " to see all NPCs");
    }
}