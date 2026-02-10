<?php

namespace App\Helpers;

class CostCentersHelper
{
    /**
     * The complete list of required cost center codes
     */
    const REQUIRED_COST_CENTERS = [
        '1A01840',
        '1A02840',
        '1A03840',
        '1B01840',
        '1B02840',
        '1B03840',
        '1B04840',
        '1B05840',
        '1C01840',
        '1D01840',
        '1J01840',
        '1J01880',
        '1J01881',
        '1J01882',
        '1J02840',
        '1K01840',
        '1K02840',
        '1K03840',
        '1L01840',
        '1M01840',
        '1N01840',
        '1P01840',
        '1P02840',
        '1R01840',
        '1T01840',
        '1W01840',
    ];

    /**
     * Get the list of required cost center codes
     * 
     * @return array
     */
    public static function getRequiredCodes(): array
    {
        return self::REQUIRED_COST_CENTERS;
    }

    /**
     * Check if a cost center code is in the required list
     * 
     * @param string $code
     * @return bool
     */
    public static function isRequired(string $code): bool
    {
        return in_array($code, self::REQUIRED_COST_CENTERS);
    }

    /**
     * Get cost center codes that are missing from a given list
     * 
     * @param array $existingCodes
     * @return array Missing cost center codes
     */
    public static function getMissing(array $existingCodes): array
    {
        return array_values(array_diff(self::REQUIRED_COST_CENTERS, $existingCodes));
    }
}
