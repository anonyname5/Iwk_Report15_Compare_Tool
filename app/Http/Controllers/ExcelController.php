<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ExcelJson;
use App\Services\ExcelParserService;
use App\Services\ComparisonService;
use App\Services\ExcelExportService;

class ExcelController extends Controller
{
    public function index()
    {
        $comparisonPairs = ExcelJson::getComparisonPairs();
        return view('excel.index', compact('comparisonPairs'));
    }

    public function show($id)
    {
        $excel = ExcelJson::findOrFail($id);
        
        // Apply data corrections if needed
        if (!empty($excel->data['cost_centers'])) {
            foreach ($excel->data['cost_centers'] as $key => $costCenter) {
                $code = $costCenter['code'] ?? '';
                
                // Handle special cases
                if ($code === '1W01840') {
                    Log::debug("Applying fixes for cost center 1W01840");
                    
                    // Ensure main_descriptions is initialized properly
                    if (empty($costCenter['main_descriptions']) || !is_array($costCenter['main_descriptions'])) {
                        $excel->data['cost_centers'][$key]['main_descriptions'] = [];
                        Log::debug("Initialized empty main_descriptions for 1W01840");
                    }
                }
                
                // General validation/correction for all cost centers
                if (!isset($costCenter['main_descriptions']) || !is_array($costCenter['main_descriptions'])) {
                    $excel->data['cost_centers'][$key]['main_descriptions'] = [];
                    Log::debug("Fixed missing main_descriptions for cost center {$code}");
                }
            }
        }
        
        return view('excel.show', compact('excel'));
    }

    public function uploadForm()
    {
        return view('upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file_1' => 'required|file|mimes:xlsx,xls',
            'file_2' => 'required|file|mimes:xlsx,xls',
            'comparison_name' => 'required|string|max:255',
        ]);

