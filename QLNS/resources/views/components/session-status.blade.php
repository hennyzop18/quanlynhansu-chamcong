{{-- resources/views/components/session-status.blade.php --}}
@props(['status', 'type' => 'status']) {{-- status là message, type là kiểu thông báo --}}

@if ($status)
    @php
        $baseClasses = 'mb-4 font-medium text-sm p-3 rounded-md border';
        $typeClasses = '';
        switch ($type) {
            case 'success':
                $typeClasses = 'text-green-600 bg-green-100 border-green-200';
                break;
            case 'error':
                $typeClasses = 'text-red-600 bg-red-100 border-red-200';
                break;
            case 'warning':
                $typeClasses = 'text-yellow-600 bg-yellow-100 border-yellow-200';
                break;
            case 'info':
            case 'status': // Mặc định cho 'status'
            default:
                $typeClasses = 'text-blue-600 bg-blue-100 border-blue-200';
                break;
        }
    @endphp

    <div {{ $attributes->merge(['class' => $baseClasses . ' ' . $typeClasses]) }}>
        {{ $status }}
    </div>
@endif