<?php

namespace quest;

use pocketmine\scheduler\Task;
use quest\Quest;

class TaskScheduler extends Task {

    /** @var Quest */
    private Quest $plugin;

    public function __construct(Quest $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        // 15 dakikada bir tüm oyuncuların görevlerini yeniliyoruz
        $this->plugin->getQuestmanager()->refreshAllPlayerTasks();
    }
}
