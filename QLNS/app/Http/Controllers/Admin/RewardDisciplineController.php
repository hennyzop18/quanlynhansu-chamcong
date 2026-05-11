<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardDiscipline;
use App\Models\Employee;
use Illuminate\Http\Request;

class RewardDisciplineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Lọc theo nhân viên, loại, ngày tháng...
        $filterEmployee = $request->input('employee_id');
        $filterType = $request->input('type');

        $query = RewardDiscipline::with('employee')->latest();

        if ($filterEmployee) {
            $query->where('employee_id', $filterEmployee);
        }
        if ($filterType) {
            $query->where('type', $filterType);
        }

        $rewardsDiscipline = $query->paginate(20);
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        return view('admin.rewards_discipline.index', compact('rewardsDiscipline', 'employees', 'filterEmployee', 'filterType'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        return view('admin.rewards_discipline.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => ['required', \Illuminate\Validation\Rule::in(['reward', 'discipline'])],
            'date' => 'required|date',
            'reason' => 'required|string',
            'amount' => 'nullable|numeric|min:0', // Cho phép null hoặc số không âm
        ]);

        RewardDiscipline::create($validated);

        return redirect()->route('admin.rewards-discipline.index')->with('success', 'Thêm ghi nhận Khen thưởng/Kỷ luật thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(RewardDiscipline $rewardsDiscipline) // Laravel tự động chuyển snake_case thành camelCase
    {
        // Route model binding sẽ dùng $rewardDiscipline variable
        // Đảm bảo tên biến trong route ({rewards_discipline}) khớp
        // Hoặc đặt tên biến là $rewardDiscipline để rõ ràng
        $rewardDiscipline = $rewardsDiscipline; // Gán lại nếu tên biến khác
        $rewardDiscipline->load('employee');
        return view('admin.rewards_discipline.show', compact('rewardDiscipline'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RewardDiscipline $rewardsDiscipline)
    {
        $rewardDiscipline = $rewardsDiscipline;
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        return view('admin.rewards_discipline.edit', compact('rewardDiscipline', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RewardDiscipline $rewardsDiscipline)
    {
        $rewardDiscipline = $rewardsDiscipline;
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => ['required', \Illuminate\Validation\Rule::in(['reward', 'discipline'])],
            'date' => 'required|date',
            'reason' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $rewardDiscipline->update($validated);

        return redirect()->route('admin.rewards-discipline.index')->with('success', 'Cập nhật ghi nhận thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RewardDiscipline $rewardsDiscipline)
    {
        $rewardDiscipline = $rewardsDiscipline;
        $rewardDiscipline->delete();
        return redirect()->route('admin.rewards-discipline.index')->with('success', 'Xóa ghi nhận thành công!');
    }
}