<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\form;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\form\SimpleForm;
use pocketmine\player\Player;

/**
 * Main scoreboard management form
 * Provides a simple interface for managing scoreboards
 */
class ScoreboardManagementForm extends SimpleForm {

    public function __construct() {
        $scoreboardManager = CoreAPI::getInstance()->getScoreboardManager();
        $scoreboards = $scoreboardManager->getScoreboards();
        
        $content = "§7Manage your scoreboard display:\n\n";
        
        if (empty($scoreboards)) {
            $content .= "§cNo scoreboards are currently available.\n";
            $content .= "§7Contact an administrator if you need scoreboards to be configured.";
        } else {
            $content .= "§e• §fView All Scoreboards §7- See all available scoreboards\n";
            $content .= "§e• §fShow Scoreboard §7- Display a specific scoreboard\n";
            $content .= "§e• §fHide Scoreboard §7- Hide your current scoreboard\n";
            $content .= "§e• §fCurrent Status §7- View your current scoreboard info\n\n";
            $content .= "§7Total available: §a" . count($scoreboards) . " scoreboards";
        }

        parent::__construct("§l§6CoreAPI Scoreboards", $content);

        if (!empty($scoreboards)) {
            $this->button("§l§aView All Scoreboards", "view_all", function(Player $player) {
                $form = new ScoreboardListForm();
                $form->sendTo($player);
            });
            
            $this->button("§l§bShow Scoreboard", "show", function(Player $player) {
                $form = new ScoreboardSelectionForm();
                $form->sendTo($player);
            });
            
            $this->button("§l§cHide Scoreboard", "hide", function(Player $player) {
                $this->hideCurrentScoreboard($player);
            });
            
            $this->button("§l§eStatus & Info", "status", function(Player $player) {
                $form = new ScoreboardStatusForm();
                $form->sendTo($player);
            });
        }
        
        $this->closeButton("§l§cClose");
    }

    /**
     * Hide the player's current scoreboard
     */
    private function hideCurrentScoreboard(Player $player): void {
        $scoreboardManager = CoreAPI::getInstance()->getScoreboardManager();
        $sessionManager = CoreAPI::getInstance()->getSessionManager();
        $session = $sessionManager->getSessionByPlayer($player);
        
        if ($session === null) {
            $player->sendMessage("§l§6CoreAPI §r§7» §cNo session found.");
            return;
        }
        
        $component = $session->getComponent("scoreboard");
        if ($component === null || !$component->hasActiveScoreboard()) {
            $player->sendMessage("§l§6CoreAPI §r§7» §eYou don't have any scoreboard displayed.");
            return;
        }
        
        $activeScoreboard = $component->getActiveScoreboard();
        $scoreboardTitle = $activeScoreboard->getTitle();
        
        $success = $scoreboardManager->hideScoreboard($player);
        
        if ($success) {
            $player->sendMessage("§l§6CoreAPI §r§7» §aScoreboard hidden successfully!");
            $player->sendMessage("§7Previously showing: " . $scoreboardTitle);
        } else {
            $player->sendMessage("§l§6CoreAPI §r§7» §cFailed to hide scoreboard.");
        }
    }
}
