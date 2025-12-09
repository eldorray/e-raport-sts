<?php

namespace App\Http\Controllers;

use App\Models\PrintSetting;
use App\Models\SchoolProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrintSettingController extends Controller
{
    public function edit(): View
    {
        $setting = PrintSetting::first();
        $school = SchoolProfile::first();

        return view('rapor.print-settings', [
            'setting' => $setting,
            'school' => $school,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tempat_cetak' => ['nullable', 'string', 'max:255'],
            'tanggal_cetak' => ['nullable', 'date'],
            'tanggal_cetak_rapor' => ['nullable', 'date'],
            'watermark' => ['nullable', 'string', 'max:255'],
        ]);

        PrintSetting::query()->firstOrNew()->fill($data)->save();

        return back()->with('status', __('Pengaturan cetak berhasil disimpan.'));
    }
}
