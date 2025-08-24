<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\npc\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to list all registered NPC types
 */
class ListTypesSubCommand extends SubCommand {

    public function __construct() {
        parent::__construct(
            "listtypes",
            "List all registered NPC types",
            "/corenpc listtypes",
            0,
            0,
            "coreapi.npc.use"
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        $npcManager = CoreAPI::getInstance()->getNpcManager();
        $registeredTypes = $npcManager->getRegisteredNpcTypes();

        if (empty($registeredTypes)) {
            $sender->sendMessage(TextFormat::RED . "No NPC types are currently registered.");
            return;
        }

        $sender->sendMessage(TextFormat::GREEN . "§l§6Registered NPC Types");
        $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/corenpc create <type> [name]" . TextFormat::GRAY . " to create an NPC");
        $sender->sendMessage("");

        // Sort types alphabetically
        ksort($registeredTypes);

        foreach ($registeredTypes as $typeName => $className) {
            // Extract the namespace for display
            $namespaceParts = explode("\\", $className);
            $pluginNamespace = $namespaceParts[0] ?? "Unknown";
            
            $sender->sendMessage(TextFormat::YELLOW . "§l• " . TextFormat::WHITE . $typeName);
            $sender->sendMessage(TextFormat::GRAY . "  Class: " . TextFormat::AQUA . $className);
            $sender->sendMessage(TextFormat::GRAY . "  Plugin: " . TextFormat::GREEN . $pluginNamespace);
            $sender->sendMessage("");
        }

        $sender->sendMessage(TextFormat::GREEN . "Total: " . count($registeredTypes) . " NPC types available");
        $sender->sendMessage(TextFormat::GRAY . "For more information, use " . TextFormat::YELLOW . "/corenpc howtouse");
    }
}