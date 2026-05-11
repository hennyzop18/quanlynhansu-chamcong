<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $query = OvertimeRequest::with('employee');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(15);

        return view('admin.overtime_requests.index', compact('requests', 'status'));
    }

    public function approve(Request $request, OvertimeRequest $overtimeRequest)
    {
        $overtimeRequest->update([
            'status' => 'approved',
            'admin_note' => $request->input('admin_note'),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Đã phê duyệt đơn tăng ca.');
    }

    public function reject(Request $request, OvertimeRequest $overtimeRequest)
    {
        $overtimeRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note'),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Đã từ chối đơn tăng ca.');
    }
}
