<?php

declare(strict_types=1);

namespace quest\events;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\Server;
use quest\Quest;

class BlockBreakListener implements Listener {

    private Quest $plugin;

    public function __construct(Quest $plugin) {
        $this->plugin = $plugin;
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
    
        if ($event->isCancelled()) {
            return;
        }
    
        $playerTask = $this->plugin->getQuestManager()->getPlayerTask($player);
        if ($playerTask === null) {
            return;
        }
        
        $questType = $playerTask["type"];
    
        if ($this->plugin->getQuestManager()->isValidBlockForQuest($block, $questType)) {
            
            // Hedefi düşür
            $playerTask["current_goal"]--;
            
            // Güncel görevi kaydet
            $this->plugin->getQuestManager()->setPlayerTask($player, $playerTask);

            if ($playerTask["current_goal"] <= 0) {
                // GÖREV BİTTİ
                $msg = $this->plugin->getConfig()->getNested("messages.quest-completed");
                $msg = str_replace(["{name}"], [$playerTask["name"]], $msg);
                $player->sendMessage($msg);
                
                // Config'deki komutları çalıştır
                if(isset($playerTask["commands"]) && is_array($playerTask["commands"])){
                    $console = new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage());
                    foreach($playerTask["commands"] as $cmd){
                        // {player} değişkenini oyuncu ismiyle değiştir
                        $cmd = str_replace("{player}", $player->getName(), $cmd);
                        Server::getInstance()->dispatchCommand($console, $cmd);
                    }
                }
    
                $this->plugin->getQuestManager()->clearPlayerTask($player);
            } else {
                // Popup göster
                $player->sendPopup("§eGörev: §f{$playerTask['name']} §7| Kalan: §a{$playerTask['current_goal']}");
            }
        }
    }
}