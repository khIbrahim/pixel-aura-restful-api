<?php

namespace App\Exceptions\V1\StoreMember;

use App\Exceptions\V1\BaseApiException;

class StoreMemberCodeAlreadyExistsException extends BaseApiException
{

    protected $message          = "Le code du membre du magasin existe déjà.";
    protected int $statusCode   = 409;
    protected string $errorType = "STORE_MEMBER_CODE_ALREADY_EXISTS";

}
