<?php

namespace App\Hydrators\V1\Store;

use App\DTO\V1\Store\CreateStoreDTO;
use App\Http\Requests\V1\StoreMember\CreateStoreRequest;
use App\Hydrators\V1\BaseHydrator;
use App\Services\V1\Item\SkuGeneratorService;
use DateTimeZone;
use Locale;

class StoreHydrator extends BaseHydrator
{

    public function __construct(
        private readonly SkuGeneratorService $skuGeneratorService
    ){}

    /** @throws */
    public function fromCreateRequest(CreateStoreRequest $request): CreateStoreDTO
    {
        $data = $request->validated();

        $preferred = $this->getPreferredLanguage();
        $locale    = str_replace('-', '_', $preferred ?: '');
        $language  = $data['language'] ?? (Locale::getPrimaryLanguage($locale) ?: 'fr');
        $country   = $data['country'] ?? strtoupper(Locale::getRegion($locale) ?: '');

        $timezone = $data['timezone'] ?? null;
        if (! $timezone && $country) {
            $tzs = DateTimeZone::listIdentifiers(timezoneGroup: DateTimeZone::PER_COUNTRY, countryCode: $country);
            $timezone = $tzs[0] ?? null;
        }

        $timezone = $timezone ?: 'Africa/Algiers';
        $currency = $data['currency'] ?? 'DZD';

        $data['country']  = $country;
        $data['currency'] = $currency;
        $data['timezone'] = $timezone;
        $data['language'] = $language;

        $name = (string) $data['name'];
        $sku  = $this->skuGeneratorService->generateSku($name, -1);

        $data['sku'] = $sku;

        return $this->fromRequest($request, CreateStoreDTO::class);
    }

}
