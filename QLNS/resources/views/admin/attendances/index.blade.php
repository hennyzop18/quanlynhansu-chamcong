<x-admin-layout>
     <x-slot name="header">
         <h2 class="font-semibold text-xl text-gray-800 leading-tight">
             {{ __('Quản lý Chấm Công') }}
         </h2>
     </x-slot>

     <div class="py-12">
         <div class="max-w-full mx-auto sm:px-6 lg:px-8"> 
             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 bg-white border-b border-gray-200">

                     {{-- Form Lọc --}}
                     <form method="GET" action="{{ route('admin.attendances.index') }}" class="mb-6 p-4 bg-gray-50 rounded-md border">
                         <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                             <div>
                                 <x-input-label for="date" value="Ngày" />
                                 <x-text-input type="date" name="date" id="date" class="block w-full mt-1" :value="$filterDate ?? ''" />
                             </div>
                             <div>
                                  <x-input-label for="department_id" value="Phòng Ban" />
                                  <select name="department_id" id="department_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                      <option value="">-- Tất cả Phòng Ban --</option>
                                      @foreach($departments as $department)
                                          <option value="{{ $department->id }}" @selected($filterDepartment == $department->id)>
                                              {{ $department->name }}
                                          </option>
                                      @endforeach
                                  </select>
                              </div>
                             <div>
                                 <x-input-label for="employee_id" value="Nhân Viên" />
                                 <select name="employee_id" id="employee_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                     <option value="">-- Tất cả Nhân Viên --</option>
                                     @foreach($employees as $employee)
                                         <option value="{{ $employee->id }}" @selected($filterEmployee == $employee->id)>
                                             {{ $employee->last_name }} {{ $employee->first_name }}
                                         </option>
                                     @endforeach
                                 </select>
                             </div>
                             <div>
                                 <x-primary-button type="submit">Lọc</x-primary-button>
                                 <a href="{{ route('admin.attendances.index') }}" class="ml-2 text-sm text-gray-600 underline">Xóa lọc</a>
                             </div>
                         </div>
                     </form>

                     <x-session-status class="mb-4" :status="session('success')" type="success" />
                     <x-session-status class="mb-4" :status="session('error')" type="error" />

                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                 <tr>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã NV</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nhân Viên</th>
                                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phòng Ban</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giờ Vào</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giờ Ra</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng Thái</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin sửa</th>
                                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ghi chú</th>
                                     <th class="relative px-6 py-3"></th>
                                 </tr>
                             </thead>
                             <tbody class="bg-white divide-y divide-gray-200">
                                 @forelse ($attendances as $attendance)
                                     <tr>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->employee->employee_code ?? 'N/A' }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $attendance->employee->last_name ?? '' }} {{ $attendance->employee->first_name ?? 'N/A' }}</td>
                                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->employee->department->name ?? 'N/A' }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s') : '-' }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '-' }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap">
                                             @if($attendance->status)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $attendance->status == 'present' ? 'bg-green-100 text-green-800' : ($attendance->status == 'late' ? 'bg-yellow-100 text-yellow-800' : ($attendance->status == 'absent' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                                    {{ ucfirst($attendance->status == 'present' ? 'Có mặt' : ($attendance->status == 'late' ? 'Đi trễ' : ($attendance->status == 'absent' ? 'Vắng' : 'N/A'))) }}
                                                </span>
                                             @endif
                                         </td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                             @if($attendance->admin)
                                                 <span class="text-indigo-600 font-medium">{{ $attendance->admin->name }}</span>
                                             @else
                                                 <span class="text-gray-400 italic">Hệ thống</span>
                                             @endif
                                         </td>
                                         <td class="px-6 py-4 whitespace-normal text-sm text-gray-500 text-ellipsis overflow-hidden max-w-xs">{{ $attendance->notes }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                             <a href="{{ route('admin.attendances.edit', ['attendance' => $attendance->id] + request()->query()) }}" class="text-blue-600 hover:text-blue-900">Sửa</a>
                                         </td>
                                     </tr>
                                 @empty
                                     <tr>
                                         <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">Không tìm thấy dữ liệu chấm công cho ngày đã chọn.</td>
                                     </tr>
                                 @endforelse
                             </tbody>
                         </table>
                     </div>

                     <div class="mt-4">
                         {{ $attendances->links() }}
                     </div>

                 </div>
             </div>
         </div>
     </div>
  </x-admin-layout>