<?php

namespace App\Services;

use App\Models\ExcelJson;
use Illuminate\Support\Facades\Log;

class ComparisonService
{
    /**
     * Compare two Excel files and return the comparison results
     * 
     * @param ExcelJson $file1 The first file
     * @param ExcelJson $file2 The second file
     * @return array The comparison results
     */
    public function compare(ExcelJson $file1, ExcelJson $file2)
    {

                // Increase memory limit for large Excel files
                ini_set('memory_limit', '512M');
                // Increase PHP execution time limit to 300 seconds (5 minutes)
                set_time_limit(300);
                
        Log::debug('Starting comparison between files: ' . $file1->file_name . ' and ' . $file2->file_name);
        
        $result = [
            'comparison_name' => $file1->comparison_name,
            'file1' => [
                'id' => $file1->id,
                'name' => $file1->file_name,
                'created_at' => $file1->created_at->format('Y-m-d H:i:s'),
            ],
            'file2' => [
                'id' => $file2->id,
                'name' => $file2->file_name,
                'created_at' => $file2->created_at->format('Y-m-d H:i:s'),
            ],
            'cost_centers' => [],
            'overall_totals' => [],
            'report_totals' => [
                'exists_in_file1' => false,
                'exists_in_file2' => false,
                'differences' => [],
                'has_differences' => false,
                'file1_data' => [],
                'file2_data' => []
            ],
            'age_balance' => [
                'exists_in_file1' => false,
                'exists_in_file2' => false,
                'differences' => [],
                'has_differences' => false,
                'file1_data' => [],
                'file2_data' => []
            ],
            'summary' => [
                'total_cost_centers' => 0,
                'matched_cost_centers' => 0,
                'only_in_file1' => 0,
                'only_in_file2' => 0,
                'with_differences' => 0,
                'overall_with_differences' => 0
            ]
        ];
        
        Log::info('Comparing cost centers between files');
        
        $costCenters1 = collect($file1->data['cost_centers'] ?? []);
        $costCenters2 = collect($file2->data['cost_centers'] ?? []);
        
        // Get all unique cost center codes
        $allCodes = $costCenters1->pluck('code')
            ->merge($costCenters2->pluck('code'))
            ->unique()
            ->values();
            
        $result['summary']['total_cost_centers'] = count($allCodes);
        
        // Determine if CST is included
        $includeCst = false;
        foreach ($costCenters1 as $cc) {
            foreach ($cc['main_descriptions'] ?? [] as $md) {
                foreach ($md['description_types'] ?? [] as $dt) {
                    if ($dt['type'] === 'CST') {
                        $includeCst = true;
                        break 3;
                    }
                }
            }
        }
        if (!$includeCst) {
            foreach ($costCenters2 as $cc) {
                foreach ($cc['main_descriptions'] ?? [] as $md) {
                    foreach ($md['description_types'] ?? [] as $dt) {
                        if ($dt['type'] === 'CST') {
                            $includeCst = true;
                            break 3;
                        }
                    }
                }
            }
        }
        
        foreach ($allCodes as $code) {
            $cc1 = $costCenters1->firstWhere('code', $code);
            $cc2 = $costCenters2->firstWhere('code', $code);
            
            // Update summary counters
            if ($cc1 && $cc2) {
                $result['summary']['matched_cost_centers']++;
            } elseif ($cc1) {
                $result['summary']['only_in_file1']++;
            } elseif ($cc2) {
                $result['summary']['only_in_file2']++;
            }
            
            // If a cost center is missing from one file, create a zero-value version
            // using the other file's structure as a template
            if ($cc1 && !$cc2) {
                Log::info('Cost center ' . $code . ' missing in file 2 - creating zero-value entry');
                $cc2 = $this->createZeroCostCenter($code, $cc1, $includeCst);
            } elseif (!$cc1 && $cc2) {
                Log::info('Cost center ' . $code . ' missing in file 1 - creating zero-value entry');
                $cc1 = $this->createZeroCostCenter($code, $cc2, $includeCst);
            }
            
            $comparison = [
                'code' => $code,
                'exists_in_file1' => true,
                'exists_in_file2' => true,
                'main_descriptions' => []
            ];
            
            // Compare main descriptions (both now always exist)
            {
                // Calculate cost center totals for both files
                $costCenterTotal1 = [
                    'billing_total' => 0,
                    'receipts_total' => 0,
                    'crbal_total' => 0,
                    'no_accounts' => 0,
                    'outstanding_balance' => 0,
                    'current_no_accounts' => 0,
                    'current_balance' => 0,
                    'aging' => []
                ];
                
                $costCenterTotal2 = [
                    'billing_total' => 0,
                    'receipts_total' => 0,
                    'crbal_total' => 0,
                    'no_accounts' => 0,
                    'outstanding_balance' => 0,
                    'current_no_accounts' => 0,
                    'current_balance' => 0,
                    'aging' => []
                ];
                
                $mainDescs1 = collect($cc1['main_descriptions'] ?? []);
                $mainDescs2 = collect($cc2['main_descriptions'] ?? []);
                
                // Build a canonical mapping to handle any remaining name variations
                // This is a safety net in case the parser didn't fully standardize names
                $canonicalNames = [];
                $nameMapping = []; // maps raw name → canonical name
                
                foreach ($mainDescs1->pluck('name') as $name) {
                    $norm = $this->normalizeDescForMatching($name);
                    if (!isset($canonicalNames[$norm])) {
                        $canonicalNames[$norm] = $name;
                    }
                    $nameMapping[$name] = $canonicalNames[$norm];
                }
                foreach ($mainDescs2->pluck('name') as $name) {
                    $norm = $this->normalizeDescForMatching($name);
                    if (!isset($canonicalNames[$norm])) {
                        $canonicalNames[$norm] = $name;
                    }
                    $nameMapping[$name] = $canonicalNames[$norm];
                }
                
                // Get all unique CANONICAL main description names
                $allMainDescNames = collect(array_values($canonicalNames))->unique()->values();
                
                foreach ($allMainDescNames as $mainDescName) {
                    // Find matching descriptions using canonical mapping
                    $md1 = $mainDescs1->first(function ($md) use ($mainDescName, $nameMapping) {
                        return ($nameMapping[$md['name']] ?? $md['name']) === $mainDescName;
                    });
                    $md2 = $mainDescs2->first(function ($md) use ($mainDescName, $nameMapping) {
                        return ($nameMapping[$md['name']] ?? $md['name']) === $mainDescName;
                    });
                    
                    $mainDescComparison = [
                        'name' => $mainDescName,
                        'exists_in_file1' => !is_null($md1),
                        'exists_in_file2' => !is_null($md2),
                        'differences' => [],
                        'has_differences' => false
                    ];
                    
                    // Compare totals if both exist
                    if ($md1 && $md2) {
                        $total1 = $md1['main_total'] ?? [];
                        $total2 = $md2['main_total'] ?? [];
                        
                        // Accumulate cost center totals from file 1
                        if (!empty($total1)) {
                            $costCenterTotal1['billing_total'] += $total1['billing_total'] ?? 0;
                            $costCenterTotal1['receipts_total'] += $total1['receipts_total'] ?? 0;
                            $costCenterTotal1['crbal_total'] += $total1['crbal_total'] ?? 0;
                            $costCenterTotal1['no_accounts'] += $total1['no_accounts'] ?? 0;
                            $costCenterTotal1['outstanding_balance'] += $total1['outstanding_balance'] ?? 0;
                            $costCenterTotal1['current_no_accounts'] += $total1['current_no_accounts'] ?? 0;
                            $costCenterTotal1['current_balance'] += $total1['current_balance'] ?? 0;
                            
                            // Accumulate aging data for file 1
                            if (isset($total1['aging'])) {
                                foreach ($total1['aging'] as $period => $aging) {
                                    if (!isset($costCenterTotal1['aging'][$period])) {
                                        $costCenterTotal1['aging'][$period] = [
                                            'no_accounts' => 0,
                                            'balance' => 0
                                        ];
                                    }
                                    $costCenterTotal1['aging'][$period]['no_accounts'] += $aging['no_accounts'] ?? 0;
                                    $costCenterTotal1['aging'][$period]['balance'] += $aging['balance'] ?? 0;
                                }
                            }
                        }
                        
                        // Accumulate cost center totals from file 2
                        if (!empty($total2)) {
                            $costCenterTotal2['billing_total'] += $total2['billing_total'] ?? 0;
                            $costCenterTotal2['receipts_total'] += $total2['receipts_total'] ?? 0;
                            $costCenterTotal2['crbal_total'] += $total2['crbal_total'] ?? 0;
                            $costCenterTotal2['no_accounts'] += $total2['no_accounts'] ?? 0;
                            $costCenterTotal2['outstanding_balance'] += $total2['outstanding_balance'] ?? 0;
                            $costCenterTotal2['current_no_accounts'] += $total2['current_no_accounts'] ?? 0;
                            $costCenterTotal2['current_balance'] += $total2['current_balance'] ?? 0;
                            
                            // Accumulate aging data for file 2
                            if (isset($total2['aging'])) {
                                foreach ($total2['aging'] as $period => $aging) {
                                    if (!isset($costCenterTotal2['aging'][$period])) {
                                        $costCenterTotal2['aging'][$period] = [
                                            'no_accounts' => 0,
                                            'balance' => 0
                                        ];
                                    }
                                    $costCenterTotal2['aging'][$period]['no_accounts'] += $aging['no_accounts'] ?? 0;
                                    $costCenterTotal2['aging'][$period]['balance'] += $aging['balance'] ?? 0;
                                }
                            }
                        }
                        
                        // Compare financial data
                        $financialDifferences = $this->compareFinancialData($total1, $total2);
                        if (!empty($financialDifferences)) {
                            $mainDescComparison['differences'] = $financialDifferences;
                            $mainDescComparison['has_differences'] = true;
                            $result['summary']['with_differences']++;
                        }
                        
                        // Include the original values for reference
                        $mainDescComparison['file1_data'] = $total1;
                        $mainDescComparison['file2_data'] = $total2;
                        
                        // Compare description types (Connected, Nil, IST, CST if included)
                        if (isset($md1['description_types']) && isset($md2['description_types'])) {
                            $descTypes1 = collect($md1['description_types']);
                            $descTypes2 = collect($md2['description_types']);
                            
                            // Get all unique description types
                            $allDescTypes = $descTypes1->pluck('type')
                                ->merge($descTypes2->pluck('type'))
                                ->unique()
                                ->values();
                            
                            $descTypesDifferences = [];
                            
                            foreach ($allDescTypes as $descType) {
                                $dt1 = $descTypes1->firstWhere('type', $descType);
                                $dt2 = $descTypes2->firstWhere('type', $descType);
                                
                                // Only compare if both description types exist
                                if ($dt1 && $dt2) {
                                    $dtData1 = $dt1['data'] ?? [];
                                    $dtData2 = $dt2['data'] ?? [];
                                    
                                    $dtDifferences = $this->compareFinancialData(
                                        $dtData1, 
                                        $dtData2
                                    );
                                    
                                    if (!empty($dtDifferences)) {
                                        $descTypesDifferences[$descType] = $dtDifferences;
                                        $mainDescComparison['has_differences'] = true;
                                    }
                                }
                            }
                            
                            if (!empty($descTypesDifferences)) {
                                $mainDescComparison['description_types_differences'] = $descTypesDifferences;
                            }
                        }
                    }
                    
                    $comparison['main_descriptions'][] = $mainDescComparison;
                }
                
                // Compare cost center totals and add to comparison
                $costCenterTotalDiffs = $this->compareFinancialData($costCenterTotal1, $costCenterTotal2);
                
                // Check if there are differences in cost center totals
                $hasCostCenterTotalDiffs = false;
                foreach ($costCenterTotalDiffs as $diff) {
                    if (!isset($diff['is_zero_diff']) || !$diff['is_zero_diff']) {
                        $hasCostCenterTotalDiffs = true;
                        break;
                    }
                }
                
                if ($hasCostCenterTotalDiffs) {
                    Log::info('Cost center ' . $code . ' has total differences:');
                    foreach ($costCenterTotalDiffs as $diff) {
                        if (!isset($diff['is_zero_diff']) || !$diff['is_zero_diff']) {
                            Log::info('  ' . $diff['display_name'] . ': ' . 
                                     number_format($diff['file1_value'], 2) . ' → ' . 
                                     number_format($diff['file2_value'], 2) . ' (' . 
                                     ($diff['difference'] >= 0 ? '+' : '') . 
                                     number_format($diff['difference'], 2) . ')');
                        }
                    }
                }
                
                $comparison['cost_center_total'] = [
                    'differences' => $costCenterTotalDiffs,
                    'has_differences' => $hasCostCenterTotalDiffs,
                    'file1_data' => $costCenterTotal1,
                    'file2_data' => $costCenterTotal2
                ];
            }
            
            $result['cost_centers'][] = $comparison;
        }
        
        // Compare overall totals
        Log::info('Comparing overall totals between files');
        
        $overallTotals1 = collect($file1->data['overall_totals'] ?? []);
        $overallTotals2 = collect($file2->data['overall_totals'] ?? []);
        
        // Build canonical mapping for overall totals (same fuzzy approach)
        $overallCanonicalNames = [];
        $overallNameMapping = [];
        
        foreach ($overallTotals1->pluck('name') as $name) {
            $norm = $this->normalizeDescForMatching($name);
            if (!isset($overallCanonicalNames[$norm])) {
                $overallCanonicalNames[$norm] = $name;
            }
            $overallNameMapping[$name] = $overallCanonicalNames[$norm];
        }
        foreach ($overallTotals2->pluck('name') as $name) {
            $norm = $this->normalizeDescForMatching($name);
            if (!isset($overallCanonicalNames[$norm])) {
                $overallCanonicalNames[$norm] = $name;
            }
            $overallNameMapping[$name] = $overallCanonicalNames[$norm];
        }
        
        // Get all unique CANONICAL overall main description names
        $allOverallMainDescNames = collect(array_values($overallCanonicalNames))->unique()->values();
            
        foreach ($allOverallMainDescNames as $mainDescName) {
            $md1 = $overallTotals1->first(function ($md) use ($mainDescName, $overallNameMapping) {
                return ($overallNameMapping[$md['name']] ?? $md['name']) === $mainDescName;
            });
            $md2 = $overallTotals2->first(function ($md) use ($mainDescName, $overallNameMapping) {
                return ($overallNameMapping[$md['name']] ?? $md['name']) === $mainDescName;
            });
            
            $mainDescComparison = [
                'name' => $mainDescName,
                'exists_in_file1' => !is_null($md1),
                'exists_in_file2' => !is_null($md2),
                'differences' => [],
                'has_differences' => false
            ];
            
            // Compare totals if both exist
            if ($md1 && $md2) {
                $total1 = $md1['main_total'] ?? [];
                $total2 = $md2['main_total'] ?? [];
                
                // Compare financial data
                $financialDifferences = $this->compareFinancialData($total1, $total2);
                
                if (!empty($financialDifferences)) {
                    $mainDescComparison['differences'] = $financialDifferences;
                    $mainDescComparison['has_differences'] = true;
                    $result['summary']['overall_with_differences']++;
                }
                
                // Include the original values for reference
                $mainDescComparison['file1_data'] = $total1;
                $mainDescComparison['file2_data'] = $total2;
                
                // Compare description types (Connected, Nil, IST, CST if included)
                if (isset($md1['description_types']) && isset($md2['description_types'])) {
                    $descTypes1 = collect($md1['description_types']);
                    $descTypes2 = collect($md2['description_types']);
                    
                    // Get all unique description types
                    $allDescTypes = $descTypes1->pluck('type')
                        ->merge($descTypes2->pluck('type'))
                        ->unique()
                        ->values();
                    
                    $descTypesDifferences = [];
                    
                    foreach ($allDescTypes as $descType) {
                        $dt1 = $descTypes1->firstWhere('type', $descType);
                        $dt2 = $descTypes2->firstWhere('type', $descType);
                        
                        // Only compare if both description types exist
                        if ($dt1 && $dt2) {
                            $dtData1 = $dt1['data'] ?? [];
                            $dtData2 = $dt2['data'] ?? [];
                            
                            $dtDifferences = $this->compareFinancialData(
                                $dtData1, 
                                $dtData2
                            );
                            
                            if (!empty($dtDifferences)) {
                                $descTypesDifferences[$descType] = $dtDifferences;
                                $mainDescComparison['has_differences'] = true;
                            }
                        }
                    }
                    
                    if (!empty($descTypesDifferences)) {
                        $mainDescComparison['description_types_differences'] = $descTypesDifferences;
                    }
                }
            }
            
            $result['overall_totals'][] = $mainDescComparison;
        }
        
        // Compare Cost Center Report Totals
        Log::info('Comparing Cost Center Report Totals');
        $reportTotals1 = $file1->data['report_totals'] ?? null;
        $reportTotals2 = $file2->data['report_totals'] ?? null;
        
        $result['report_totals']['exists_in_file1'] = !is_null($reportTotals1);
        $result['report_totals']['exists_in_file2'] = !is_null($reportTotals2);
        
        if ($reportTotals1 && $reportTotals2) {
            $financialDifferences = $this->compareFinancialData(
                $reportTotals1, 
                $reportTotals2
            );
            
            if (!empty($financialDifferences)) {
                $result['report_totals']['differences'] = $financialDifferences;
                $result['report_totals']['has_differences'] = true;
                $result['summary']['overall_with_differences']++;
            }
            
            $result['report_totals']['file1_data'] = $reportTotals1;
            $result['report_totals']['file2_data'] = $reportTotals2;
        }
        
        // Compare Age Balance %
        Log::info('Comparing Age Balance %');
        $ageBalance1 = $file1->data['age_balance'] ?? null;
        $ageBalance2 = $file2->data['age_balance'] ?? null;
        
        $result['age_balance']['exists_in_file1'] = !is_null($ageBalance1);
        $result['age_balance']['exists_in_file2'] = !is_null($ageBalance2);
        
        if ($ageBalance1 && $ageBalance2) {
            $financialDifferences = $this->compareFinancialData(
                $ageBalance1, 
                $ageBalance2
            );
            
            if (!empty($financialDifferences)) {
                $result['age_balance']['differences'] = $financialDifferences;
                $result['age_balance']['has_differences'] = true;
                $result['summary']['overall_with_differences']++;
            }
            
            $result['age_balance']['file1_data'] = $ageBalance1;
            $result['age_balance']['file2_data'] = $ageBalance2;
        }
        
        // Remove detailed change summary logging
        
        Log::info('Comparison completed: ' . 
                 $result['summary']['matched_cost_centers'] . ' matched cost centers, ' .
                 $result['summary']['with_differences'] . ' cost centers with differences, ' .
                 count($result['overall_totals']) . ' overall totals compared, ' .
                 $result['summary']['overall_with_differences'] . ' overall sections with differences');
        
        return $result;
    }
    
