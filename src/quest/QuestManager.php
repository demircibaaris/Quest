<?php

namespace quest;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;

class QuestManager {

    private array $tasks = []; 
    private array $playerTasks = []; 
    private Quest $plugin;

    public function __construct(Quest $plugin) {
        $this->plugin = $plugin;
        $this->loadTasksFromConfig();
    }

    public function loadTasksFromConfig(): void {
        $config = $this->plugin->getConfig()->get("quests", []);
        $this->tasks = $config;
    }

    public function getPlayerTask(Player $player): ?array {
        return $this->playerTasks[$player->getName()] ?? null;
    }

    public function setPlayerTask(Player $player, array $task): void {
        $this->playerTasks[$player->getName()] = $task;
    }

    public function clearPlayerTask(Player $player): void {
        unset($this->playerTasks[$player->getName()]);
    }

    public function getRandomTask(): array {
        if(empty($this->tasks)) return [];
        $key = array_rand($this->tasks);
        return $this->tasks[$key];
    }

    public function assignTask(Player $player, array $task): void {
        if(empty($task)) return;

        // Config'deki 'goal' sabit kalmalı, oyuncunun kestiği miktar düşmeli
        $task["current_goal"] = $task["goal"]; 
        
        $this->playerTasks[$player->getName()] = $task;

        $msg = $this->plugin->getConfig()->getNested("messages.quest-received");
        $msg = str_replace(
            ["{name}", "{desc}", "{reward}"],
            [$task["name"], $task["description"], $task["reward_text"]],
            $msg
        );
        $player->sendMessage($msg);
    }

    public function refreshAllPlayerTasks(): void {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $randomTask = $this->getRandomTask();
            $this->assignTask($player, $randomTask);
        }
    }

    public function isValidBlockForQuest(Block $block, string $questType): bool {
        return match($questType) {
            "chop_wood" => in_array($block->getTypeId(), [
                VanillaBlocks::OAK_LOG()->getTypeId(),
                VanillaBlocks::BIRCH_LOG()->getTypeId(),
                VanillaBlocks::SPRUCE_LOG()->getTypeId(),
                VanillaBlocks::JUNGLE_LOG()->getTypeId(),
                VanillaBlocks::ACACIA_LOG()->getTypeId(),
                VanillaBlocks::DARK_OAK_LOG()->getTypeId(),
            ], true),
    
            "mine_stone" => in_array($block->getTypeId(), [
                VanillaBlocks::STONE()->getTypeId(),
                VanillaBlocks::COBBLESTONE()->getTypeId(),
            ], true),
    
            default => false
        };
    }
}