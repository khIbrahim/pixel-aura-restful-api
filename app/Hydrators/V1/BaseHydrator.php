<?php

namespace App\Hydrators\V1;

use App\Models\V1\Store;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseHydrator
{

    public function fromArray(array $data, string $dtoClass): object
    {
        return $this->createInstance($dtoClass, $data);
    }
    public function fromRequest(FormRequest $request, string $dtoClass): object
    {
        $data = $request->validated();

        if ($request->attributes->get('store') instanceof Store) {
            $data['store_id'] = $request->attributes->get('store')->id;
        }

        return $this->createInstance($dtoClass, $data);
    }

    private function createInstance(string $dtoClass, array $data): object
    {
        $reflection = new \ReflectionClass($dtoClass);
        $parameters = $reflection->getConstructor()->getParameters() ?? [];

        $args = [];
        foreach ($parameters as $param){
            $name = $param->getName();
            $args[$name] = array_key_exists($name, $data) ? $data[$name] : ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
        }

        return $reflection->newInstanceArgs($args);
    }

}
