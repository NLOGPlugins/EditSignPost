<?php
namespace nlog\NLOGSignFix;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\Plugin;
use pocketmine\tile\Sign;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use ifteam\SimpleArea\database\area\AreaProvider;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use TelegramBot\Api\BotApi;
class Main extends PluginBase implements Listener {
	/** @var array */
	public $process = [];
	/** @var bool */
	public $area = false;
	const TAG = "§b§l[ SignFix ]§r §7";
	const FORM_ID = 2098;
	public function onEnable() {
		$this->getLogger()->info("표지판 수정 플러그인 활성화");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if ($this->getServer()->getPluginManager()->getPlugin("SimpleArea") instanceof Plugin) {
			$this->area = true;
		}
	}
	/**
	 * 커맨드의 label과 args에 공백을 정렬하는 소스입니다.
	 * - Made by 엔로그 (nnnlog, NLOG)
	 *
	 * @param string $label
	 * @param array $args
	 * @return array
	 */
	public function lineup(string $label, array $args) {
		$explode = explode(" ", $label);
		$cmd = "";
		if ($explode !== false) {
			$label = $explode[0];
			unset($explode[0]);
			$i = 0;
			foreach ($explode as $word) {
				if ($word !== "") {
					$cmd .= $word;
					if ($i === 0) {
						$i++;
						$cmd .= " ";
					}
				}
			}
		}
		if ($cmd !== "") {
			$array = [];
			$args[0] = $cmd . " " . $args[0];
			foreach ($args as $k => $v) {
				$explode = explode(" ", $args[$k]);
				if ($explode !== false) {
					foreach ($explode as $v1) {
						if ($v1 === "") {
							continue;
						}
						$array[] = $v1;
					}
				}
			}
		}
		elseif (!empty($args)) {
			$array = [];
			foreach ($args as $k => $v) {
				$explode = explode(" ", $args[$k]);
				if ($explode !== false) {
					foreach ($explode as $v1) {
						if ($v1 === "") {
							continue;
						}
						$array[] = $v1;
					}
				}
			}
		}
		return ["label" => $label, "args" => $array];
	}
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
		if (!$sender instanceof Player) {
			$sender->sendMessage(self::TAG . "인게임 내에서 사용하세요.");
			return true;
		}
		$l = $this->lineup($label, $args);
		$label = $l["label"];
		$args = $l["args"];
		$sub = $args[0] ?? "default";
		switch ($sub) {
			case "생성":
				if (isset($this->process[$sender->getName() ])) {
					$sender->sendMessage(self::TAG . "이미 생성 작업 중 입니다.");
					return true;
				}
				$this->process[$sender->getName() ] = "";
				$sender->sendMessage(self::TAG . "수정 작업을 시작합니다. 표지판을 터치해주세요.");
				return true;
			case "취소":
				if (!isset($this->process[$sender->getName() ])) {
					$sender->sendMessage(self::TAG . "생성 작업을 하고 있지 않습니다.");
					return true;
				}
				unset($this->process[$sender->getName() ]);
				$sender->sendMessage(self::TAG . "생성 작업을 취소하였습니다.");
				return true;
			default:
				$sender->sendMessage(self::TAG . "/{$label} 수정 - 표지판을 수정합니다.");
				$sender->sendMessage(self::TAG . "/{$label} 취소 - 표지판 수정 작업을 취소합니다.");
				return true;
		}
	} //onCommand
	public function onRecieve(DataPacketReceiveEvent $ev) {
		$packet = $ev->getPacket();
		if ($packet instanceof ModalFormResponsePacket && $packet->formId === self::FORM_ID) {
			$data = json_decode($packet->formData, true);
			$line1 = $data[0];
			$line2 = $data[1];
			$line3 = $data[2];
			$line4 = $data[3];
			$sign = $this->process[$ev->getPlayer()->getName() ];
			$sign->setText($line1, $line2, $line3, $line4);
		}
	}
	public function onTouch(PlayerInteractEvent $ev) {
		if (!isset($this->process[$ev->getPlayer()->getName() ])) {
			return;
		}
		$tile = $ev->getBlock()->getLevel()->getTile($ev->getBlock()->asVector3());
		if ($tile instanceof Sign) {
			unset($this->process[$ev->getPlayer()->getName() ]);
			if ($ev->getPlayer()->isOp()) {
				$pk = new ModalFormRequestPacket();
				$pk->formId = self::FORM_ID;
				$pk->formData = $this->makeForm($tile->getLine(0), $tile->getLine(1), $tile->getLine(2), $tile->getLine(3));
				$ev->getPlayer()->dataPacket($pk);
				$this->process[$ev->getPlayer()->getName() ] = $tile;
			}
			elseif ($this->area) {
				$section = AreaProvider::getInstance()->getArea($ev->getBlock()->getLevel(), $ev->getBlock()->getX(), $ev->getBlock()->getZ());
				if ($section !== null && $section->isResident($ev->getPlayer()->getName())) {
					$pk = new ModalFormRequestPacket();
					$pk->formId = self::FORM_ID;
					$pk->formData = $this->makeForm($tile->getLine(0), $tile->getLine(1), $tile->getLine(2), $tile->getLine(3));
					$ev->getPlayer()->dataPacket($pk);
					$this->process[$ev->getPlayer()->getName() ] = $tile;
				}
			}
		} //sign instance
		
	} //interact
	public function makeForm($line1 = "", $line2 = "", $line3 = "", $line4 = "") {
		$json = [];
		$json["type"] = "custom_form";
		$json["title"] = "표지판 수정";
		$json["contents"] = [];
		$json["contents"][] = ["type" => "input", "text" => "첫번째 줄", "default" => $line1, "placeholder" => "첫번째 줄"];
		$json["contents"][] = ["type" => "input", "text" => "두번째 줄", "default" => $line2, "placeholder" => "두번째 줄"];
		$json["contents"][] = ["type" => "input", "text" => "세번째 줄", "default" => $line3, "placeholder" => "세번째 줄"];
		$json["contents"][] = ["type" => "input", "text" => "네번째 줄", "default" => $line4, "placeholder" => "네번째 줄"];
		return json_encode($json);
	}
	public function onQuit(PlayerQuitEvent $ev) {
		if (isset($this->process[$ev->getPlayer()->getName() ])) {
			unset($this->process[$ev->getPlayer()->getName() ]);
		}
	}
} //클래스 괄호

?>
