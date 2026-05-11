<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f7; }
        .email-wrapper { width: 100%; background-color: #f4f4f7; padding: 20px 0; }
        .email-content { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background-color: #4f46e5; padding: 30px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .body { padding: 40px; }
        .greeting { font-size: 18px; font-weight: bold; margin-bottom: 20px; color: #111827; }
        .summary-card { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 20px; margin: 25px 0; }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .summary-item:last-child { margin-bottom: 0; padding-top: 10px; border-top: 1px dashed #d1d5db; }
        .label { color: #6b7280; font-size: 14px; }
        .value { font-weight: 600; color: #111827; }
        .value.net { color: #4f46e5; font-size: 18px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; }
        .button { display: inline-block; padding: 12px 24px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <div class="header">
                <h1>Thông Báo Phiếu Lương</h1>
            </div>
            <div class="body">
                <div class="greeting">Chào {{ $salary->employee->full_name }},</div>
                <p>Hệ thống Quản lý Nhân sự <strong>QLNS</strong> xin thông báo phiếu lương <strong>tháng {{ $salary->month }}/{{ $salary->year }}</strong> của bạn đã được phê duyệt và phát hành.</p>
                
                <div class="summary-card">
                    <div class="summary-item">
                        <span class="label">Lương cơ bản:</span>
                        <span class="value">{{ number_format($salary->base_salary, 0, ',', '.') }} VNĐ</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Lương thực tế (theo công):</span>
                        <span class="value">{{ number_format($salary->prorated_salary > 0 ? $salary->prorated_salary : $salary->base_salary, 0, ',', '.') }} VNĐ</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Phụ cấp & Thưởng:</span>
                        <span class="value">+ {{ number_format(($salary->taxable_allowances + $salary->nontaxable_allowances + $salary->bonus), 0, ',', '.') }} VNĐ</span>
                    </div>
                    <div class="summary-item" style="border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 10px;">
                        <span class="label">Tổng thu nhập (Gross):</span>
                        <span class="value">{{ number_format($salary->total_gross, 0, ',', '.') }} VNĐ</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Tổng khấu trừ & Phạt:</span>
                        <span class="value" style="color: #ef4444;">- {{ number_format($salary->total_deductions, 0, ',', '.') }} VNĐ</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Thực lĩnh (Net):</span>
                        <span class="value net">{{ number_format($salary->net_salary, 0, ',', '.') }} VNĐ</span>
                    </div>
                </div>

                <p>Vui lòng xem chi tiết các khoản lương, thưởng và khấu trừ trong file <strong>PDF đính kèm</strong> bên dưới.</p>
                
                <p style="font-size: 14px; color: #6b7280; margin-top: 30px;">Nếu có bất kỳ thắc mắc nào về bảng lương, vui lòng liên hệ phòng Nhân sự để được giải đáp.</p>
            </div>
            <div class="footer">
                <p>Đây là email tự động từ hệ thống QLNS, vui lòng không phản hồi email này.</p>
                <p>&copy; {{ date('Y') }} Công ty QLNS. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
