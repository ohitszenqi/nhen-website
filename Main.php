<?php

namespace zenqi;
use pocketmine\math\Vector3;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use zenqi\NPC;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use pocketmine\inventory\BaseInventory;
use Ifera\ScoreHud\event\ServerTagUpdateEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use mcbeany\CustomSound\CustomSound;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use Ifera\ScoreHud\event\TagsResolveEvent;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\item\Item;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\Skin;
use onebone\economyapi\EconomyAPI;

use pocketmine\event\player\PlayerInteractEvent;

class main extends PluginBase implements Listener {
    public $queued = [];
    public $bruh = [];
    public $sneak;
    public $elo;
    public function show() {
         return count($this->bruh);
        }
    public function onEnable() {
        $this->getLogger()->info("{$this->show()}");
        Entity::registerEntity(NPC::class, true); // onEnable
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $server = Server::getInstance();
        $server->loadLevel("debuff");
        $server->loadLevel("bruh");
    }
    public function onMove(PlayerMoveEvent $event) {
      $ev = new PlayerTagUpdateEvent(
                    $event->getPlayer(), 
                    new ScoreTag("crouch.toggle", "Walking")
                );
            $ev->call();
            $this->sneak = "Walking";
    }
    
    public function onDeathz(PlayerDeathEvent $event) {
      $player = $event->getPlayer();
      $ev = new PlayerTagUpdateEvent(
                                    $event->getPlayer(),
                                    new ScoreTag("crouch.money", EconomyAPI::getInstance()->myMoney($player))
                                );
                            $ev->call();
                            $this->elo = EconomyAPI::getInstance()->myMoney($player);;
        }
      public function Bruh(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $ev = new PlayerTagUpdateEvent(
                                                            $event->getPlayer(), 
                                                            new ScoreTag("crouch.money", EconomyAPI::getInstance()->myMoney($player))
                                                        );
                                                    $ev->call();
                                                    $this->elo = EconomyAPI::getInstance()->myMoney($player);
      }
    public function onSprint(PlayerToggleSprintEvent $event) {
      $ev = new PlayerTagUpdateEvent(
                    $event->getPlayer(), 
                    new ScoreTag("crouch.toggle", "Sprinting")
                );
            $ev->call();
      $this->sneak = "Sprinting";
    }
    public function onSneak(PlayerToggleSneakEvent $event) {
      $ev = new PlayerTagUpdateEvent(
              $event->getPlayer(), 
              new ScoreTag("crouch.toggle", "Sneaking")
          );
      $ev->call();
      $this->sneak = "Sneaking";
    }
    public function showSneak() {
      return "{$this->sneak}";
    }
    public function showElo() {
      return "{$this->elo}";
    }
    public function onTagResolve(TagsResolveEvent $event){
        $player = $event->getPlayer();
        $tag = $event->getTag();
        $toggle = $this->showSneak();
        switch($tag->getName()){
            case "crouch.toggle":
                $tag->setValue($toggle);
            break;
            case "crouch.money":
            $tag->setValue($this->showElo());
            break;
        }
    }
    public function onQuit(PlayerQuitEvent $event) {
      $event->setQuitMessage("");
    }
    public function onJoined(PlayerJoinEvent $event) {
     
        $event->setJoinMessage("");
        $player = $event->getPlayer();
          $this->elo = EconomyAPI::getInstance()->myMoney($player);
                var_dump($this->elo);
        $player->getLevel()->setTime(1000);
       $level = $this->getServer()->getLevelByName("bruh");
        $player->teleport(new Position(262, 68, 267, $level));
        $slot5 = item::get(267, 0, 1);
        $slot5->setCustomName("§c§l1v1");
        if ($player->getName() === "iWaSWhiteBox"){
          
        }
        $player->setDisplayName("§7[§5Member§7] §7┇ {$player->getName()}");
        $player->setNameTag("§7[§5Member§7] §7┇ §b{$player->getName()}\n§7Ping: §b{$player->getPing()}ms §7┇ §b{$player->getHealth()}/{$player->getMaxHealth()} HP");
        $player->getInventory()->clearAll();
        $player->getInventory()->setItem(4, $slot5);
        $slot6 = item::get(276, 0, 1);
        $slot6->setCustomName("§c§lFFA");
        $player->getInventory()->setItem(5, $slot6);
        if (EconomyAPI::getInstance()->myMoney($player) === 0) {
        EconomyAPI::getInstance()->setMoney($player, 1000);
        }
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
                    $form = $api->createSimpleForm(function (Player $player, int $data = null){
                        $result = $data;
                        if($result === null){
                            return true;
                        }
                      switch($result) {
                        case 0: 
                          $pos = $player->asVector3();
                CustomSound::playSound("oniichan", $player, $pos);
                        break;
                      }
                    });
                $form->setTitle("§l§5Welcome!");
                $form->setContent("§5Hello §f{$player->getName()}!\n§5This server is still on the beta test,\n§5we are hoping that you are gonna enjoy the server.\n§5Thanks, §fZenqi§5 & §fAbyzz\n\n\n\n\n");
                $form->addButton("§l§5Continue");
                $form->sendToPlayer($player);
    }
    public function queue(Player $player) {
      $this->bruh[] = $player->getName();
      
    }
    
