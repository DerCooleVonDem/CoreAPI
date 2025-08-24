<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\npc\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to list all active NPCs
 */
class ListNpcsSubCommand extends SubCommand {

    public function __construct() {
        parent::__construct(
            "list",
            "List all active NPCs",
            "/corenpc list",
            0,
            0,
            "coreapi.npc.list"
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        $npcManager = CoreAPI::getInstance()->getNpcManager();
        $npcs = $npcManager->getActiveNpcs();

        if (empty($npcs)) {
            $sender->sendMessage(TextFormat::RED . "No NPCs are currently active.");
            return;
        }

        $sender->sendMessage(TextFormat::GREEN . "§l§6CoreAPI NPCs");
        $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/corenpc info <id>" . TextFormat::GRAY . " to get more information about an NPC");
        $sender->sendMessage("");

        foreach ($npcs as $id => $npc) {
            $type = $npc->getNpcType();
            $name = $npc->getNpcName();
            $position = $npc->getPosition();
            $world = $npc->getWorld()->getFolderName();
            
            $sender->sendMessage(TextFormat::YELLOW . "§l• " . TextFormat::WHITE . "ID: " . $id);
            $sender->sendMessage(TextFormat::GRAY . "  Type: " . TextFormat::AQUA . $type);
            $sender->sendMessage(TextFormat::GRAY . "  Name: " . TextFormat::RESET . $name);
            $sender->sendMessage(TextFormat::GRAY . "  Location: " . TextFormat::GOLD . "X: " . round($position->x, 1) . ", Y: " . round($position->y, 1) . ", Z: " . round($position->z, 1) . " in " . $world);
            
            $sender->sendMessage("");
        }

        $sender->sendMessage(TextFormat::GREEN . "Total: " . count($npcs) . " NPCs");
    }
}