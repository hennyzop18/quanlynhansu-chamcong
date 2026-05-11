<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Phiếu Lương - {{ $salary->employee->full_name }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.5; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .company-name { font-size: 18px; font-weight: bold; color: #1e3a8a; }
        .title { font-size: 16px; font-weight: bold; margin-top: 10px; text-transform: uppercase; }
        .info-table, .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; }
        .details-table th, .details-table td { border: 1px solid #ddd; padding: 8px; }
        .details-table th { background-color: #f3f4f6; text-align: left; }
        .text-right { text-align: right; }
        .footer { margin-top: 50px; }
        .signature-box { width: 45%; display: inline-block; text-align: center; }
        .total-row { font-weight: bold; background-color: #e5e7eb; }
        .net-salary { font-size: 14px; font-weight: bold; color: #b91c1c; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">CÔNG TY QUẢN LÝ NHÂN SỰ QLNS</div>
        <div class="title">PHIẾU LƯƠNG THÁNG {{ $salary->month }}/{{ $salary->year }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%">Nhân viên:</td>
            <td width="30%"><strong>{{ $salary->employee->full_name }}</strong></td>
            <td width="20%">Mã NV:</td>
            <td width="30%"><strong>{{ $salary->employee->employee_code }}</strong></td>
        </tr>
        <tr>
            <td>Phòng ban:</td>
            <td>{{ $salary->employee->department->name ?? '--' }}</td>
            <td>Chức vụ:</td>
            <td>{{ $salary->employee->position->name ?? '--' }}</td>
        </tr>
        <tr>
            <td>Ngày công chuẩn:</td>
            <td>{{ $salary->standard_work_days }} ngày</td>
            <td>Ngày công thực:</td>
            <td>{{ $salary->actual_work_days }} ngày</td>
        </tr>
    </table>

    <table class="details-table">
        <thead>
            <tr>
                <th>Nội dung</th>
                <th class="text-right">Số tiền (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Lương cơ bản / Lương tính theo công</td>
                <td class="text-right">{{ number_format($salary->prorated_salary ?? $salary->base_salary, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Phụ cấp chịu thuế</td>
                <td class="text-right">{{ number_format($salary->taxable_allowances, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Phụ cấp miễn thuế</td>
                <td class="text-right">{{ number_format($salary->nontaxable_allowances, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Tiền tăng ca (OT)</td>
                <td class="text-right">{{ number_format(($salary->ot_hours ?? 0) * ($salary->hourly_rate_snapshot ?? 0) * 1.5, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Thưởng (Bonus)</td>
                <td class="text-right">{{ number_format($salary->bonus, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>TỔNG THU NHẬP (GROSS)</td>
                <td class="text-right">{{ number_format($salary->total_gross, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Bảo hiểm xã hội (10.5%)</td>
                <td class="text-right">- {{ number_format($salary->si_deduction + $salary->hi_deduction + $salary->ui_deduction, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Thuế TNCN</td>
                <td class="text-right">- {{ number_format($salary->pit_tax, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Phạt đi trễ / Kỷ luật</td>
                <td class="text-right">- {{ number_format($salary->fines + $salary->late_fine, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>THỰC LĨNH (NET)</td>
                <td class="text-right net-salary">{{ number_format($salary->net_salary, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <p><strong>Người lập biểu</strong></p>
            <br><br><br>
            <p>(Ký, ghi rõ họ tên)</p>
        </div>
        <div class="signature-box" style="float: right;">
            <p><strong>Người nhận lương</strong></p>
            <br><br><br>
            <p>(Ký, ghi rõ họ tên)</p>
        </div>
    </div>
</body>
</html>
