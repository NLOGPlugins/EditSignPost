<?php
/**
 * Created by PhpStorm.
 * User: Home
 * Date: 2019-03-01
 * Time: 오후 2:15
 */

namespace nlog\editsign;


use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;

class EditSignPostCommand extends PluginCommand {

    public function __construct(Loader $owner) {
        parent::__construct('표지판', $owner);
        $this->setDescription('표지판 내용 수정을 시작합니다.');
        $this->setAliases(['sign']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) {
            $sender->sendMessage(Loader::$prefix . "인게임 내에서 사용하세요.");
            return true;
        }

        $opt = $args[0] ?? '';
        if ($opt === '수정' || $opt === 'fix' || $opt === 'f') {
            if ((Loader::$process[$sender->getName()] ?? 'none') === 'edit') {
                $sender->sendMessage(Loader::$prefix . "이미 수정 작업 중 입니다.");
                return true;
            }
            Loader::$process[$sender->getName()] = 'edit';
            $sender->sendMessage(Loader::$prefix . "수정 작업을 시작합니다. 표지판을 터치해주세요.");
        } elseif ($opt === '취소' || $opt === 'cancel' || $opt === 'c') {
            if (!isset(Loader::$process[$sender->getName()])) {
                $sender->sendMessage(Loader::$prefix . "수정 작업을 하고 있지 않습니다.");
                return true;
            }
            unset(Loader::$process[$sender->getName()]);
            $sender->sendMessage(Loader::$prefix . "작업을 취소하였습니다.");
        } else {
            $sender->sendMessage(Loader::$prefix . "/{$commandLabel} 수정 - 표지판을 수정합니다.");
            $sender->sendMessage(Loader::$prefix . "/{$commandLabel} 취소 - 표지판 수정 작업을 취소합니다.");
        }

        return true;
    }

}