    /**
     * Create a zero-value financial data structure
     * 
     * @return array Financial data with all zeros
     */
    private function createZeroFinancialData()
    {
        return [
            'billing_total' => 0,
            'receipts_total' => 0,
            'crbal_total' => 0,
            'no_accounts' => 0,
            'outstanding_balance' => 0,
            'current_no_accounts' => 0,
            'current_balance' => 0,
            'aging' => [
                'Overdue > 1 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 2 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 3 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 6 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 12 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 18 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 24 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 30 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 36 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 42 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 48 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 54 month' => ['no_accounts' => 0, 'balance' => 0],
                'Overdue > 60 month' => ['no_accounts' => 0, 'balance' => 0],
            ]
        ];
    }

    /**
     * Create a zero-value cost center structure based on an existing cost center's shape
     * 
     * @param string $code The cost center code
     * @param array $referenceCc The existing cost center to use as template for structure
     * @param bool $includeCst Whether CST is included
     * @return array A cost center with the same main descriptions but all zero values
     */
    private function createZeroCostCenter($code, $referenceCc, $includeCst = false)
    {
        $zeroFinancial = $this->createZeroFinancialData();
        $descriptionTypes = \App\Helpers\DescriptionTypesHelper::getTypes($includeCst);
        
        $mainDescriptions = [];
        foreach ($referenceCc['main_descriptions'] ?? [] as $md) {
            $zeroDescTypes = [];
            foreach ($descriptionTypes as $type) {
                $zeroDescTypes[] = [
                    'type' => $type,
                    'data' => $zeroFinancial
                ];
            }
            
            $mainDescriptions[] = [
                'name' => $md['name'],
                'description_types' => $zeroDescTypes,
                'main_total' => $zeroFinancial
            ];
        }
        
        return [
            'code' => $code,
            'main_descriptions' => $mainDescriptions,
            'cost_center_total' => []
        ];
    }

