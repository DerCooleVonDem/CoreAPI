<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to show detailed information about a scoreboard or current scoreboard
 */
class InfoScoreboardSubCommand extends SubCommand {

    public function __construct() {
        parent::__construct(
            "info",
            "Show detailed information about a scoreboard",
            "/coresb info [scoreboard_id]",
            0,
            1,
            "coreapi.scoreboard.info"
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        $scoreboardManager = CoreAPI::getInstance()->getScoreboardManager();
        $scoreboard = null;
        
        if (count($args) > 0) {
            // Show info for specific scoreboard
            $scoreboardId = $args[0];
            $scoreboard = $scoreboardManager->getScoreboard($scoreboardId);
            
            if ($scoreboard === null) {
                $sender->sendMessage(TextFormat::RED . "Scoreboard '" . $scoreboardId . "' not found.");
                return;
            }
        } else {
            // Show info for current scoreboard (players only)
            if (!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "Console must specify a scoreboard ID.");
                $sender->sendMessage(TextFormat::GRAY . "Usage: /coresb info <scoreboard_id>");
                return;
            }
            
            $sessionManager = CoreAPI::getInstance()->getSessionManager();
            $session = $sessionManager->getSessionByPlayer($sender);
            
            if ($session === null) {
                $sender->sendMessage(TextFormat::RED . "No session found.");
                return;
            }
            
            $component = $session->getComponent("scoreboard");
            if ($component === null || !$component->hasActiveScoreboard()) {
                $sender->sendMessage(TextFormat::YELLOW . "You don't have any scoreboard displayed.");
                $sender->sendMessage(TextFormat::GRAY . "Use " . TextFormat::YELLOW . "/coresb list" . TextFormat::GRAY . " to see available scoreboards.");
                return;
            }
            
            $scoreboard = $component->getActiveScoreboard();
        }
        
        // Display detailed information
        $sender->sendMessage(TextFormat::GREEN . "§l§6Scoreboard Information");
        $sender->sendMessage("");
        
        $sender->sendMessage(TextFormat::YELLOW . "§lBasic Info:");
        $sender->sendMessage(TextFormat::GRAY . "  ID: " . TextFormat::WHITE . $scoreboard->getId());
        $sender->sendMessage(TextFormat::GRAY . "  Title: " . TextFormat::RESET . $scoreboard->getTitle());
        $sender->sendMessage(TextFormat::GRAY . "  Owner: " . TextFormat::AQUA . $scoreboard->getOwnerPlugin());
        $sender->sendMessage(TextFormat::GRAY . "  Priority: " . TextFormat::GOLD . $scoreboard->getPriority());
        $sender->sendMessage("");
        
        $sender->sendMessage(TextFormat::YELLOW . "§lSettings:");
        $autoUpdate = $scoreboard->isAutoUpdate() ? "§aEnabled" : "§cDisabled";
        $autoDisplay = $scoreboard->isAutoDisplay() ? "§aEnabled" : "§cDisabled";
        $sender->sendMessage(TextFormat::GRAY . "  Auto-update: " . $autoUpdate);
        
        if ($scoreboard->isAutoUpdate()) {
            $interval = $scoreboard->getUpdateInterval();
            $seconds = round($interval / 20, 1);
            $sender->sendMessage(TextFormat::GRAY . "  Update interval: " . TextFormat::YELLOW . $seconds . " seconds" . TextFormat::GRAY . " (" . $interval . " ticks)");
        }
        
        $sender->sendMessage(TextFormat::GRAY . "  Auto-display: " . $autoDisplay);
        $sender->sendMessage("");
        
        // Show lines
        $lines = $scoreboard->getLines();
        $sender->sendMessage(TextFormat::YELLOW . "§lLines (" . count($lines) . "):");
        
        if (empty($lines)) {
            $sender->sendMessage(TextFormat::GRAY . "  No lines configured");
        } else {
            foreach ($lines as $i => $line) {
                $visible = $line->isVisible() ? "§a✓" : "§c✗";
                $sender->sendMessage(TextFormat::GRAY . "  " . ($i + 1) . ". " . $visible . " " . TextFormat::WHITE . "'" . $line->getTemplate() . "'" . TextFormat::GRAY . " (score: " . $line->getScore() . ")");
            }
        }
        $sender->sendMessage("");
        
        // Show tags
        $tags = $scoreboard->getTags();
        $sender->sendMessage(TextFormat::YELLOW . "§lTags (" . count($tags) . "):");
        
        if (empty($tags)) {
            $sender->sendMessage(TextFormat::GRAY . "  No tags configured");
        } else {
            foreach ($tags as $tag) {
                $sender->sendMessage(TextFormat::GRAY . "  " . TextFormat::DARK_AQUA . "{" . $tag->getName() . "}" . TextFormat::GRAY . " - " . $tag->getDescription());
            }
        }
    }
}
