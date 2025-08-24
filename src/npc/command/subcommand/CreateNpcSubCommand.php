<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\npc\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to create a new NPC
 */
class CreateNpcSubCommand extends SubCommand {

    public function __construct() {
        parent::__construct(
            "create",
            "Create a new NPC",
            "/corenpc create <type> [name]",
            1,
            2,
            "coreapi.npc.create"
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return;
        }

        $npcManager = CoreAPI::getInstance()->getNpcManager();
        $type = $args[0];
        $name = $args[1] ?? null;

        // Check if the NPC type is registered
        $registeredTypes = $npcManager->getRegisteredNpcTypes();
        if (!isset($registeredTypes[$type])) {
            $sender->sendMessage(TextFormat::RED . "Unknown NPC type: " . $type);
            $sender->sendMessage(TextFormat::GRAY . "Available types: " . TextFormat::YELLOW . implode(", ", array_keys($registeredTypes)));
            return;
        }

        // Create the NPC
        $data = [];
        if ($name !== null) {
            $data["name"] = $name;
        }

        $npc = $npcManager->createNpc($type, $sender, $data);
        if ($npc === null) {
            $sender->sendMessage(TextFormat::RED . "Failed to create NPC.");
            return;
        }

        $sender->sendMessage(TextFormat::GREEN . "Successfully created " . TextFormat::YELLOW . $type . TextFormat::GREEN . " NPC with ID " . TextFormat::YELLOW . $npc->getId());
        $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/corenpc list" . TextFormat::GRAY . " to see all NPCs");
    }
}