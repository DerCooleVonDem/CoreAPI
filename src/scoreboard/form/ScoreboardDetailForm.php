<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\form;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\form\SimpleForm;
use JonasWindmann\CoreAPI\scoreboard\Scoreboard;
use pocketmine\player\Player;

/**
 * Form to show detailed information about a specific scoreboard
 */
class ScoreboardDetailForm extends SimpleForm {

    /** @var Scoreboard */
    private Scoreboard $scoreboard;

    public function __construct(Scoreboard $scoreboard) {
        $this->scoreboard = $scoreboard;
        
        $content = $this->buildDetailedContent();
        parent::__construct("§l§eScoreboard Details", $content);

        // Add action buttons
        $this->button("§l§aDisplay This Scoreboard", "display", function(Player $player) {
            $this->displayScoreboard($player);
        });
        
        $this->button("§l§bView Lines & Tags", "view_content", function(Player $player) {
            $form = new ScoreboardContentForm($this->scoreboard);
            $form->sendTo($player);
        });
        
        $this->button("§l§7« Back to List", "back", function(Player $player) {
            $form = new ScoreboardListForm();
            $form->sendTo($player);
        });
    }

    private function buildDetailedContent(): string {
        $content = "§7Detailed information about this scoreboard:\n\n";
        
        $content .= "§e§lBasic Information:\n";
        $content .= "§7ID: §f" . $this->scoreboard->getId() . "\n";
        $content .= "§7Title: §r" . $this->scoreboard->getTitle() . "\n";
        $content .= "§7Owner: §a" . $this->scoreboard->getOwnerPlugin() . "\n";
        $content .= "§7Priority: §6" . $this->scoreboard->getPriority() . "\n\n";
        
        $content .= "§e§lSettings:\n";
        $autoUpdate = $this->scoreboard->isAutoUpdate() ? "§aEnabled" : "§cDisabled";
        $autoDisplay = $this->scoreboard->isAutoDisplay() ? "§aEnabled" : "§cDisabled";
        
        $content .= "§7Auto-update: " . $autoUpdate . "\n";
        
        if ($this->scoreboard->isAutoUpdate()) {
            $interval = $this->scoreboard->getUpdateInterval();
            $seconds = round($interval / 20, 1);
            $content .= "§7Update interval: §e" . $seconds . " seconds\n";
        }
        
        $content .= "§7Auto-display: " . $autoDisplay . "\n\n";
        
        // Show line and tag counts
        $lines = $this->scoreboard->getLines();
        $tags = $this->scoreboard->getTags();
        
        $content .= "§e§lContent:\n";
        $content .= "§7Lines: §b" . count($lines) . "\n";
        $content .= "§7Tags: §d" . count($tags) . "\n\n";
        
        if (!empty($tags)) {
            $tagNames = array_map(function($tag) {
                return "{" . $tag->getName() . "}";
            }, $tags);
            $content .= "§7Available tags: §d" . implode("§7, §d", $tagNames) . "\n\n";
        }
        
        $content .= "§7Use the buttons below to interact with this scoreboard.";
        
        return $content;
    }

    private function displayScoreboard(Player $player): void {
        $scoreboardManager = CoreAPI::getInstance()->getScoreboardManager();
        
        $success = $scoreboardManager->displayScoreboardDirect($player, $this->scoreboard);
        
        if ($success) {
            $player->sendMessage("§l§6CoreAPI §r§7» §aNow displaying: " . $this->scoreboard->getTitle());
            $player->sendMessage("§7Owner: §a" . $this->scoreboard->getOwnerPlugin());
            
            if ($this->scoreboard->isAutoUpdate()) {
                $interval = $this->scoreboard->getUpdateInterval();
                $seconds = round($interval / 20, 1);
                $player->sendMessage("§7Updates every §e" . $seconds . " seconds");
            }
        } else {
            $player->sendMessage("§l§6CoreAPI §r§7» §cFailed to display scoreboard. Please try again.");
        }
    }
}
