<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MeResource;
use Illuminate\Http\Request;

class MeController extends Controller
{

    public function me(Request $request): MeResource
    {
        return new MeResource(resource: [
            'store'  => $request->store(),
            'device' => $request->device(),
            'token'  => $request->user()->currentAccessToken()
        ]);
    }

}
