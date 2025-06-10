<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\form;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\form\SimpleForm;
use pocketmine\player\Player;

/**
 * Form to show the player's current scoreboard status
 */
class ScoreboardStatusForm extends SimpleForm {

    public function __construct() {
        parent::__construct("§l§eYour Scoreboard Status", "");
        
        // We'll set the content in the sendTo method since we need the player context
    }

    public function sendTo(Player $player): void {
        $content = $this->buildStatusContent($player);
        $this->content($content);
        
        $sessionManager = CoreAPI::getInstance()->getSessionManager();
        $session = $sessionManager->getSessionByPlayer($player);
        
        if ($session !== null) {
            $component = $session->getComponent("scoreboard");
            
            if ($component !== null && $component->hasActiveScoreboard()) {
                $activeScoreboard = $component->getActiveScoreboard();
                
                $this->button("§l§bView Details", "details", function(Player $player) use ($activeScoreboard) {
                    $form = new ScoreboardDetailForm($activeScoreboard);
                    $form->sendTo($player);
                });
                
                $this->button("§l§cHide Scoreboard", "hide", function(Player $player) {
                    $this->hideCurrentScoreboard($player);
                });
                
                $this->button("§l§aChange Scoreboard", "change", function(Player $player) {
                    $form = new ScoreboardSelectionForm();
                    $form->sendTo($player);
                });
            } else {
                $this->button("§l§aShow Scoreboard", "show", function(Player $player) {
                    $form = new ScoreboardSelectionForm();
                    $form->sendTo($player);
                });
            }
        }
        
        $this->button("§l§7« Back to Menu", "back", function(Player $player) {
            $form = new ScoreboardManagementForm();
            $form->sendTo($player);
        });
        
        parent::sendTo($player);
    }

    private function buildStatusContent(Player $player): string {
        $sessionManager = CoreAPI::getInstance()->getSessionManager();
        $session = $sessionManager->getSessionByPlayer($player);
        
        if ($session === null) {
            return "§cNo session found. Please rejoin the server.";
        }
        
        $component = $session->getComponent("scoreboard");
        if ($component === null) {
            return "§cScoreboard component not found. Please contact an administrator.";
        }
        
        if (!$component->hasActiveScoreboard()) {
            return "§7You currently don't have any scoreboard displayed.\n\n" .
                   "§e§lAvailable Actions:\n" .
                   "§7• Show a scoreboard from the available options\n" .
                   "§7• View all available scoreboards\n\n" .
                   "§7Use the buttons below to get started.";
        }
        
        $activeScoreboard = $component->getActiveScoreboard();
        
        $content = "§7Your current scoreboard status:\n\n";
        
        $content .= "§e§lCurrently Displaying:\n";
        $content .= "§7Title: §r" . $activeScoreboard->getTitle() . "\n";
        $content .= "§7ID: §b" . $activeScoreboard->getId() . "\n";
        $content .= "§7Owner: §a" . $activeScoreboard->getOwnerPlugin() . "\n";
        $content .= "§7Priority: §6" . $activeScoreboard->getPriority() . "\n\n";
        
        $content .= "§e§lSettings:\n";
        $autoUpdate = $activeScoreboard->isAutoUpdate() ? "§aEnabled" : "§cDisabled";
        $content .= "§7Auto-update: " . $autoUpdate . "\n";
        
        if ($activeScoreboard->isAutoUpdate()) {
            $interval = $activeScoreboard->getUpdateInterval();
            $seconds = round($interval / 20, 1);
            $content .= "§7Update interval: §e" . $seconds . " seconds\n";
        }
        
        $content .= "\n§e§lContent:\n";
        $lines = $activeScoreboard->getLines();
        $tags = $activeScoreboard->getTags();
        $content .= "§7Lines: §b" . count($lines) . "\n";
        $content .= "§7Tags: §d" . count($tags) . "\n\n";
        
        $content .= "§7Use the buttons below to manage your scoreboard.";
        
        return $content;
    }

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
            
            // Refresh the form to show updated status
            $form = new ScoreboardStatusForm();
            $form->sendTo($player);
        } else {
            $player->sendMessage("§l§6CoreAPI §r§7» §cFailed to hide scoreboard.");
        }
    }
}
