<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $query = AttendanceAdjustment::with(['attendance.employee', 'approver']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $adjustments = $query->latest()->paginate(15);

        return view('admin.attendance_adjustments.index', compact('adjustments', 'status'));
    }

    public function approve(Request $request, AttendanceAdjustment $adjustment)
    {
        $adjustment->update([
            'status' => 'approved',
            'admin_note' => $request->input('admin_note'),
            'approved_by' => Auth::id(),
        ]);

        // Cập nhật lại status của attendance nếu cần
        $attendance = $adjustment->attendance;
        if ($adjustment->type === 'late' || $adjustment->type === 'early' || $adjustment->type === 'forget') {
            $attendance->update([
                'status' => 'present',
                'actual_hours' => $attendance->actual_hours > 0 ? $attendance->actual_hours : 8,
                'notes' => $attendance->notes . "\n[Duyệt giải trình: " . $adjustment->reason . "]"
            ]);
        }

        return redirect()->back()->with('success', 'Đã phê duyệt giải trình chấm công.');
    }

    public function reject(Request $request, AttendanceAdjustment $adjustment)
    {
        $adjustment->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note'),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Đã từ chối giải trình chấm công.');
    }
}
