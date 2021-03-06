<?php

namespace DonateSystem;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;

use onebone\economyapi\EconomyAPI;

class main extends PluginBase implements Listener
{

    public function onEnable()
    {
        $this->data = new Config($this->getDataFolder() . "data.yml", Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if(!$this->data->exists("MAX")){
            $this->data->set("MAX","1000000");
            $this->data->save();
            $this->data->reload();
        }
        if(!$this->data->exists("DATA")){
            $this->data->set("DATA",0);
            $this->data->save();
            $this->data->reload();
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $max = $this->data->get("MAX");
        $data = $this->data->get("DATA");
        $money = EconomyAPI::getInstance()->myMoney($sender->getName());
        switch ($command->getName()) {
            case"donate":
                if(!isset($args[0])){
                    $sender->sendMessage("§e[寄付システム] /donate < pay | check | setting >");
                }else {
                    switch ($args[0]) {
                        case"pay":
                            if ($data >= $max) {
                                $sender->sendMessage("§e[寄付システム] 現在は寄付金を受け付けていません。");
                            } else {
                                if (!isset($args[1])) {
                                    $sender->sendMessage("§e[寄付システム] 寄付する金額を指定してください。");
                                } else {
                                    if ($args[1] > $money) {
                                        $sender->sendMessage("§e[寄付システム] 残高不足です。");
                                    } else {
                                        EconomyAPI::getInstance()->reduceMoney($sender->getName(), $args[1]);
                                        $this->data->set("DATA", $data + $args[1]);
                                        $this->data->save();
                                        $this->data->reload();
                                        $sender->sendMessage("§6[寄付システム] {$args[1]}円を寄付しました。");
                                    }
                                }
                            }
                            break;
                        case"check":
                            $sender->sendMessage("§6[寄付システム] {$max}円のうち{$data}円が集まっています。");
                            break;
                        case"setting":
                            if(!$sender->isOp()){
                                $sender->sendMessage("§cこのコマンドを実行する権限がありません。");
                            }else{
                                if(!isset($args[1])){
                                    $sender->sendMessage("§e[寄付システム] 設定をする金額を入力してください。");
                                }else{
                                    $this->data->set("MAX",$args[1]);
                                    $this->data->save();
                                    $this->data->reload();
                                    $sender->sendMessage("§6[寄付システム] 上限を{$args[1]}円に設定しました。");
                                }
                            }
                            break;
                        case"reset":
                            if(!$sender->isOp()){
                                $sender->sendMessage("§cこのコマンドを実行する権限がありません。");
                            }else{
                                $this->data->set("DATA",0);
                                $this->data->save();
                                $this->data->reload();
                                $sender->sendMessage("§6[寄付システム] 寄付金額をリセットしました。");
                            }
                            break;
                        default:
                            $sender->sendMessage("§e[寄付システム] /donate < pay | check | setting | reset >");
                            break;
                    }
                }
                break;
        }
        return true;

    }

}