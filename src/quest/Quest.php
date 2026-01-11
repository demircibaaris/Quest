<?php

namespace quest;

use pocketmine\plugin\PluginBase;
use quest\events\BlockBreakListener;

class Quest extends PluginBase {

    private QuestManager $questManager;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        
        $this->questManager = new QuestManager($this);

        $this->getServer()->getCommandMap()->register("gorev", new MyQuest($this));

        $time = $this->getConfig()->get("refresh-time", 900);
        $this->getScheduler()->scheduleRepeatingTask(new TaskScheduler($this), $time * 20);

        $this->getServer()->getPluginManager()->registerEvents(new BlockBreakListener($this), $this);
    }

    public function getQuestManager(): QuestManager {
        return $this->questManager;
    }
}