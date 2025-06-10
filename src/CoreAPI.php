<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI;

use JonasWindmann\CoreAPI\command\CommandManager;
use JonasWindmann\CoreAPI\form\FormManager;
use JonasWindmann\CoreAPI\session\PlayerSessionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class CoreAPI extends PluginBase{

    use SingletonTrait;

    /** @var CommandManager */
    private CommandManager $commandManager;

    /** @var PlayerSessionManager */
    private PlayerSessionManager $sessionManager;

    /** @var FormManager */
    private FormManager $formManager;

    protected function onEnable(): void {
        self::setInstance($this);
        $this->commandManager = new CommandManager($this);
        $this->sessionManager = new PlayerSessionManager($this);
        $this->formManager = new FormManager($this);
    }

    /**
     * Get the player session manager
     * 
     * @return PlayerSessionManager
     */
    public function getSessionManager(): PlayerSessionManager {
        return $this->sessionManager;
    }

    /**
     * Get the command manager
     * 
     * @return CommandManager
     */
    public function getCommandManager(): CommandManager {
        return $this->commandManager;
    }

    /**
     * Get the form manager
     * 
     * @return FormManager
     */
    public function getFormManager(): FormManager {
        return $this->formManager;
    }
}
