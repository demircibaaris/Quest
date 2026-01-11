<?php

namespace quest;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\player\Player;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;

class MyQuest extends Command implements PluginOwned {

    use PluginOwnedTrait;

    public function __construct(Quest $plugin) {
        parent::__construct("gorev", "Görevinizi görüntülemek için kullanabilirsiniz.", "/gorev");
        $this->setPermission("command.myquest");
        $this->owningPlugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Oyun içinden kullanın.");
            return false;
        }
        $this->showMyQuest($sender);
        return true;
    }

    public function showMyQuest($player){
        $myquest = $this->owningPlugin->getQuestManager()->getPlayerTask($player);
        $config = $this->owningPlugin->getConfig();

        if($myquest === null){
            return $player->sendMessage($config->getNested("messages.no-quest"));
        }

        // Configden mesaj formatını al ve doldur
        $content = $config->getNested("messages.gui-content");
        $content = str_replace(
            ["{name}", "{desc}", "{goal}", "{reward}"],
            [$myquest['name'], $myquest['description'], $myquest['current_goal'], $myquest['reward_text']],
            $content
        );

        $form = new MenuForm(
            $config->getNested("messages.gui-title"),
            $content,
            [new MenuOption("Kapat")],
            function (Player $player, int $selected): void {}
        );

        $player->sendForm($form);
    }
}