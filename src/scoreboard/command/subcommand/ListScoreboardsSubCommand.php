<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to list all available scoreboards
 */
class ListScoreboardsSubCommand extends SubCommand {

    public function __construct() {
        parent::__construct(
            "list",
            "List all available scoreboards",
            "/coresb list",
            0,
            0,
            "coreapi.scoreboard.list"
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        $scoreboardManager = CoreAPI::getInstance()->getScoreboardManager();
        $scoreboards = $scoreboardManager->getScoreboards();

        if (empty($scoreboards)) {
            $sender->sendMessage(TextFormat::RED . "No scoreboards are currently registered.");
            return;
        }

        $sender->sendMessage(TextFormat::GREEN . "§l§6CoreAPI Scoreboards");
        $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/coresb show <id>" . TextFormat::GRAY . " to display a scoreboard");
        $sender->sendMessage("");

        // Sort by priority (highest first)
        usort($scoreboards, function($a, $b) {
            return $b->getPriority() <=> $a->getPriority();
        });

        foreach ($scoreboards as $scoreboard) {
            $priority = $scoreboard->getPriority();
            $autoUpdate = $scoreboard->isAutoUpdate() ? "§aYes" : "§cNo";
            $autoDisplay = $scoreboard->isAutoDisplay() ? "§aYes" : "§cNo";
            $owner = $scoreboard->getOwnerPlugin();
            
            $sender->sendMessage(TextFormat::YELLOW . "§l• " . TextFormat::WHITE . $scoreboard->getId());
            $sender->sendMessage(TextFormat::GRAY . "  Title: " . TextFormat::RESET . $scoreboard->getTitle());
            $sender->sendMessage(TextFormat::GRAY . "  Owner: " . TextFormat::AQUA . $owner . TextFormat::GRAY . " | Priority: " . TextFormat::GOLD . $priority);
            $sender->sendMessage(TextFormat::GRAY . "  Auto-update: " . $autoUpdate . TextFormat::GRAY . " | Auto-display: " . $autoDisplay);
            
            // Show tags
            $tags = $scoreboard->getTags();
            if (!empty($tags)) {
                $tagNames = array_map(function($tag) {
                    return "{" . $tag->getName() . "}";
                }, $tags);
                $sender->sendMessage(TextFormat::GRAY . "  Tags: " . TextFormat::DARK_AQUA . implode(", ", $tagNames));
            }
            
            $sender->sendMessage("");
        }

        $sender->sendMessage(TextFormat::GREEN . "Total: " . count($scoreboards) . " scoreboards");
        $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/coresb manage" . TextFormat::GRAY . " for form-based management");
    }
}
