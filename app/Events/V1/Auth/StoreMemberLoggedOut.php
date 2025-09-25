<?php

namespace App\Events\V1\Auth;

use App\Models\V1\Device;
use App\Models\V1\StoreMember;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreMemberLoggedOut
{
    use Dispatchable, SerializesModels;

    /**
     * Créer une nouvelle instance d'événement.
     */
    public function __construct(
        public readonly StoreMember $storeMember,
        public readonly Device $device
    ) {
    }
}
