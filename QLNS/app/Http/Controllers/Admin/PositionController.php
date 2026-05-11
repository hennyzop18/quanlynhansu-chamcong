<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with(['employees:id,first_name,last_name,employee_code,avatar,personal_email,phone_number,department_id,position_id'])->latest()->paginate(15);
        
        $positions->each(function($pos) {
            $pos->employees->each(function($emp) {
                $emp->avatar_url = $emp->avatar ? asset('storage/' . $emp->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($emp->full_name) . '&color=7F9CF5&background=EBF4FF';
            });
        });

        return view('admin.positions.index', compact('positions'));
    }

    public function create()
    {
        return view('admin.positions.create');
    }

    public function store(StorePositionRequest $request)
    {
        Position::create($request->validated());
        return redirect()->route('admin.positions.index')->with('success', 'Thêm chức vụ thành công!');
    }

    public function show(Position $position)
    {
        return view('admin.positions.show', compact('position'));
    }

    public function edit(Position $position)
    {
        return view('admin.positions.edit', compact('position'));
    }

    public function update(UpdatePositionRequest $request, Position $position)
    {
        $position->update($request->validated());
        return redirect()->route('admin.positions.index')->with('success', 'Cập nhật chức vụ thành công!');
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->where('status', 'active')->count() > 0) {
            return redirect()->route('admin.positions.index')
                ->with('error', 'Không thể ẩn chức vụ đang có nhân viên hoạt động.');
        }

        $position->delete();
        return redirect()->route('admin.positions.index')->with('success', 'Đã chuyển chức vụ vào thùng rác.');
    }

    public function trash()
    {
        $positions = Position::onlyTrashed()->latest()->paginate(15);
        return view('admin.positions.trash', compact('positions'));
    }

    public function restore($id)
    {
        $position = Position::withTrashed()->findOrFail($id);
        $position->restore();
        return redirect()->route('admin.positions.trash')->with('success', 'Khôi phục chức vụ thành công!');
    }
}