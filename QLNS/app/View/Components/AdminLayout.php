<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     * Lấy view / nội dung đại diện cho component.
     * Trỏ đến file layout admin đã tồn tại.
     */
    public function render(): View
    {
        // Trả về view nằm trong resources/views/layouts/admin.blade.php
        return view('layouts.admin');
    }
}