<?php

namespace App\Casts;

use App\Models\ValueObjects\CarnetIdentidad as ValueObject;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class CarnetIdentidad implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return ValueObject
     */
    public function get($model, $key, $value, $attributes)
    {
        return new ValueObject(
            $attributes['ci'],
            $attributes['ci_complemento']
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  ValueObject  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, $key, $value, $attributes)
    {
        if (! $value instanceof ValueObject) {
            throw new InvalidArgumentException('The given value is not an CarnetIdentidad value object.');
        }

        return [
            'ci' => $value->raiz,
            'ci_complemento' => $value->complemento,
        ];
    }
}