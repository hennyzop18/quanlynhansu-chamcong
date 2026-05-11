<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;
        $requests = OvertimeRequest::where('employee_id', $employee->id)
            ->latest()
            ->paginate(10);

        return view('employee.overtime.index', compact('requests'));
    }

    public function create()
    {
        return view('employee.overtime.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:500',
        ]);

        $employee = Auth::user()->employee;
        
        // Tính số giờ OT
        $start = Carbon::parse($validated['start_time']);
        $end = Carbon::parse($validated['end_time']);
        $hours = round($start->diffInMinutes($end) / 60, 1);

        OvertimeRequest::create([
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'hours' => $hours,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('overtime.index')->with('success', 'Đăng ký tăng ca thành công, vui lòng chờ duyệt.');
    }
}
