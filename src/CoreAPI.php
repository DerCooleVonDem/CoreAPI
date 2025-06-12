<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI;

use JonasWindmann\CoreAPI\command\CommandManager;
use JonasWindmann\CoreAPI\form\FormManager;
use JonasWindmann\CoreAPI\item\CustomItemManager;
use JonasWindmann\CoreAPI\item\command\CustomItemCommand;
use JonasWindmann\CoreAPI\scoreboard\ScoreboardManager;
use JonasWindmann\CoreAPI\scoreboard\command\TestScoreboardCommand;
use JonasWindmann\CoreAPI\scoreboard\command\CoreScoreboardCommand;
use JonasWindmann\CoreAPI\session\PlayerSessionManager;
use JonasWindmann\CoreAPI\session\SimpleComponentFactory;
use JonasWindmann\CoreAPI\scoreboard\session\ScoreboardComponent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class CoreAPI extends PluginBase
{

    use SingletonTrait;

    /** @var CommandManager */
    private CommandManager $commandManager;

    /** @var PlayerSessionManager */
    private PlayerSessionManager $sessionManager;

    /** @var FormManager */
    private FormManager $formManager;

    /** @var ScoreboardManager */
    private ScoreboardManager $scoreboardManager;

    /** @var CustomItemManager */
    private CustomItemManager $customItemManager;

    protected function onEnable(): void
    {
        self::setInstance($this);
        $this->commandManager = new CommandManager($this);
        $this->sessionManager = new PlayerSessionManager($this);
        $this->formManager = new FormManager($this);
        $this->scoreboardManager = new ScoreboardManager($this);
        $this->customItemManager = new CustomItemManager($this);

        // Register the scoreboard component factory
        $this->sessionManager->registerComponentFactory(
            SimpleComponentFactory::createFactory("scoreboard", function() {
                return new ScoreboardComponent();
            })
        );

        // Register scoreboard commands
        $this->commandManager->registerCommand(new CoreScoreboardCommand());

        // Register custom item commands
        $this->commandManager->registerCommand(new CustomItemCommand());


    }

    /**
     * Get the player session manager
     *
     * @return PlayerSessionManager
     */
    public function getSessionManager(): PlayerSessionManager
    {
        return $this->sessionManager;
    }

    /**
     * Get the command manager
     *
     * @return CommandManager
     */
    public function getCommandManager(): CommandManager
    {
        return $this->commandManager;
    }

    /**
     * Get the form manager
     *
     * @return FormManager
     */
    public function getFormManager(): FormManager
    {
        return $this->formManager;
    }

    /**
     * Get the scoreboard manager
     *
     * @return ScoreboardManager
     */
    public function getScoreboardManager(): ScoreboardManager
    {
        return $this->scoreboardManager;
    }

    /**
     * Get the custom item manager
     *
     * @return CustomItemManager
     */
    public function getCustomItemManager(): CustomItemManager
    {
        return $this->customItemManager;
    }
}
