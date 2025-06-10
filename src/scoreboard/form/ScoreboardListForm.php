<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\form;

use JonasWindmann\CoreAPI\CoreAPI;
use JonasWindmann\CoreAPI\form\SimpleForm;
use pocketmine\player\Player;

/**
 * Form to display all available scoreboards with detailed information
 */
class ScoreboardListForm extends SimpleForm {

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
        parent::__construct("§l§6Available Scoreboards", $content);

        // Add buttons for each scoreboard
        foreach ($this->scoreboards as $index => $scoreboard) {
            $autoDisplay = $scoreboard->isAutoDisplay() ? "§a●" : "§7○";
            $buttonText = $autoDisplay . " §f" . $scoreboard->getTitle() . "\n§7" . $scoreboard->getId();
            
            $this->button($buttonText, "scoreboard_" . $index, function(Player $player) use ($scoreboard) {
                $form = new ScoreboardDetailForm($scoreboard);
                $form->sendTo($player);
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
        
        $content = "§7Select a scoreboard to view details or display it:\n\n";
        
        foreach ($this->scoreboards as $i => $scoreboard) {
            $autoDisplay = $scoreboard->isAutoDisplay() ? "§a[Auto]" : "§7[Manual]";
            $priority = $scoreboard->getPriority();
            
            $content .= "§e" . ($i + 1) . ". " . $autoDisplay . " §f" . $scoreboard->getTitle() . "\n";
            $content .= "§7   ID: §b" . $scoreboard->getId() . " §7| Priority: §6" . $priority . "\n";
            $content .= "§7   Owner: §a" . $scoreboard->getOwnerPlugin() . "\n\n";
        }
        
        $content .= "§7Total: §a" . count($this->scoreboards) . " scoreboards";
        
        return $content;
    }
}
