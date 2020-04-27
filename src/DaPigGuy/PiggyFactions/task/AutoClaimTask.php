<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyFactions\task;

use DaPigGuy\PiggyFactions\claims\ClaimsManager;
use DaPigGuy\PiggyFactions\PiggyFactions;
use DaPigGuy\PiggyFactions\players\PlayerManager;
use pocketmine\scheduler\Task;

class AutoClaimTask extends Task
{
    /** @var PiggyFactions */
    private $plugin;

    public function __construct(PiggyFactions $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick): void
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
            if (($member = PlayerManager::getInstance()->getPlayer($p->getUniqueId())) !== null && ($faction = $member->getFaction()) !== null && $member->isAutoClaiming()) {
                if (floor($faction->getPower() / $this->plugin->getConfig()->getNested("factions.claim.cost", 1)) > ($total = count(ClaimsManager::getInstance()->getFactionClaims($faction)))) {
                    if ($total < ($max = $this->plugin->getConfig()->getNested("factions.claims.max", -1)) || $max === -1) {
                        $chunk = $p->getLevel()->getChunkAtPosition($p);
                        $claim = $this->plugin->getClaimsManager()->getClaim($p->getLevel(), $chunk);
                        if ($claim === null) {
                            $this->plugin->getClaimsManager()->createClaim($faction, $p->getLevel(), $chunk);
                            return;
                        }
                        if ($this->plugin->getConfig()->getNested("factions.claim.overclaim", true) && $claim->canBeOverClaimed()) {
                            $claim->setFaction($faction);
                        }
                    }
                }
            }
        }
    }
}