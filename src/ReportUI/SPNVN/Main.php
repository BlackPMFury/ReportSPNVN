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
use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener{
	public $tag = "§6♥ §aS§bP§dN§e VN§6 ♥§r";
	public $report = "§6[§cReport Box§6]§r";
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getLogger()->info($this->tag . " §cReport§aSPN is Always Online.....");
		$this->getLogger()->info("\n\n§c§l•§b R༶E༶P༶O༶R༶T༶S༶P༶N༶V༶N༶ §6Version §e5\n§c❤️ §aStarting Plugin By §cBlackPMFury\n\n");
		$this->rp = new Config($this->getDataFolder() . "Report.yml", Config::YAML, []);
		$this->cr = new Config($this->getDataFolder() ."Cancel-Report.yml", Config::YAML, []);
		$this->user = new Config($this->getDataFolder() . "User.yml", Config::YAML, []);
		$this->scam = new Config($this->getDataFolder() .  "ScamReport.yml", Config::YAML, []);
		$this->EconomyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		$this->eco = EconomyAPI::getInstance();
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
        $player->sendMessage($msg);
		if(!$this->kiemTra($ten)) {
            $this->taoNguoiDung($ten);
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

	public function truDiem($ten, $diem){
	    $ten = strtolower($ten);
	    $this->congDiem($ten, -$diem);
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
					$this->menu($sender);
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
				$reason = "Hacking/cheat";
				break;
				case 1:
				$reason = "Phá Hoại Tài Sản";
				break;
				case 2:
				$reason = "non-respect Statf";
				break;
				case 3:
				$reason = "non-respect Other Player";
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
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "tell ".strtolower($data[1])." §eYou Have Report From§a ".$sender->getName()."§e With Reason §c".$reason."§c \ §aOther Reason:". $data[3]);
			if(!(isset($data[3]))){
				$sender->sendMessage("§c•[1] §aĐiền Rõ Lỹ Do Tuỳ Chọn Nếu Có!");
				return true;
			}
			$this->congDiem($ten, +4);
			if($this->xemDiem($ten) == 100){
				$sender->sendMessage("You got 400000 Coins!");
				$this->eco->addMoney($ten, 400000);
				$this->truDiem($ten, -100);
				return true;
			}
		});
		$form->setTitle("§a-==• §e>>§a Tố Cáo§e << §a•==-");
		$form->addLabel("§a => Tố Cáo Đúng Lý Do Là Tốt Nhấn Để xét duyệt!");
		$form->addInput("§c❤️ §aTên §c❤️");
		$form->addDropdown("§c❤️ §aLý Do§c ❤️", ["Hacking/cheat", "Phá Hoại Tài Sản", "non-respect Statf", "non-respect Other Player", "Others Reason"]);
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
			$name = $this->rp->get($sender->getName());
			$this->cr->set( $sender->getName(), ["Tên" => $data[1], "Lý Do Huỷ" => $data[2]]);
			$this->cr->save();
			$sender->sendMessage($this->report . "§a Huỷ Đơn Tố cáo Của §c".$data[1]."§a Thành Công!");
			//$this->rp->remove($data[1], $name);
            $ten = $name["Tên"];
            $reason = $name["Lý Do"];
            $rsother = $name["Lý Do Khác"];
            $this->rp->remove($ten);
            $this->rp->remove($reason);
            $this->rp->remove($rsother);
		});
		$form->setTitle("§6>> §aHuỷ Tố Cáo §6<<");
		$form->addLabel("§e=> Huỷ Đơn Tố Cáo Nếu Bạn Nhầm Lẫn!");
		$form->addInput("§eTên:");
		$form->addInput("§eLý Do Huỷ:");
		$form->sendToPlayer($sender);
	}
	
	public function managerReport($sender){
		$name = $this->rp->get($sender->getName());
		$ten = $name["Tên"];
		$reason = $name["Lý Do"];
		$other = $name["Lý Do Khác"];
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createCustomForm(Function (Player $sender, $data){
			foreach($this->getServer()->getOnlinePlayers() as $players){
			    if($players->hasPermission("Manager.Admin")){
				    $players->sendMessage("§l§aSuccess!");
				}else{
				    $players->sendMessage("§l§cBạn không có quyền để xem Report Manager Admin!");
				}
				/**foreach($this->rp->getAll() as $report){
				}*/
			}
		});
		$form->setTitle("§6>> §c<=> §aManager Report§c <=> §6<<");
		$form->addLabel("§fReport #1:");
		$form->addLabel("§eTên Người bị Tố Cáo:§c ". $ten);
		$form->addLabel("§eLý Do: §c". $reason);
		$form->addLabel("§eLý Do khác (Nếu Có):§c ". $other);
		$form->sendToPlayer($sender);
	}
	
	/**public function managerReport($sender){
		$form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(Function (Player $sender, $data){
		});
		$form->setTitle("§6>> §c<=> §aManager Report§c <=> §6<<");
		$form->addLabel("§aĐang Bảo Trì Hệ Thống!");
		$form->sendToPlayer($sender);
	}*/

	public function specReport($sender){
	    $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
	    $form = $api->createCustomForm(Function (Player $sender, $data) {
            $ten = $sender->getName();
            $this->scam->set($sender->getName(), ["Người Bị kiện" => $data[1], "Bạn bị mất những gì" => $data[2], "Time" => $data[3]]);
            $this->scam->save();
            if ($this->xemDiem($ten) < 20) {
                $this->congDiem($ten, +2);
            }elseif($this->xemDiem($ten) > 20){
                $this->congDiem($ten, +40);
            }elseif($this->xemDiem($ten) > 40){
                $this->congDiem($ten, +50);
                return true;
            }
            if($this->scam->exists($sender->getName())){
                $sender->sendMessage($this->tag . "§c You're Already Reported another people!");
            }else{
                $sender->sendMessage("§a Report Scamer: §c".$data[1]." §aCompleted");
                $this->getServer()->getLogger()->notice($this->tag . " An Player is Reported a Scamer with time§e ".$data[3]." §a- Reporter: §b". $sender->getName());
            }
        });
	    $form->setTitle("§a-==• §e>>§a Tố Cáo§e << §a•==-");
	    $form->addLabel("§c=> §aĐăng Bằng Chứng Và kèm theo Thời gian tố cáo [/tocao] lên fanpage");
	    $form->addInput("§aTên người bị kiện:");
	    $form->addInput("§aMất những gì");
	    $form->addInput("§aThời Gian bị scam:");
	    $form->sendToPlayer($sender);
    }

    public function menu($sender){
	    $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
	    $form = $api->createSimpleForm(Function (Player $sender, $data){
	        $ketqua = $data;
	        if ($ketqua == null) {
            }
	        switch($ketqua) {
                case 0:
                    $this->onReport($sender);
                    break;
                case 1:
                    $this->specReport($sender);
                    break;
                case 2:
                    $sender->sendMessage($this->tag . " §cEXIT!");
                    break;
            }
        });
	    $form->setTitle("§a-==• §e>>§a Tố Cáo§e << §a•==-");
	    $form->addButton("§a-==• §c>>§a Tố Cáo Vấn Đề §c<<§a •==-", 0);
	    $form->addButton("§a-==• §c>>§a Tố Cáo Lừa đảo (Scam) §c<<§a •==-", 1);
	    $form->addButton("§cEXIT!");
	    $form->sendToPlayer($sender);
    }
}