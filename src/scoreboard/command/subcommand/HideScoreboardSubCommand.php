<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\command\subcommand;

use JonasWindmann\CoreAPI\command\SubCommand;
use JonasWindmann\CoreAPI\CoreAPI;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

/**
 * Subcommand to hide the current scoreboard
 */
class HideScoreboardSubCommand extends SubCommand {

    public function __construct() {
        parent::__construct(
            "hide",
            "Hide your current scoreboard",
            "/coresb hide",
            0,
            0,
            "coreapi.scoreboard.hide"
        );
    }

    public function execute(CommandSender $sender, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used by players.");
            return;
        }

        $scoreboardManager = CoreAPI::getInstance()->getScoreboardManager();
        
        // Check if player has an active scoreboard
        $sessionManager = CoreAPI::getInstance()->getSessionManager();
        $session = $sessionManager->getSessionByPlayer($sender);
        
        if ($session === null) {
            $sender->sendMessage(TextFormat::RED . "No session found.");
            return;
        }
        
        $component = $session->getComponent("scoreboard");
        if ($component === null || !$component->hasActiveScoreboard()) {
            $sender->sendMessage(TextFormat::YELLOW . "You don't have any scoreboard displayed.");
            return;
        }
        
        $activeScoreboard = $component->getActiveScoreboard();
        $scoreboardTitle = $activeScoreboard->getTitle();
        
        // Hide the scoreboard
        $success = $scoreboardManager->hideScoreboard($sender);
        
        if ($success) {
            $sender->sendMessage(TextFormat::GREEN . "§l§6CoreAPI §r§7» §aScoreboard hidden.");
            $sender->sendMessage(TextFormat::GRAY . "Previously showing: " . TextFormat::RESET . $scoreboardTitle);
        } else {
            $sender->sendMessage(TextFormat::RED . "Failed to hide scoreboard.");
        }
    }
}
