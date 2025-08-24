<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\npc\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to get information about an NPC
 */
class InfoNpcSubCommand extends SubCommand {

    public function __construct() {
        parent::__construct(
            "info",
            "Get information about an NPC",
            "/corenpc info <id>",
            1,
            1,
            "coreapi.npc.info"
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

        // Get NPC info
        $type = $npc->getNpcType();
        $name = $npc->getNpcName();
        $position = $npc->getPosition();
        $world = $npc->getWorld()->getFolderName();
        $data = $npc->getAllData();

        $sender->sendMessage(TextFormat::GREEN . "§l§6NPC Information");
        $sender->sendMessage(TextFormat::YELLOW . "ID: " . TextFormat::WHITE . $id);
        $sender->sendMessage(TextFormat::YELLOW . "Type: " . TextFormat::AQUA . $type);
        $sender->sendMessage(TextFormat::YELLOW . "Name: " . TextFormat::RESET . $name);
        $sender->sendMessage(TextFormat::YELLOW . "Location: " . TextFormat::GOLD . "X: " . round($position->x, 1) . ", Y: " . round($position->y, 1) . ", Z: " . round($position->z, 1) . " in " . $world);
        
        // Display custom data
        if (!empty($data)) {
            $sender->sendMessage(TextFormat::YELLOW . "Custom Data:");
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $sender->sendMessage(TextFormat::GRAY . "  " . $key . ": " . TextFormat::WHITE . $value);
            }
        }
        
        $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/corenpc remove " . $id . TextFormat::GRAY . " to remove this NPC");
    }
}