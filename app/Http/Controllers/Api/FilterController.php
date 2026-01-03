<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FilterController extends Controller
{
    public function filterRequests(Request $request)
    {
        $click = $request->input('click');
        
        if ($click == "filterReq") {
            $userid = Auth::user()->emp_id;
            $status = $request->input('status');
            $type = $request->input('type');
            $limit = $request->input('limit');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            $query = Application::where('employee_id', $userid);
            
            // Apply status filter
            if ($status != 'all') {
                $query->where('status', $status);
            }
            
            // Apply type filter
            if ($type != 'all') {
                $query->where('req_type', $type);
            }
            
            // Apply date limit filter
            switch ($limit) {
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'quarter':
                    $currentQuarter = ceil(now()->month / 3);
                    $startMonth = ($currentQuarter - 1) * 3 + 1;
                    $endMonth = $currentQuarter * 3;
                    $query->whereMonth('created_at', '>=', $startMonth)
                          ->whereMonth('created_at', '<=', $endMonth)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
                case 'custom':
                    if ($startDate && $endDate) {
                        $query->whereDate('created_at', '>=', $startDate)
                              ->whereDate('created_at', '<=', $endDate);
                    }
                    break;
                // 'all' - no date filter
            }
            
            $applications = $query->orderBy('id', 'desc')->get();
            
            if ($applications->count() >= 1) {
                $output = "";
                foreach ($applications as $row) {
                    $create = Carbon::parse($row->created_at)->setTimezone('Asia/Kolkata');
                    $time = $create->format('Y-m-d H:i:s');

                    $start = Carbon::parse($row->start_date)->setTimezone('Asia/Kolkata');
                    $end = $row->end_date ? Carbon::parse($row->end_date)->setTimezone('Asia/Kolkata') : null;
                    
                    $output .= "<tr>
                                    <td>{$row->created_at}</td>
                                    <td>{$row->req_type}</td>
                                    <td>{$row->subject}</td>
                                    <td>{$start->format('M d')} - " . ($end ? $end->format('M d') : "") . "</td>
                                    <td><span class='status-badge status-{$row->status}'>{$row->status}</span></td>
                                    <td>
                                        <button class='action-btn view-btn view_request' data-id='{$row->employee_id}' data-type='{$row->req_type}' data-time='{$time}'>
                                            <i class='fas fa-eye'></i> View
                                        </button>
                                    </td>
                                </tr>";
                }
                return response($output);
            } else {
                return response("<p style='color:red;'>Not Found!</p>");
            }
        }
        
        return response('error');
    }
}