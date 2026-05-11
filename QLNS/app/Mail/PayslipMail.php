<?php

namespace App\Mail;

use App\Models\Salary;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $salary;

    /**
     * Create a new message instance.
     */
    public function __construct(Salary $salary)
    {
        $this->salary = $salary;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Phiếu lương tháng {$this->salary->month}/{$this->salary->year} - {$this->salary->employee->full_name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payslip',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.payslip', ['salary' => $this->salary]);
        $output = $pdf->output();
        
        return [
            Attachment::fromData(fn () => $output, "Phieu_luong_{$this->salary->month}_{$this->salary->year}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
