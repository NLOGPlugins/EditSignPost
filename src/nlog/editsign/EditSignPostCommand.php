<?php
/**
 * Copyright (C) 2017-2019  NLOG(엔로그)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
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