        try {
            $comparisonName = $request->input('comparison_name');
            $files = [];
            $fileIds = [];
            
            // Process file 1
            $file1 = $request->file('file_1');
            $filename1 = $file1->getClientOriginalName();
            $filePath1 = $file1->getPathname();
            
            Log::debug('Starting to process 1st Excel file: ' . $filename1);
            
            $parserService = new ExcelParserService();
            $structuredData1 = $parserService->parse($filePath1);
            
            $costCentersCount1 = count($structuredData1['cost_centers'] ?? []);
            Log::debug('Total cost centers found in file 1: ' . $costCentersCount1);
            
            // Save file 1 to DB
            $excelModel1 = ExcelJson::create([
                'file_name' => $filename1,
                'data' => $structuredData1,
                'file_type' => 'file_1',
                'comparison_name' => $comparisonName
            ]);
            
            $fileIds[] = $excelModel1->id;
            $files[] = [
                'name' => $filename1,
                'cost_centers_count' => $costCentersCount1
            ];
            
            // Process file 2
            $file2 = $request->file('file_2');
            $filename2 = $file2->getClientOriginalName();
            $filePath2 = $file2->getPathname();
            
            Log::debug('Starting to process 2nd Excel file: ' . $filename2);
            
            $structuredData2 = $parserService->parse($filePath2);
            
            $costCentersCount2 = count($structuredData2['cost_centers'] ?? []);
            Log::debug('Total cost centers found in file 2: ' . $costCentersCount2);
            
            // Save file 2 to DB
            $excelModel2 = ExcelJson::create([
                'file_name' => $filename2,
                'data' => $structuredData2,
                'file_type' => 'file_2',
                'comparison_name' => $comparisonName
            ]);
            
            $fileIds[] = $excelModel2->id;
            $files[] = [
                'name' => $filename2,
                'cost_centers_count' => $costCentersCount2
            ];

            // Check if this is an AJAX request
            if ($request->ajax()) {
                // Return a JSON response for AJAX requests
                return response()->json([
                    'message' => 'Files processed successfully',
                    'comparison_name' => $comparisonName,
                    'files' => $files,
                    'file_ids' => $fileIds,
                    'success' => true,
                    'redirect' => route('excel.index')
                ]);
            }
            
            // For regular form submission, redirect to the list page with a success message
            return redirect()->route('excel.index')
                ->with('success', 'Files uploaded and processed successfully.');

        } catch (\Exception $e) {
            Log::error('Excel processing error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Error processing Excel files: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error processing Excel files: ' . $e->getMessage());
        }
    }
    
    /**
     * Compare two Excel files and directly download the comparison Excel file
     * 
     * @param string $comparisonName The name of the comparison
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function compare($comparisonName)
    {
        try {
            // Find the pair of files with the given comparison name
            $file1 = ExcelJson::where('comparison_name', $comparisonName)
                ->where('file_type', 'file_1')
                ->firstOrFail();
                
            $file2 = ExcelJson::where('comparison_name', $comparisonName)
                ->where('file_type', 'file_2')
                ->firstOrFail();
            
            // Use the ComparisonService to generate comparison data
            $comparisonService = new ComparisonService();
            $comparisonData = $comparisonService->compare($file1, $file2);
            
            // Use the ExcelExportService to export data to Excel in original format
            $excelExportService = new ExcelExportService();
            $filepath = $excelExportService->exportOriginalFormat($comparisonData, $file1, $file2);
            
            // Return file for download
            return response()->download($filepath, $comparisonData['comparison_name'] . '.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Comparison error: ' . $e->getMessage());
            return redirect()->route('excel.index')
                ->with('error', 'Error generating comparison: ' . $e->getMessage());
        }
    }
    
    /**
     * Export comparison data to Excel and return download response
     * 
     * @param string $comparisonName The name of the comparison
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel($comparisonName)
    {
        try {
            // Find the pair of files with the given comparison name
            $file1 = ExcelJson::where('comparison_name', $comparisonName)
                ->where('file_type', 'file_1')
                ->firstOrFail();
                
            $file2 = ExcelJson::where('comparison_name', $comparisonName)
                ->where('file_type', 'file_2')
                ->firstOrFail();
            
            // Use the ComparisonService to generate comparison data
            $comparisonService = new ComparisonService();
            $comparisonData = $comparisonService->compare($file1, $file2);
            
            // Use the ExcelExportService to export data to Excel
            $excelExportService = new ExcelExportService();
            $filepath = $excelExportService->exportComparison($comparisonData, $file1, $file2);
            
            // Return file for download
            return response()->download($filepath, $comparisonData['comparison_name'] . '.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Excel export error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->route('excel.compare', $comparisonName)
                ->with('error', 'Error exporting to Excel: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a comparison (both files)
     * 
     * @param string $comparisonName The name of the comparison
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function delete($comparisonName)
    {
        try {
            // Find both files in the comparison
            $file1 = ExcelJson::where('comparison_name', $comparisonName)
                ->where('file_type', 'file_1')
                ->first();
                
            $file2 = ExcelJson::where('comparison_name', $comparisonName)
                ->where('file_type', 'file_2')
                ->first();
            
            if (!$file1 && !$file2) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Comparison not found'
                    ], 404);
                }
                return redirect()->route('excel.index')
                    ->with('error', 'Comparison not found.');
            }
            
            $deletedCount = 0;
            
            // Delete file 1 if exists
            if ($file1) {
                $file1->delete();
                $deletedCount++;
                Log::info('Deleted file 1: ' . $file1->file_name);
            }
            
            // Delete file 2 if exists
            if ($file2) {
                $file2->delete();
                $deletedCount++;
                Log::info('Deleted file 2: ' . $file2->file_name);
            }
            
            Log::info('Deleted comparison: ' . $comparisonName . ' (' . $deletedCount . ' files)');
            
            // Return JSON response for AJAX requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comparison deleted successfully',
                    'deleted_count' => $deletedCount
                ]);
            }
            
            // For regular requests, redirect with success message
            return redirect()->route('excel.index')
                ->with('success', 'Comparison "' . $comparisonName . '" deleted successfully.');
                
        } catch (\Exception $e) {
            Log::error('Delete comparison error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting comparison: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('excel.index')
                ->with('error', 'Error deleting comparison: ' . $e->getMessage());
        }
    }
}
