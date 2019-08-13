<?php

/** -==•[ReportSPN]•==-
*
* Report Everybody is Abuse The Rules of Server.
*/

namespace ReportUI\SPNVN;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\Config;
use pocketmine\{Player, Server};
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;
use jojoe7777\FormAPI;

class Main extends PluginBase implements Listener{
	public $tag = "§6♥ §aS§bP§dN§e VN§6 ♥§r";
	public $report = "§6[§cReport Box§6]§r";
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getLogger()->info($this->tag . " §cReport§aSPN is Always Online.....");
		$this->getLogger()->info("\n\n§c§l•§b R༶E༶P༶O༶R༶T༶S༶P༶N༶V༶N༶ §6Version §e5\n§c❤️ §aStarting Plugin By §cBlackPMFury\n\n");
		$this->rp = new Config($this->getDataFolder() . "Report.yml", Config::YAML, []);
		$this->rp->save();
		$this->cr = new Config($this->getDataFolder() ."Cancel-Report.yml", Config::YAML, []);
		$this->cr->save();
		$this->user = new Config($this->getDataFolder() . "User.yml", Config::YAML, []);
	}
	
	public function onJoin(PlayerJoinEvent $ev){
		$player = $ev->getPlayer();
		$ten = $player->getName();
		$diemhienco = $this->XemDiem($ten);
		$msg = "
§a-==<§c•§a> §eĐiểm Thưởng Của Bạn <§c•§a>==-
§aĐiểm Hiện Tại:§e $diemhienco";
		if($this->rp->exists($player->getName())){
			$player->sendMessage($this->report . "§a You have a Report in Email!");
		}else{
			$player->sendMessage($this->report . "§e You do not have Any Report, G'Day!");
		}
		foreach($this->getServer()->getOnlinePlayers() as $players){
			$players->sendMessage($msg);
			if(!$this->kiemTra($ten)){
				$this->taoNguoiDung($ten);
			}
		}
	}
	
	public function taoNguoiDung($ten){
		$ten = strtolower($ten);
		$this->user->set($ten, 0);
		$this->user->save();
	}
	
	public function congDiem($ten, $diem){
		$ten = strtolower($ten);
		$diemhienco = $this->user->get($ten);
		$this->user->set($ten, $diemhienco + $diem);
		$this->user->save();
	}
	
	public function caiDiem($ten){
		$ten = strtolower($ten);
		$this->user($ten, $diem);
		$this->user->save();
	}
	
	public function xemDiem($ten){
		$ten = strtolower($ten);
		if($this->kiemTra($ten)){
			$diemhienco = $this->user->get($ten);
			return $diemhienco;
		}
		return false;
	}
		
	public function kiemTra($ten){
		$ten = strtolower($ten);
		if($this->user->exists($ten)){
			return true;
		}
		return false;
	}
		
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		switch(strtolower($cmd->getName())){
			case "tocao":
			if(!($sender instanceof Player)){
				$this->getServer()->getLogger()->warning("Please Use In Server!");
				return true;
			}
			$ten = strtolower($sender->getName());
			$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
			$form = $api->createSimpleForm(Function (Player $sender, $data){
				
				$result = $data;
				if ($result == null) {
				}
				switch ($result) {
					case 0:
					$sender->sendMessage("§c");
					break;
					case 1:
					$this->onReport($sender);
					break;
					case 2:
					$this->onAdminTools($sender);
					break;
				}
			});
			
			$form->setTitle("§6>> ".$this->report."§6 <<");
			$form->addButton("§cEXIT", 0);
			$form->addButton("§6>>§a Tố Cáo §6<<", 1);
			$form->addButton("§6>>§a Report Manager §6<<", 2);
			$form->sendToPlayer($sender);
		}
		return true;
	}
	
	public function onReport($sender){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createCustomForm(Function (Player $sender, $data){
			$ten = strtolower($sender->getName());
			switch($data[2]){
				case 0:
				$reason = "Hack Game/Speed";
				break;
				case 1:
				$reason = "Lừa Đảo/Trộm Cắp";
				break;
				case 2:
				$reason = "Không Tôn Trọng Người Khác/Staff";
				break;
				case 3:
				$reason = "Cố Ý Đả Kích/Chửi Người chơi Khác";
				break;
				case 4:
				$reason = "Others Reason";
				break;
			}
			$this->rp->set( $sender->getName(), ["Tên" => $data[1], "Lý Do" => $reason, "Lý Do Khác" => $data[3]]);
			$this->rp->save();
			$sender->sendMessage($this->report . "§a Tố Cáo§c ".$data[1]."§a Thành Công!");
			$this->getServer()->getLogger()->info($this->report . "§l§a Trường Hợp §c".$reason."§a Của §c".$data[1]."§a Bị báo Cáo Bởi§e ". $sender->getName());
			$sender->sendMessage($this->tag . "§l§a Đợi Owner Xét Duyệt!");
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "tell ".strtolower($data[1])." §eYou Have Report From§a ".$sender->getName()."§e With Reason §c".$reason." \ ". $data[3]);
			if(!(isset($data[3]))){
				$sender->sendMessage("§c•[1] §aĐiền Rõ Lỹ Do Tuỳ Chọn Nếu Có!");
				return true;
			}
			$this->congDiem($ten, +2);
		});
		$form->setTitle("§a-==• §e>>§a Tố Cáo§e << §a•==-");
		$form->addLabel("§a => Tố Cáo Đúng Lý Do Là Tốt Nhấn Để xét duyệt!");
		$form->addInput("§c❤️ §aTên §c❤️");
		$form->addDropdown("§c❤️ §aLý Do§c ❤️", ["Hack Game/Speed", "Cố Ý Trộm Đồ", "Lừa Đảo", "Không Tôn Trọng Người Khác/Staff", "Others Reason"]);
		$form->addInput("§aLý Do Khác (Opitions)");
		$form->sendToPlayer($sender);
	}
	
	public  function onAdminTools($sender){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(Function (Player $sender, $data){
			
			$ketqua = $data;
			if ($ketqua == null) {
			}
			switch ($ketqua) {
				case 0:
				$this->onCancelReport($sender);
				break;
				case 1:
				$this->managerReport($sender);
				break;
			}
		});
		$form->setTitle("§6>> §c<=> §aManager Report §c<=> §6<<");
		$form->addButton("§c Huỷ Tố Cáo", 0);
		$form->addButton("§c Manager Report", 1);
		$form->sendToPlayer($sender);
	}
	
	public function onCancelReport($sender){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createCustomForm(Function (Player $sender, $data){
			$this->cr->set( $sender->getName(), ["Tên" => $data[1], "Lý Do Huỷ" => $data[2]]);
			$this->cr->save();
			$sender->sendMessage($this->report . "§a Huỷ Đơn Tố cáo Của §c".$data[1]."§a Thành Công!");
			$this->rp->remove($data[1]);
		});
		$form->setTitle("§6>> §aHuỷ Tố Cáo §6<<");
		$form->addLabel("§e=> Huỷ Đơn Tố Cáo Nếu Bạn Nhầm Lẫn!");
		$form->addInput("§eTên:");
		$form->addInput("§eLý Do Huỷ:");
		$form->sendToPlayer($sender);
	}
	
	/**public function managerReport($sender){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createCustomForm(Function (Player $sender, $data){
			foreach($this->getServer()->getPlayerOnline() as $players){
			    if($sender->hasPermission("Manager.Admin")){
				    $sender->sendMessage("§c");
				}else{
				    $sender->sendMessage("§cBạn không có quyền để xem Report Manager Admin!");
				}
				foreach($this->rp->get("Tên") as $report){
					$report->save();
				}
			}
		});
		$form->setTitle("§6>> §c<=> §aManager Report§c <=> §6<<");
		$form->addLabel("§fReport #1:");
		$form->addLabel("§eTên Người bị Tố Cáo:§c ". $this->rp->get("Tên"));
		$form->addLabel("§eLý Do: §c". $this->rp->get("Lý Do"));
		$form->addLabel("§eLý Do khác (Nếu Có):§c ". $this->rp->get("Lý Do Khác"));
		$form->sendToPlayer($sender);
	}*/
	
	public function managerReport($sender){
		$form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(Function (Player $sender, $data){
		});
		$form->setTitle("§6>> §c<=> §aManager Report§c <=> §6<<");
		$form->addLabel("§aĐang Bảo Trì Hệ Thống!");
		$form->sendToPlayer($sender);
	}
}