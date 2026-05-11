<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with(['employees:id,first_name,last_name,employee_code,avatar,personal_email,phone_number,department_id,position_id'])->latest()->paginate(15);
        
        // Chuẩn bị avatar_url để dùng trong JS
        $departments->each(function($dept) {
            $dept->employees->each(function($emp) {
                $emp->avatar_url = $emp->avatar ? asset('storage/' . $emp->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($emp->full_name) . '&color=7F9CF5&background=EBF4FF';
            });
        });

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:departments,name|max:255',
            'description' => 'nullable|string',
        ]);

        Department::create($validated);
        return redirect()->route('admin.departments.index')->with('success', 'Thêm phòng ban thành công!');
    }

    public function show(Department $department)
    {
        return view('admin.departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('departments')->ignore($department->id)],
            'description' => 'nullable|string',
        ]);

        $department->update($validated);
        return redirect()->route('admin.departments.index')->with('success', 'Cập nhật phòng ban thành công!');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', 'Không thể ẩn phòng ban đang có nhân viên. Hãy chuyển nhân viên sang phòng ban khác trước.');
        }

        $department->delete();
        return redirect()->route('admin.departments.index')->with('success', 'Đã chuyển phòng ban vào thùng rác.');
    }

    public function trash()
    {
        $departments = Department::onlyTrashed()->latest()->paginate(15);
        return view('admin.departments.trash', compact('departments'));
    }

    public function restore($id)
    {
        $department = Department::withTrashed()->findOrFail($id);
        $department->restore();
        return redirect()->route('admin.departments.trash')->with('success', 'Khôi phục phòng ban thành công!');
    }
}