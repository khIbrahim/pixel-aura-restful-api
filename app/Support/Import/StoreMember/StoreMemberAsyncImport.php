<?php

namespace App\Support\Import\StoreMember;

use Illuminate\Contracts\Queue\ShouldQueue;

class StoreMemberAsyncImport extends StoreMemberSyncImport implements ShouldQueue
{

}
