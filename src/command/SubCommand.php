<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\command;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

abstract class SubCommand {

    protected string $name;
    protected string $description;
    protected string $usage;
    protected int $minArgs;
    protected int $maxArgs;
    protected string $permission;

    public function __construct(
        string $name,
        string $description,
        string $usage = "",
        int $minArgs = 0,
        int $maxArgs = 0,
        string $permission = ""
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->usage = $usage;
        $this->minArgs = $minArgs;
        $this->maxArgs = $maxArgs;
        $this->permission = $permission;
    }

    /**
     * Execute the subcommand
     *
     * @param CommandSender $sender
     * @param array $args Raw arguments
     */
    abstract public function execute(CommandSender $sender, array $args): void;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getUsage(): string
    {
        return $this->usage;
    }

    /**
     * @return int
     */
    public function getMinArgs(): int
    {
        return $this->minArgs;
    }

    /**
     * @return int
     */
    public function getMaxArgs(): int
    {
        return $this->maxArgs;
    }

    /**
     * @return string
     */
    public function getPermission(): string
    {
        return $this->permission;
    }

    /**
     * Converts a CommandSender to a Player if possible
     * 
     * @param CommandSender $sender
     * @return Player
     * @throws \InvalidArgumentException if sender is not a player
     */
    public function senderToPlayer(CommandSender $sender): Player {
        if ($sender instanceof Player) {
            return $sender;
        } else {
            throw new \InvalidArgumentException("Sender is not a player");
        }
    }
}