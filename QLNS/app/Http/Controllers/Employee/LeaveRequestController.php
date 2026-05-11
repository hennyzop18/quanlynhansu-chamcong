<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;
        $requests = LeaveRequest::where('employee_id', $employee->id)
            ->latest()
            ->paginate(10);

        return view('employee.leaves.index', compact('requests'));
    }

    public function create()
    {
        return view('employee.leaves.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:annual,sick,unpaid,marriage,funeral,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $employee = Auth::user()->employee;
        
        // Tính số ngày nghỉ (tạm thời tính đơn giản bằng diffInDays + 1)
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $totalDays = $start->diffInDays($end) + 1;

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('leaves.index')->with('success', 'Đăng ký nghỉ phép thành công, vui lòng chờ duyệt.');
    }
}