    /*
    public function queuePlayer(string $playername) : void {
        if(empty($this->queued)) { # If no one's in the queue
            $this->queued[] = $playername; # Set the player to the queue
            return;
        } # If someone is already in the queue
        $codeToStartTheGame; # Start the game
        foreach(array_values($this->queued) as $playername) {
          $x = 58;
          $y = 69;
          $z = 6;
          $lvl = $this->getServer()->getDefaultLevel()->getName();
          $player1 = $this->getServer()->getPlayerExact($this->queued[0]);
          $player2 = $this->getServer()->getPlayerExact($this->queued[1]);
          $level = $this->getServer()->getLevelByName("debuff");
          $server = Server::getInstance();
              $player = $server->getPlayer($playername);
              $player1->teleport(new Position(270, 4, 289, $level));
              $player2->teleport(new Position(270, 4, 266, $level));
          }
          
        
        $this->queued = array_values($this->queued);
        return;
    }
    */
    public function Doctor(Player $player, Player $p2) {
                
                $nbt = Entity::createBaseNBT(new Vector3($p2->getX(), $p2->getY(), $p2->getZ()), null, $p2->getYaw(), $p2->getPitch());
                              $skin = $player->getSkin();
                              $tag = new CompoundTag("Skin", [
                                          new StringTag("Name", $skin->getSkinId()),
                                          new ByteArrayTag("Data", $skin->getSkinData()),
                                          new ByteArrayTag("CapeData", $skin->getCapeData()),
                                          new StringTag("GeometryName", $skin->getGeometryName()),
                                          new ByteArrayTag("GeometryData", $skin->getGeometryData())
                              ]);
                              $nbt->setTag($tag);
                              $npc = new NPC($player->getLevel(), $nbt);
                             $npc->setHealth(20);
                             $npc->setMaxHealth(20);
                             $pos = $player->asVector3();
                             CustomSound::playSound("bruh", $player, $pos);
                             
                                         $npc->spawnToAll();
                               }
    public function onDeath(PlayerDeathEvent $event) {
           if ($event->getPlayer()->getLastDamageCause() instanceof EntityDamageByEntityEvent) {
           $player = $event->getPlayer();
           $slot5 = item::get(267, 0, 1);
                   $slot5->setCustomName("§c§l1v1");
           $last = $player->getLastDamageCause();
           $killer = $last->getDamager();
           $server = Server::getInstance();
           $killer->sendMessage("§5+25 Elo");
           EconomyAPI::getInstance()->addMoney($killer, 25);
           $player->getInventory()->clearAll();
                   $player->getInventory()->setItem(4, $slot5);
                   $slot6 = item::get(276, 0, 1);
                   $slot6->setCustomName("§c§lFFA");
                   $player->getInventory()->setItem(5, $slot6);
            $event->setDrops([]);
            $event->setDeathMessage("");
           $server->broadcastMessage("[§5HP§r] §5{$killer->getName()} §6killed {$player->getName()}");
           $killer->sendMessage("u kelled samwan");
           $this->Doctor($killer, $player);
           $level = $this->getServer()->getLevelByName("bruh");
                              $player->teleport(new Position(262, 68, 267, $level));
            
           }
         }
    
