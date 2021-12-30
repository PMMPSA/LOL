<?php
namespace xBeastMode\Weapons;
interface GunData{
        const GUN_LIST = [
            "mg42",
            "mp40",
            "minigun",
            "thompson",
            "m1911",
			"panzerfaust",
			"183mml4",
			"nukegun"
        ];

        const FIRE_RATES = [
            "mg42" => 5,
            "mp40" => 10,
            "minigun" => 5,
            "thompson" => 10,
        ];

        const SHOT_PITCH = [
            "mg42" => 0.4,
            "mp40" => 0.6,
            "minigun" => 0.8,
            "thompson" => 0.6,
            "m1911" => 0.6,
			"panzerfaust" => 0.8,
			"183mml4" => 0.4,
			"nukegun" => 0.4        
		];

        const DAMAGES = [
            "mg42" => 100,
            "mp40" => 100,
            "minigun" => 300,
            "thompson" => 200,
            "m1911" => 100,
			"panzerfaust" => 1000,
			"183mml4" => 999999,
			"nukegun" => 99999999
        ];

        const FULL_AUTO = [
            "mg42",
            "mp40",
            "minigun",
            "thompson"
        ];

        const EXPLODE = [
		    "panzerfaust" => 1,
			"183mml4" => 10,
			"nukegun" => 30
        ];
}