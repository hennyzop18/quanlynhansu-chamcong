<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $query = LeaveRequest::with('employee');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(15);

        return view('admin.leave_requests.index', compact('requests', 'status'));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load('employee');
        return view('admin.leave_requests.show', compact('leaveRequest'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'approved',
            'admin_note' => $request->input('admin_note'),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Đã phê duyệt đơn nghỉ phép.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note'),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Đã từ chối đơn nghỉ phép.');
    }
}
