<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class EmployeeLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     * Lấy view / nội dung đại diện cho component.
     * Trỏ đến file layout employee đã tồn tại.
     */
    public function render(): View
    {
        // Trả về view nằm trong resources/views/layouts/employee.blade.php
        return view('layouts.employee');
    }
}