<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\npc\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to get information about NPCs near the player
 */
class InfoNearSubCommand extends SubCommand {

    /** @var float The maximum distance to search for NPCs */
    private const MAX_DISTANCE = 10.0;

    public function __construct() {
        parent::__construct(
            "infonear",
            "Get information about NPCs near you",
            "/corenpc infonear [radius]",
            0,
            1,
            "coreapi.npc.info"
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return;
        }

        $npcManager = CoreAPI::getInstance()->getNpcManager();
        $npcs = $npcManager->getActiveNpcs();

        if (empty($npcs)) {
            $sender->sendMessage(TextFormat::RED . "No NPCs are currently active.");
            return;
        }

        // Get search radius from args or use default
        $radius = self::MAX_DISTANCE;
        if (isset($args[0]) && is_numeric($args[0])) {
            $radius = (float) $args[0];
            if ($radius <= 0) {
                $sender->sendMessage(TextFormat::RED . "Radius must be greater than 0.");
                return;
            }
        }

        $playerPos = $sender->getPosition();
        $playerWorld = $sender->getWorld();
        $nearbyNpcs = [];

        // Find NPCs within radius
        foreach ($npcs as $id => $npc) {
            if ($npc->getWorld() !== $playerWorld) {
                continue; // Skip NPCs in different worlds
            }

            $distance = $npc->getPosition()->distance($playerPos);
            if ($distance <= $radius) {
                $nearbyNpcs[$id] = [
                    'npc' => $npc,
                    'distance' => $distance
                ];
            }
        }

        if (empty($nearbyNpcs)) {
            $sender->sendMessage(TextFormat::YELLOW . "No NPCs found within " . $radius . " blocks of your position.");
            return;
        }

        // Sort by distance (closest first)
        uasort($nearbyNpcs, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        $sender->sendMessage(TextFormat::GREEN . "§l§6NPCs Near You (within " . $radius . " blocks)");
        $sender->sendMessage("");

        foreach ($nearbyNpcs as $id => $data) {
            $npc = $data['npc'];
            $distance = $data['distance'];
            
            $type = $npc->getNpcType();
            $name = $npc->getNpcName();
            $position = $npc->getPosition();
            
            $sender->sendMessage(TextFormat::YELLOW . "§l• " . TextFormat::WHITE . "ID: " . $id . TextFormat::GRAY . " (" . round($distance, 1) . " blocks away)");
            $sender->sendMessage(TextFormat::GRAY . "  Type: " . TextFormat::AQUA . $type);
            $sender->sendMessage(TextFormat::GRAY . "  Name: " . TextFormat::RESET . $name);
            $sender->sendMessage(TextFormat::GRAY . "  Location: " . TextFormat::GOLD . "X: " . round($position->x, 1) . ", Y: " . round($position->y, 1) . ", Z: " . round($position->z, 1));
            $sender->sendMessage("");
        }

        $sender->sendMessage(TextFormat::GREEN . "Total: " . count($nearbyNpcs) . " NPCs nearby");
        $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/corenpc info <id>" . TextFormat::GRAY . " to get more details about a specific NPC");
    }
}