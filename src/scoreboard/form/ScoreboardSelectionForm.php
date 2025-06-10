<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\form;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\form\SimpleForm;
use pocketmine\player\Player;

/**
 * Form to select and display a scoreboard
 */
class ScoreboardSelectionForm extends SimpleForm {

    /** @var array */
    private array $scoreboards = [];

    public function __construct() {
        $scoreboardManager = CoreAPI::getInstance()->getScoreboardManager();
        $this->scoreboards = $scoreboardManager->getScoreboards();
        
        // Sort by priority (highest first)
        usort($this->scoreboards, function($a, $b) {
            return $b->getPriority() <=> $a->getPriority();
        });

        $content = $this->buildContent();
        parent::__construct("§l§bSelect Scoreboard", $content);

        // Add buttons for each scoreboard
        foreach ($this->scoreboards as $index => $scoreboard) {
            $autoUpdate = $scoreboard->isAutoUpdate() ? "§a⟲" : "§7○";
            $priority = $scoreboard->getPriority();
            
            $buttonText = $autoUpdate . " §f" . $scoreboard->getTitle() . "\n§7Priority: §6" . $priority . " §7| §a" . $scoreboard->getOwnerPlugin();
            
            $this->button($buttonText, "select_" . $index, function(Player $player) use ($scoreboard) {
                $this->displayScoreboard($player, $scoreboard);
            });
        }
        
        $this->button("§l§7« Back to Menu", "back", function(Player $player) {
            $form = new ScoreboardManagementForm();
            $form->sendTo($player);
        });
    }

    private function buildContent(): string {
        if (empty($this->scoreboards)) {
            return "§7No scoreboards are currently available.\n\n§cContact an administrator if you need scoreboards to be configured.";
        }
        
        $content = "§7Choose a scoreboard to display:\n\n";
        $content .= "§e§lNote: §r§7Selecting a scoreboard will replace your current one.\n\n";
        
        return $content;
    }

    private function displayScoreboard(Player $player, $scoreboard): void {
        $scoreboardManager = CoreAPI::getInstance()->getScoreboardManager();
        
        // Display the selected scoreboard
        $success = $scoreboardManager->displayScoreboardDirect($player, $scoreboard);
        
        if ($success) {
            $player->sendMessage("§l§6CoreAPI §r§7» §aNow displaying: " . $scoreboard->getTitle());
            $player->sendMessage("§7Owner: §a" . $scoreboard->getOwnerPlugin());
            
            if ($scoreboard->isAutoUpdate()) {
                $interval = $scoreboard->getUpdateInterval();
                $seconds = round($interval / 20, 1);
                $player->sendMessage("§7Updates every §e" . $seconds . " seconds");
            }
        } else {
            $player->sendMessage("§l§6CoreAPI §r§7» §cFailed to display scoreboard. Please try again.");
        }
    }
}