    /**
     * Compare financial data between two arrays
     * 
     * @param array $data1 Financial data from file 1
     * @param array $data2 Financial data from file 2
     * @return array List of differences
     */
    private function compareFinancialData($data1, $data2)
    {
        $differences = [];
        
        // List of fields to compare
        $fieldsToCompare = [
            'billing_total' => 'Billing Total',
            'receipts_total' => 'Receipts Total',
            'crbal_total' => 'CR Bal Total',
            'no_accounts' => 'Number of Accounts',
            'outstanding_balance' => 'Outstanding Balance',
            'current_no_accounts' => 'Current No. Accounts',
            'current_balance' => 'Current Balance'
        ];
        
        foreach ($fieldsToCompare as $field => $displayName) {
            $value1 = $data1[$field] ?? 0;
            $value2 = $data2[$field] ?? 0;
            
            // Include all fields, even when values match (difference is zero)
            $differences[] = [
                'field' => $field,
                'display_name' => $displayName,
                'file1_value' => $value1,
                'file2_value' => $value2,
                'difference' => $value1 - $value2,
                'percentage_change' => $value2 != 0 ? (($value1 - $value2) / abs($value2)) * 100 : null,
                'is_zero_diff' => $value1 == $value2  // Flag to identify zero differences
            ];
        }
        
        // Compare aging data
        if (isset($data1['aging']) && isset($data2['aging'])) {
            foreach ($data1['aging'] as $period => $aging1) {
                $aging2 = $data2['aging'][$period] ?? ['no_accounts' => 0, 'balance' => 0];
                
                // Compare number of accounts - include all, even matching values
                $differences[] = [
                    'field' => "aging.{$period}.no_accounts",
                    'display_name' => "{$period} - Accounts",
                    'file1_value' => $aging1['no_accounts'],
                    'file2_value' => $aging2['no_accounts'],
                    'difference' => $aging1['no_accounts'] - $aging2['no_accounts'],
                    'percentage_change' => $aging2['no_accounts'] != 0 ? 
                        (($aging1['no_accounts'] - $aging2['no_accounts']) / abs($aging2['no_accounts'])) * 100 : null,
                    'is_zero_diff' => $aging1['no_accounts'] == $aging2['no_accounts']
                ];
                
                // Compare balance - include all, even matching values
                $differences[] = [
                    'field' => "aging.{$period}.balance",
                    'display_name' => "{$period} - Balance",
                    'file1_value' => $aging1['balance'],
                    'file2_value' => $aging2['balance'], 
                    'difference' => $aging1['balance'] - $aging2['balance'],
                    'percentage_change' => $aging2['balance'] != 0 ? 
                        (($aging1['balance'] - $aging2['balance']) / abs($aging2['balance'])) * 100 : null,
                    'is_zero_diff' => $aging1['balance'] == $aging2['balance']
                ];
            }
        }
        
        return $differences;
    }

    /**
     * Normalize a main description name for fuzzy matching.
     * Removes periods, hyphens, extra spaces, and converts to lowercase.
     * This is a safety net for matching names that weren't fully standardized by the parser.
     *
     * @param string $name The main description name
     * @return string Normalized name for matching
     */
    private function normalizeDescForMatching(string $name): string
    {
        $normalized = trim($name);
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace('-', ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return strtolower($normalized);
    }
}