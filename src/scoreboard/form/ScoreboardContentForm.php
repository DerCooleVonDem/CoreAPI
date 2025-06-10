<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\scoreboard\form;

use JonasWindmann\CoreAPI\form\SimpleForm;
use JonasWindmann\CoreAPI\scoreboard\Scoreboard;
use pocketmine\player\Player;

/**
 * Form to show detailed content of a scoreboard (lines and tags)
 */
class ScoreboardContentForm extends SimpleForm {

    /** @var Scoreboard */
    private Scoreboard $scoreboard;

    public function __construct(Scoreboard $scoreboard) {
        $this->scoreboard = $scoreboard;
        
        $content = $this->buildContentDetails();
        parent::__construct("§l§dScoreboard Content", $content);

        $this->button("§l§7« Back to Details", "back", function(Player $player) {
            $form = new ScoreboardDetailForm($this->scoreboard);
            $form->sendTo($player);
        });
    }

    private function buildContentDetails(): string {
        $content = "§7Detailed content of: §f" . $this->scoreboard->getTitle() . "\n\n";
        
        // Show lines
        $lines = array_reverse($this->scoreboard->getLines());
        $content .= "§e§lLines (" . count($lines) . "):\n";
        
        if (empty($lines)) {
            $content .= "§7No lines configured\n\n";
        } else {
            foreach ($lines as $i => $line) {
                $visible = $line->isVisible() ? "§a✓" : "§c✗";
                $content .= "§7" . ($i + 1) . ". " . $visible . " §f'" . $line->getTemplate() . "'\n";
                $content .= "§7   Score: §6" . $line->getScore() . "\n";
            }
            $content .= "\n";
        }
        
        // Show tags
        $tags = $this->scoreboard->getTags();
        $content .= "§e§lTags (" . count($tags) . "):\n";
        
        if (empty($tags)) {
            $content .= "§7No tags configured\n\n";
        } else {
            foreach ($tags as $tag) {
                $content .= "§d{" . $tag->getName() . "}\n";
                if (!empty($tag->getDescription())) {
                    $content .= "§7  " . $tag->getDescription() . "\n";
                }
            }
            $content .= "\n";
        }
        
        return $content;
    }
}
