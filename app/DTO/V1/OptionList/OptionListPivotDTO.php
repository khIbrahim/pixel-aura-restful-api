<?php

namespace App\DTO\V1\OptionList;

use App\DTO\V1\Abstract\AbstractPivotDTO;

readonly class OptionListPivotDTO extends AbstractPivotDTO
{

    public function __construct(
        public int   $option_list_id,
        public int   $store_id,
        public ?bool $is_required = null,
        public ?int  $min_selections = null,
        public ?int  $max_selections = null,
        public ?int  $display_order = null,
        public ?bool $is_active = null,
    ){}

    public function getPivotKey(): int|string
    {
        return $this->option_list_id;
    }

    public function getPivotData(): array
    {
        return [
            'option_list_id' => $this->option_list_id,
            'is_required'    => $this->is_required,
            'store_id'       => $this->store_id,
            'min_selections' => $this->min_selections,
            'max_selections' => $this->max_selections,
            'display_order'  => $this->display_order,
            'is_active'      => $this->is_active,
        ];
    }
}