    public function onCommand(CommandSender $sender, Command $cmd, String $label, Array $arrays) : bool {
      switch($cmd->getName()) {
        case "elo":
      if ($sender instanceof Player) {
        $money = EconomyAPI::getInstance()->myMoney($sender);
        $sender->sendmessage("Your Elo: {$money}");
        
        var_dump($this->bruh);
                            foreach ($this->bruh as $bruhz) {
                              $level = $this->getServer()->getLevelByName("debuff");
                              $p1 = Server::getInstance()->getPlayerExact((string) $bruhz[1]);
                              $p2 = Server::getInstance()->getPlayerExact((string) $bruhz[2]);
                            
                        $p1->teleport(new Position(270, 4, 289, $level));
                                            $p2->teleport(new Position(270, 4, 266, $level));
                            }
                            
        
                          
                        
                          
      }
       
      break;
       case "try":
                     if ($sender instanceof Player) {
                       $player = $sender;
                    $nbt = Entity::createBaseNBT(new Vector3($player->getX(), $player->getY(), $player->getZ()), null, $player->getYaw(), $player->getPitch());
                    $skin = $sender->getSkin();
                    $tag = new CompoundTag("Skin", [
                                new StringTag("Name", $skin->getSkinId()),
                                new ByteArrayTag("Data", $skin->getSkinData()),
                                new ByteArrayTag("CapeData", $skin->getCapeData()),
                                new StringTag("GeometryName", $skin->getGeometryName()),
                                new ByteArrayTag("GeometryData", $skin->getGeometryData())
                    ]);
                    $nbt->setTag($tag);
                    $npc = new NPC($player->getLevel(), $nbt);
                   $npc->setHealth(20);
                   $npc->setMaxHealth(20);
                   $pos = $player->asVector3();
                   CustomSound::playSound("bruh", $player, $pos);
                   
                               $npc->spawnToAll();
                     }
                   break;
     }
     
     return true;
    }
/*
    public function onDamage(EntityDamageEvent $event) {
        $victim = $event->getEntity();
        if($event->getFinalDamage() >= $victim->getHealth()) {
            $event->setCancelled();
$lastDamageCause = $victim->getLastDamageCause();
$killer = $victim->getDamager();
$victim->setHealth($victim->getMaxHealth());
EconomyAPI::getInstance()->addMoney($killer, $amount);
                        $killer->addTitle("§7You got 500 ELO!", "§5Congrats!");
                     Server::getInstance()->broadcastMessage("[§5HP§r] {$killer->getNamed()} killed {$victim->getName()}");
        }
    }
*/
    public function playerscount() {
        $bruh = count($this->queued);
        return $bruh;
    }
    public function onInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $item = $event->getPlayer();
        $name = $player->getName();
        $itemname = $player->getInventory()->getItemInHand()->getName();
        if ($itemname === "Random Tags") {
            $player->sendmessage("§5You've got the gay tag!");
            $player->setDisplayName("[§5Gay§r] {$name}");
            return true;
        }
        if ($itemname === "§c§l1v1") {
          $player->sendmessage("Under maintenance");
        }
        if ($itemname === "§c§lFFA") {
          $player->sendmessage("how are u");
            $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
            $form = $api->createSimpleForm(function (Player $player, int $data = null){
                $result = $data;
                if($result === null){
                    return true;
                }
        switch($result) {
          case 0:
            $player->getLevel()->setTime(1000);
            $ender = Item::get(368, 0, 1);
            $unbreakable = Enchantment::getEnchantment(17);
            $protect = Enchantment::getEnchantment(0);
            $sharp = Enchantment::getEnchantment(9);
            $chestplate = Item::get(311, 0, 1);
            $helmet = Item::get(310, 0, 1);
              $leggings = Item::get(312, 0, 1);
              $boots = Item::get(313, 0, 1);
              $sword = Item::get(276, 0, 1);
             $unbr = new EnchantmentInstance($unbreakable, 3);
             $prot = new EnchantmentInstance($protect, 2);
             $sharpness = new EnchantmentInstance($sharp, 4);
             $chestplate->addEnchantment($prot);
             $chestplate->addEnchantment($unbr);
             $sword->addEnchantment($sharpness);
             $sword->addEnchantment($unbr);
             $helmet->addEnchantment($prot);
             $inventory = $player->getInventory();
             
                          $helmet->addEnchantment($unbr);
              $leggings->addEnchantment($prot);
                           $leggings->addEnchantment($unbr);
              $boots->addEnchantment($prot);
                           $boots->addEnchantment($unbr);
            $invs = $player->getInventory();
            $level = $this->getServer()->getLevelByName("debuff");
            $player->teleport(new Position(270, 4, 289, $level));
            
            $player->getArmorInventory()->setChestplate($chestplate);
            $player->getArmorInventory()->setHelmet($helmet);
                        $player->getArmorInventory()->setLeggings($leggings);
                        $player->getArmorInventory()->setBoots($boots);
                        $inventory->setContents(array_fill(2, $inventory->getDefaultSize(), Item::get(438, 21, 1)));
                    $inventory->setItem(0, $sword);
                    $inventory->setItem(1, $ender);
          break;
          case 1:
            $this->queue($player);
            $player->sendMessage("{$this->show()}");
          break;
          case 2:
            unset($this->bruh); 
          break;
    }
 });
             $level = $this->getServer()->getLevelByName("debuff");
                 $pcount = count($level->getPlayers());
                 $form->setTitle("§c§lFFA");
                 $form->setContent("Click a button to join a gamemode");
                 $form->addbutton("NoDebuff\nPlayers: {$pcount}");
                 $form->addButton("try");
                 $form->addButton("nigg");
                 $form->sendToPlayer($player);
}
}
                // On Button Click
                # $player instance of Player object
                # Queues the player with the above function
                

    public function onBreak(BlockBreakEvent $event) {
        if($event->getBlock()->getId() === 1) {
            $random = mt_rand(1, 100);
            if($random <= 20) {
                $player = $event->getPlayer();
                $name = $player->getName();
                $player->sendmessage("[§6zenqi§r] §6{$name} §rgot a random tag while mining!");
                $drops = array();
                $itembruh = item::get(131, 0, 1);
                $itembruh->setCustomName("Random Tags");
                $drops[] = $itembruh;
                $event->setDrops($drops);
            }
        }
    }
}