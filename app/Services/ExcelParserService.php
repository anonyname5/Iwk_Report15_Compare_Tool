<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelParserService
{
    public function parse($path)
    {
        Log::info('Starting Excel file parsing process', ['file_path' => $path]);
        
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        
        Log::info('Excel file loaded successfully', ['total_rows' => count($rows)]);

        $reportTitle = $rows[1]['A'] ?? 'Untitled Report';
        $generatedAt = now()->toIso8601String();
        
        Log::info('Report metadata extracted', [
            'report_title' => $reportTitle,
            'generated_at' => $generatedAt
        ]);

        $data = [
            'report_title' => $reportTitle,
            'generated_at' => $generatedAt,
            'cost_centers' => [],
            'overall_totals' => [],
            'report_totals' => null,
            'age_balance' => null
        ];

        $currentCostCenter = null;
        $mainDescBuffer = [];
        $overallTotals = [];
        $mainDescNames = [
            'Commercial Totals',
            'Domestic Totals',
            'Non-billable Totals',
            'Govt.Domestic Totals',
            'Govt. Premises Totals',
            'Govt. Quarters Totals',
            'Industrial Totals',
            'Ind. No HC Totals',
        ];

        // Add normalized versions of the names for matching
        $normalizedMainDescNames = array_map(function($name) {
            // Normalize Govt.Domestic Totals to handle both dot and space variations
            if ($name === 'Govt.Domestic Totals') {
                return ['Govt.Domestic Totals', 'Govt. Domestic Totals'];
            }
            // Normalize Govt. Premises Totals to handle single and double space variations
            if ($name === 'Govt. Premises Totals') {
                return ['Govt. Premises Totals', 'Govt.  Premises Totals'];
            }
            return [$name];
        }, $mainDescNames);

        Log::info('Started parsing Excel file');

        for ($i = 7; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? null;
            if (!$row || empty($row['A'])) {
                Log::debug('Skipping empty row', ['row_number' => $i]);
                continue;
            }

            $descText = trim($row['A']);
            $costCenter = trim($row['B'] ?? '');
            
            Log::debug('Processing row', [
                'row_number' => $i,
                'description' => $descText,
                'cost_center' => $costCenter
            ]);

            // Check for Cost Center Report Totals
            if (empty($costCenter) && (
                $descText === 'Cost Center Report Totals' || 
                $descText === 'Cost Centre Report Totals' ||
                stripos($descText, 'Cost Center') !== false && stripos($descText, 'Report') !== false && stripos($descText, 'Total') !== false ||
                stripos($descText, 'Cost Centre') !== false && stripos($descText, 'Report') !== false && stripos($descText, 'Total') !== false
            )) {
                Log::info('Found Cost Center Report Totals row: ' . $descText);
                $data['report_totals'] = $this->parseFinancialData($row);
                continue;
            }

            // Check for Age Balance %
            if (empty($costCenter) && (
                $descText === 'Age Balance %' || 
                stripos($descText, 'Age') !== false && stripos($descText, 'Balance') !== false && stripos($descText, '%') !== false
            )) {
                Log::info('Found Age Balance % row: ' . $descText);
                $data['age_balance'] = $this->parseFinancialData($row);
                continue;
            }

            // Check for Overall main description totals
            if (empty($costCenter)) {
                foreach ($normalizedMainDescNames as $mainDescVariations) {
                    foreach ($mainDescVariations as $mainDesc) {
                        if (stripos($descText, $mainDesc) !== false) {
                            Log::info('Found overall main description', [
                                'description' => $descText,
                                'matched_pattern' => $mainDesc,
                                'row_number' => $i
                            ]);
                            
                            // Use the standard version for storage
                            $standardMainDesc = $mainDescNames[array_search($mainDescVariations[0], $mainDescNames)];
                            
                            // Try to find subrows (Connected, Nil, IST)
                            $subTypes = ['Connected', 'Nil', 'IST'];
                            $subRows = [];
                            
                            // Look back up to 10 rows to find sub-rows
                            for ($j = 1; $j <= 10 && ($i-$j) >= 7; $j++) {
                                $subRow = $rows[$i-$j] ?? null;
                                if (!$subRow || empty($subRow['A']) || !empty($subRow['B'])) {
                                    Log::debug('Skipping potential sub-row', [
                                        'row_number' => $i-$j,
                                        'reason' => !$subRow ? 'null_row' : (empty($subRow['A']) ? 'empty_description' : 'has_cost_center')
                                    ]);
                                    continue;
                                }
                                
                                $subDesc = trim($subRow['A']);
                                foreach ($subTypes as $subType) {
                                    if ($subDesc === $subType || stripos($subDesc, $subType) === 0) {
                                        Log::info('Found overall sub-row', [
                                            'main_description' => $standardMainDesc,
                                            'sub_type' => $subType,
                                            'row_number' => $i-$j,
                                            'financial_data' => $this->parseFinancialData($subRow)
                                        ]);
                                        $subRows[] = [
                                            'type' => $subType,
                                            'data' => $this->parseFinancialData($subRow)
                                        ];
                                        break;
                                    }
                                }
                            }
                            
                            $mainTotalData = $this->parseFinancialData($row);
                            Log::info('Adding overall total with sub-rows', [
                                'main_description' => $standardMainDesc,
                                'sub_rows_count' => count($subRows),
                                'sub_types_found' => array_map(function($subRow) { return $subRow['type']; }, $subRows),
                                'main_total_data' => $mainTotalData
                            ]);
                            
                            $overallTotals[] = [
                                'name' => $standardMainDesc,
                                'description_types' => $subRows,
                                'main_total' => $mainTotalData
                            ];
                            
                            continue 2; // Continue with the outer loop
                        }
                    }
                }
            }

            // Detect Main Total row with cost center: e.g., "CC1A01840 Commercial Totals"
            if (preg_match('/^CC(\w{7})\s+(.+ Totals)$/', $descText, $matches)) {
                $code = $matches[1];
                $mainDesc = $matches[2];

                if ($currentCostCenter !== $code) {
                    if ($currentCostCenter && !empty($mainDescBuffer)) {
                        Log::info('Saving cost center data', [
                            'cost_center_code' => $currentCostCenter,
                            'main_descriptions_count' => count($mainDescBuffer)
                        ]);
                        $data['cost_centers'][] = [
                            'code' => $currentCostCenter,
                            'main_descriptions' => $mainDescBuffer,
                            'cost_center_total' => []
                        ];
                    }
                    $mainDescBuffer = [];
                    $currentCostCenter = $code;
                    Log::info('Starting new cost center', ['cost_center_code' => $code]);
                }

                // Find previous 3 rows: IST, Nil, Connected
                $ist = $rows[$i - 1] ?? null;
                $nil = $rows[$i - 2] ?? null;
                $connected = $rows[$i - 3] ?? null;

                $descriptionTypes = [];
                if ($connected) {
                    $descriptionTypes[] = [
                        'type' => 'Connected',
                        'data' => $this->parseFinancialData($connected)
                    ];
                }
                if ($nil) {
                    $descriptionTypes[] = [
                        'type' => 'Nil',
                        'data' => $this->parseFinancialData($nil)
                    ];
                }
                if ($ist) {
                    $descriptionTypes[] = [
                        'type' => 'IST',
                        'data' => $this->parseFinancialData($ist)
                    ];
                }

                $mainDescBuffer[] = [
                    'name' => $mainDesc,
                    'description_types' => $descriptionTypes,
                    'main_total' => $this->parseFinancialData($row)
                ];
            }
        }

        // Save the final cost center buffer
        if ($currentCostCenter && !empty($mainDescBuffer)) {
            Log::info('Saving final cost center data', [
                'cost_center_code' => $currentCostCenter,
                'main_descriptions_count' => count($mainDescBuffer)
            ]);
            $data['cost_centers'][] = [
                'code' => $currentCostCenter,
                'main_descriptions' => $mainDescBuffer,
                'cost_center_total' => []
            ];
        }

        // Add overall totals to the data structure
        if (!empty($overallTotals)) {
            $data['overall_totals'] = $overallTotals;
            Log::info('Added overall totals', [
                'total_count' => count($overallTotals),
                'total_types' => array_map(function($total) { 
                    return [
                        'name' => $total['name'],
                        'sub_rows_count' => count($total['description_types']),
                        'sub_types' => array_map(function($subRow) { return $subRow['type']; }, $total['description_types'])
                    ];
                }, $overallTotals)
            ]);
        }

        Log::info('Excel parsing completed', [
            'total_cost_centers' => count($data['cost_centers']),
            'total_overall_totals' => count($data['overall_totals']),
            'has_report_totals' => !is_null($data['report_totals']),
            'has_age_balance' => !is_null($data['age_balance'])
        ]);

        return $data;
    }

    private function parseFinancialData($row)
    {
        Log::debug('Parsing financial data', [
            'row_data' => array_map(function($value) {
                return is_numeric($value) ? (float)str_replace(',', '', $value) : $value;
            }, $row)
        ]);

        return [
            'billing_total' => (float) (str_replace(',', '', $row['C'] ?? 0)),
            'receipts_total' => (float) (str_replace(',', '', $row['D'] ?? 0)),
            'crbal_total' => (float) (str_replace(',', '', $row['E'] ?? 0)),
            'no_accounts' => (int) (str_replace(',', '', $row['F'] ?? 0)),
            'outstanding_balance' => (float) (str_replace(',', '', $row['G'] ?? 0)),
            'current_no_accounts' => (int) (str_replace(',', '', $row['H'] ?? 0)),
            'current_balance' => (float) (str_replace(',', '', $row['I'] ?? 0)),
            'aging' => [
                'Overdue > 1 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['J'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['K'] ?? 0)) ],
                'Overdue > 2 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['L'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['M'] ?? 0)) ],
                'Overdue > 3 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['N'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['O'] ?? 0)) ],
                'Overdue > 6 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['P'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['Q'] ?? 0)) ],
                'Overdue > 12 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['R'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['S'] ?? 0)) ],
                'Overdue > 18 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['T'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['U'] ?? 0)) ],
                'Overdue > 24 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['V'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['W'] ?? 0)) ],
                'Overdue > 30 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['X'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['Y'] ?? 0)) ],
                'Overdue > 36 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['Z'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['AA'] ?? 0)) ],
                'Overdue > 42 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['AB'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['AC'] ?? 0)) ],
                'Overdue > 48 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['AD'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['AE'] ?? 0)) ],
                'Overdue > 54 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['AF'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['AG'] ?? 0)) ],
                'Overdue > 60 month' => [ 'no_accounts' => (int) (str_replace(',', '', $row['AH'] ?? 0)), 'balance' => (float) (str_replace(',', '', $row['AI'] ?? 0)) ],
            ]
        ];
    }
}
