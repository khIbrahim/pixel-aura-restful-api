<?php

namespace App\DTO\V1\StoreMember;

final readonly class AuthenticateStoreMemberDTO implements \JsonSerializable
{

    public function __construct(
        public string $code,
        public string $pin,
    ){}

    public static function fromRequest(array $data): self
    {
        return new self(
            code: (string) $data['code'],
            pin: (string) $data['pin'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'code'     => $this->code,
            'pin'      => $this->pin,
        ];
    }
}
