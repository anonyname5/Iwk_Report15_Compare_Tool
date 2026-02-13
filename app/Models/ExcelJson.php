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
            
        // Financial fields to compare (same as ComparisonService)
        $financialFields = ['billing_total', 'receipts_total', 'crbal_total', 'no_accounts', 'outstanding_balance', 'current_no_accounts', 'current_balance'];
            
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
                $totalFields = 0;
                $matchingFields = 0;
                
                // Compare cost centers
                $costCenters1 = collect($file1->data['cost_centers'] ?? []);
                $costCenters2 = collect($file2->data['cost_centers'] ?? []);
                
                // Get all unique cost center codes
                $allCodes = $costCenters1->pluck('code')
                    ->merge($costCenters2->pluck('code'))
                    ->unique();
                
                foreach ($allCodes as $code) {
                    $cc1 = $costCenters1->firstWhere('code', $code);
                    $cc2 = $costCenters2->firstWhere('code', $code);
                    
                    $descriptions1 = collect($cc1['main_descriptions'] ?? []);
                    $descriptions2 = collect($cc2['main_descriptions'] ?? []);
                    
                    // Get all unique description names
                    $allDescNames = $descriptions1->pluck('name')
                        ->merge($descriptions2->pluck('name'))
                        ->unique();
                    
                    foreach ($allDescNames as $descName) {
                        $md1 = $descriptions1->firstWhere('name', $descName);
                        $md2 = $descriptions2->firstWhere('name', $descName);
                        
                        $total1 = $md1['main_total'] ?? [];
                        $total2 = $md2['main_total'] ?? [];
                        
                        // Compare basic financial fields
                        foreach ($financialFields as $field) {
                            $totalFields++;
                            $val1 = $total1[$field] ?? 0;
                            $val2 = $total2[$field] ?? 0;
                            if ($val1 == $val2) {
                                $matchingFields++;
                            }
                        }
                        
                        // Compare aging data
                        $aging1 = $total1['aging'] ?? [];
                        $aging2 = $total2['aging'] ?? [];
                        $allPeriods = array_unique(array_merge(array_keys($aging1), array_keys($aging2)));
                        
                        foreach ($allPeriods as $period) {
                            $a1 = $aging1[$period] ?? ['no_accounts' => 0, 'balance' => 0];
                            $a2 = $aging2[$period] ?? ['no_accounts' => 0, 'balance' => 0];
                            
                            // no_accounts
                            $totalFields++;
                            if (($a1['no_accounts'] ?? 0) == ($a2['no_accounts'] ?? 0)) {
                                $matchingFields++;
                            }
                            // balance
                            $totalFields++;
                            if (($a1['balance'] ?? 0) == ($a2['balance'] ?? 0)) {
                                $matchingFields++;
                            }
                        }
                    }
                }
                
                // Also compare overall_totals
                $overallTotals1 = collect($file1->data['overall_totals'] ?? []);
                $overallTotals2 = collect($file2->data['overall_totals'] ?? []);
                $allOverallNames = $overallTotals1->pluck('name')
                    ->merge($overallTotals2->pluck('name'))
                    ->unique();
                
                foreach ($allOverallNames as $otName) {
                    $ot1 = $overallTotals1->firstWhere('name', $otName);
                    $ot2 = $overallTotals2->firstWhere('name', $otName);
                    
                    $total1 = $ot1['main_total'] ?? [];
                    $total2 = $ot2['main_total'] ?? [];
                    
                    foreach ($financialFields as $field) {
                        $totalFields++;
                        if (($total1[$field] ?? 0) == ($total2[$field] ?? 0)) {
                            $matchingFields++;
                        }
                    }
                    
                    $aging1 = $total1['aging'] ?? [];
                    $aging2 = $total2['aging'] ?? [];
                    $allPeriods = array_unique(array_merge(array_keys($aging1), array_keys($aging2)));
                    
                    foreach ($allPeriods as $period) {
                        $a1 = $aging1[$period] ?? ['no_accounts' => 0, 'balance' => 0];
                        $a2 = $aging2[$period] ?? ['no_accounts' => 0, 'balance' => 0];
                        
                        $totalFields++;
                        if (($a1['no_accounts'] ?? 0) == ($a2['no_accounts'] ?? 0)) {
                            $matchingFields++;
                        }
                        $totalFields++;
                        if (($a1['balance'] ?? 0) == ($a2['balance'] ?? 0)) {
                            $matchingFields++;
                        }
                    }
                }
                
                // Calculate percentages
                $matchPercent = $totalFields > 0 ? round(($matchingFields / $totalFields) * 100, 1) : 100;
                $diffPercent = round(100 - $matchPercent, 1);
                
                $pairs->push([
                    'comparison_name' => $name,
                    'file1' => $file1,
                    'file2' => $file2,
                    'created_at' => $file1->created_at,
                    'has_changes' => $diffPercent > 0,
                    'match_percent' => $matchPercent,
                    'diff_percent' => $diffPercent,
                    'total_fields' => $totalFields,
                    'matching_fields' => $matchingFields,
                    'missing_cost_centers' => false
                ]);
            }
        }
        
        return $pairs->sortByDesc('created_at');
    }
}

