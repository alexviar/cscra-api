<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property Point $ubicacion
 */
class Regional extends Model
{
    use SpatialTrait;

    protected $table = "regionales";    

    protected $spatialFields = [
        'ubicacion'
    ];

    const LOCAL_ID_TO_GALENO_ID = [
        1 => "AA0000000070736",
        2 => "AA0000000070737",
        3 => "AA0000000070738",
        4 => "AA0000000070739",
        5 => "AA0000000070740",
        6 => "AA0000000070741",
        7 => "AA0000000070742",
        8 => "AA0000000070743",
        9 => "AA0000000070744",
        10 => "AA0000000070745",
        11 => "AA0000000070746"
    ];

    const GALENO_ID_TO_LOCAL_ID = [
        "AA0000000070736" => 1,
        "AA0000000070737" => 2,
        "AA0000000070738" => 3,
        "AA0000000070739" => 4,
        "AA0000000070740" => 5,
        "AA0000000070741" => 6,
        "AA0000000070742" => 7,
        "AA0000000070743" => 8,
        "AA0000000070744" => 9,
        "AA0000000070745" => 10,
        "AA0000000070746" => 11
    ];

    static function mapLocalIdToGalenoId($localId)
    {
        return Arr::get(self::LOCAL_ID_TO_GALENO_ID, $localId);
    }

    static function mapGalenoIdToLocalId($galenoId)
    {
        return Arr::get(self::GALENO_ID_TO_LOCAL_ID, $galenoId);
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array["ubicacion"] = [
            "latitud" => $this->ubicacion->getLat(),
            "longitud" => $this->ubicacion->getLng()
        ];
        return $array;
    }
}
