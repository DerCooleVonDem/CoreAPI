<?php

declare(strict_types=1);

namespace JonasWindmann\CoreAPI\session;

use JonasWindmann\CoreAPI\manager\BaseManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

/**
 * Manages player sessions
 * Creates sessions when players join and removes them when they leave
 */
class PlayerSessionManager extends BaseManager implements Listener {
    /** @var ComponentFactory[] */
    private array $componentFactories = [];
    
    /**
     * PlayerSessionManager constructor
     * 
     * @param Plugin $plugin The plugin instance
     */
    public function __construct(Plugin $plugin) {
        parent::__construct($plugin);
        
        // Register event listeners
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }
    
    /**
     * Register a component factory that will be used to create components for all sessions
     *
     * @param ComponentFactory $factory The component factory to register
     */
    public function registerComponentFactory(ComponentFactory $factory): void {
        $this->componentFactories[$factory->getId()] = $factory;

        // Add the component to all existing sessions
        foreach ($this->items as $session) {
            if ($session instanceof PlayerSession) {
                $component = $factory->create();
                $session->addComponent($component);

                // Register the component as an event listener
                $this->plugin->getServer()->getPluginManager()->registerEvents($component, $this->plugin);
            }
        }
    }

    /**
     * Register a component type that will be added to all new sessions
     * This method creates a factory automatically for backward compatibility
     *
     * @param PlayerSessionComponent $component The component to register
     * @deprecated Use registerComponentFactory() instead
     */
    public function registerComponent(PlayerSessionComponent $component): void {
        $factory = SimpleComponentFactory::createFactory($component->getId(), function() use ($component) {
            return clone $component;
        });
        $this->registerComponentFactory($factory);
    }
    
    /**
     * Get a player session by player instance
     * 
     * @param Player $player The player
     * @return PlayerSession|null The session if found, null otherwise
     */
    public function getSessionByPlayer(Player $player): ?PlayerSession {
        $session = $this->getItem($player->getUniqueId()->toString());
        if($session instanceof PlayerSession) {
            return $session;
        }

        return null;
    }
    
    /**
     * Create a session for a player
     * 
     * @param Player $player The player
     * @return PlayerSession The created session
     */
    public function createSession(Player $player): PlayerSession {
        $session = new PlayerSession($player);
        $this->addItem($session);
        
        // Add all registered component types to the session
        foreach ($this->componentFactories as $factory) {
            $component = $factory->create();
            $session->addComponent($component);

            // Register the component as an event listener
            $this->plugin->getServer()->getPluginManager()->registerEvents($component, $this->plugin);
        }
        
        return $session;
    }
    
    /**
     * Remove a session for a player
     * 
     * @param Player $player The player
     * @return bool True if the session was removed, false if it wasn't found
     */
    public function removeSession(Player $player): bool {
        $id = $player->getUniqueId()->toString();
        $session = $this->getItem($id);
        
        if ($session instanceof PlayerSession) {
            $session->destroy();
            return $this->removeItem($id);
        }
        
        return false;
    }
    
    /**
     * Handle player join event
     * Creates a session for the player
     * 
     * @param PlayerJoinEvent $event
     * @priority LOWEST
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $this->createSession($event->getPlayer());
    }
    
    /**
     * Handle player quit event
     * Removes the session for the player
     * 
     * @param PlayerQuitEvent $event
     * @priority MONITOR
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $this->removeSession($event->getPlayer());
    }
    
    /**
     * Load items from storage
     * This is a no-op for player sessions as they are created on demand
     */
    public function loadItems(): void {
        // No-op - sessions are created when players join
    }
    
    /**
     * Save items to storage
     * This is a no-op for player sessions as they are not persisted
     */
    public function saveItems(): void {
        // No-op - sessions are not persisted
    }
}