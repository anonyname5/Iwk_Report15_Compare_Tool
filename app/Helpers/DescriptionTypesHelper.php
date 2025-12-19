<?php

namespace App\Helpers;

class DescriptionTypesHelper
{
    const BASE_TYPES = ['Connected', 'Nil', 'IST'];
    const CST_TYPE = 'CST';
    
    /**
     * Get description types based on CST inclusion
     * 
     * @param bool $includeCst Whether to include CST
     * @return array Array of description type names
     */
    public static function getTypes($includeCst = false): array
    {
        $types = self::BASE_TYPES;
        if ($includeCst) {
            $types[] = self::CST_TYPE;
        }
        return $types;
    }
    
    /**
     * Get default order for description types
     * 
     * @param bool $includeCst Whether to include CST
     * @return array Array of description type names in order
     */
    public static function getOrder($includeCst = false): array
    {
        return self::getTypes($includeCst);
    }
    
    /**
     * Check if CST is included in types
     * 
     * @param array $types Array of description types
     * @return bool True if CST is included
     */
    public static function includesCst(array $types): bool
    {
        return in_array(self::CST_TYPE, $types);
    }
    
    /**
     * Get the index of a description type in the standard order
     * 
     * @param string $type Description type name
     * @param bool $includeCst Whether CST is included
     * @return int|false Index of the type, or false if not found
     */
    public static function getTypeIndex($type, $includeCst = false)
    {
        $types = self::getTypes($includeCst);
        return array_search($type, $types);
    }
    
    /**
     * Validate if a type is a valid description type
     * 
     * @param string $type Description type name
     * @return bool True if valid
     */
    public static function isValidType($type): bool
    {
        $allTypes = array_merge(self::BASE_TYPES, [self::CST_TYPE]);
        return in_array($type, $allTypes);
    }
}

