<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelJson extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'data',
        'file_type',
        'comparison_name',
        'include_cst'
    ];

    protected $casts = [
        'data' => 'array',
        'include_cst' => 'boolean',
    ];
    
    /**
     * Get the paired file for this file in a comparison
     */
    public function getPairedFile()
    {
        if (empty($this->comparison_name)) {
            return null;
        }
        
        $fileType = $this->file_type === 'file_1' ? 'file_2' : 'file_1';
        
        return self::where('comparison_name', $this->comparison_name)
            ->where('file_type', $fileType)
            ->first();
    }
    
    /**
     * Get all comparison pairs (grouped by comparison_name)
     * 
     * @return \Illuminate\Support\Collection Collection of comparison pairs
     */
    public static function getComparisonPairs()
    {
        // Get distinct comparison names
        $comparisonNames = self::whereNotNull('comparison_name')
            ->distinct()
            ->pluck('comparison_name');
            
        // For each comparison name, get file_1 and file_2
        $pairs = collect();
        
        foreach ($comparisonNames as $name) {
            $file1 = self::where('comparison_name', $name)
                ->where('file_type', 'file_1')
                ->first();
                
            $file2 = self::where('comparison_name', $name)
                ->where('file_type', 'file_2')
                ->first();
                
            if ($file1 && $file2) {
                // Check if there are differences by doing a basic comparison
                $hasChanges = false;
                $missingCostCenters = false;
                
                // Check if cost centers exist in both files
                $costCenters1 = collect($file1->data['cost_centers'] ?? []);
                $costCenters2 = collect($file2->data['cost_centers'] ?? []);
                
                $codes1 = $costCenters1->pluck('code')->toArray();
                $codes2 = $costCenters2->pluck('code')->toArray();
                
                // Check if any cost centers are missing in either file
                if (count(array_diff($codes1, $codes2)) > 0 || count(array_diff($codes2, $codes1)) > 0) {
                    $missingCostCenters = true;
                    $hasChanges = true;
                }
                
                // Check for changes in common cost centers
                if (!$hasChanges) {
                    foreach ($costCenters1 as $cc1) {
                        $cc2 = $costCenters2->firstWhere('code', $cc1['code']);
                        if ($cc2) {
                            // Just check if main_total values differ for any field
                            foreach ($cc1['main_descriptions'] ?? [] as $md1) {
                                $md2 = collect($cc2['main_descriptions'] ?? [])->firstWhere('name', $md1['name']);
                                if ($md2 && isset($md1['main_total']) && isset($md2['main_total'])) {
                                    $total1 = $md1['main_total'];
                                    $total2 = $md2['main_total'];
                                    
                                    // Check basic financial fields
                                    $fields = ['billing_total', 'receipts_total', 'outstanding_balance'];
                                    foreach ($fields as $field) {
                                        if (isset($total1[$field]) && isset($total2[$field]) && 
                                            $total1[$field] != $total2[$field]) {
                                            $hasChanges = true;
                                            break 3; // Break out of all loops once we find a change
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                $pairs->push([
                    'comparison_name' => $name,
                    'file1' => $file1,
                    'file2' => $file2,
                    'created_at' => $file1->created_at,
                    'has_changes' => $hasChanges,
                    'missing_cost_centers' => $missingCostCenters
                ]);
            }
        }
        
        return $pairs->sortByDesc('created_at');
    }
}

