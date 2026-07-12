<?php

namespace App\Models;

use App\Enums\RequestStatus;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ItemRequest extends Model
{
    protected $fillable = [
        'employee_name',
        'division',
        'item_id',
        'item_name',
        'category',
        'stock_requested',
        'status',

        'requested_at',

        'approved_kaur_at',
        'approved_kaur_by',

        'approved_kasi_at',
        'approved_kasi_by',

        'rejected_at',
        'rejected_by',

        'completed_at',
        'adjusted_stock_requested',
        'final_approved_stock',

        'confirmed_by_staff_at',
        'confirmed_by_staff_by',

        'signature',


        'bon_number',
        'bon_number_formatted',

        'request_code',
    ];

    protected $casts = [
        'status' => RequestStatus::class,

        'requested_at' => 'datetime',
        'approved_kaur_at' => 'datetime',
        'approved_kasi_at' => 'datetime',

        'confirmed_by_staff_at' => 'datetime',

        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',

        'created_at' => 'datetime'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    protected $appends = [
        'formatted_requested_at',
        'formatted_approved_kaur_at',
        'formatted_approved_kasi_at',
        'formatted_completed_at',
        'formatted_rejected_at',
        'formatted_created_at',
        'formatted_confirmed_by_staff_at',
        'final_stock_requested', // FIX 1: Wajib didaftarkan di sini agar ->append() di Controller tidak error 500
    ];

    public function getFormattedRequestedAtAttribute()
    {
        return $this->requested_at
            ? Carbon::parse($this->requested_at)->translatedFormat('d F Y H:i')
            : null;
    }

    public function getFormattedApprovedKaurAtAttribute()
    {
        return $this->approved_kaur_at
            ? Carbon::parse($this->approved_kaur_at)->translatedFormat('d F Y H:i')
            : null;
    }

    public function getFormattedApprovedKasiAtAttribute()
    {
        return $this->approved_kasi_at
            ? Carbon::parse($this->approved_kasi_at)->translatedFormat('d F Y H:i')
            : null;
    }

    public function getFormattedCompletedAtAttribute()
    {
        return $this->completed_at
            ? Carbon::parse($this->completed_at)->translatedFormat('d F Y H:i')
            : null;
    }

    public function getFormattedRejectedAtAttribute()
    {
        return $this->rejected_at
            ? Carbon::parse($this->rejected_at)->translatedFormat('d F Y H:i')
            : null;
    }

    /* FIX 2: Perbarui logikanya agar mendukung hirarki kuantitas yang baru */
    public function getFinalStockRequestedAttribute()
    {
        // 1. Jika sudah disetujui staf, pakai angka dari staf
        if ($this->final_approved_stock !== null) {
            return $this->final_approved_stock;
        }

        // 2. Jika baru sampai tahap Kaur, pakai angka hasil adjust Kaur
        if ($this->adjusted_stock_requested !== null) {
            return $this->adjusted_stock_requested;
        }

        // 3. Jika masih pending baru, pakai angka permintaan awal pemohon
        return $this->stock_requested;
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at
            ? Carbon::parse($this->created_at)->translatedFormat('d F Y')
            : null;
    }

    public function getFormattedConfirmedByStaffAtAttribute()
    {
        return $this->confirmed_by_staff_at
            ? Carbon::parse($this->confirmed_by_staff_at)->translatedFormat('d F Y H:i')
            : null;
    }
}
