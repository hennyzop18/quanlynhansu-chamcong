<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceAdjustmentController extends Controller
{
    public function create(Attendance $attendance)
    {
        // Kiểm tra quyền sở hữu
        if ($attendance->employee_id !== Auth::user()->employee->id) {
            abort(403);
        }

        return view('employee.attendance.adjust', compact('attendance'));
    }

    public function store(Request $request, Attendance $attendance)
    {
        if ($attendance->employee_id !== Auth::user()->employee->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|in:late,early,forget',
            'reason' => 'required|string|max:500',
        ]);

        AttendanceAdjustment::create([
            'attendance_id' => $attendance->id,
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('employee.attendance.history')->with('success', 'Đã gửi giải trình chấm công, vui lòng chờ duyệt.');
    }
}
