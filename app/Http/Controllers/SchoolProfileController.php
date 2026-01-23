<?php

namespace App\Http\Controllers;

use App\Models\SchoolProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SchoolProfileController extends Controller
{
    public function index()
    {
        $schoolProfile = SchoolProfile::first() ?? new SchoolProfile();

        return view('school-profile', compact('schoolProfile'));
    }

    public function update(Request $request)
    {
        $intent = $request->input('intent', 'identity');
        $schoolProfile = SchoolProfile::first() ?? new SchoolProfile();

        if ($intent === 'logo') {
            if (! $schoolProfile->exists) {
                return back()->withErrors([
                    'logo' => __('Lengkapi identitas sekolah sebelum mengelola logo.'),
                ]);
            }

            $request->validate([
                'logo' => ['nullable', 'image', 'max:2048'],
                'remove_logo' => ['nullable', 'boolean'],
            ]);

            if ($request->boolean('remove_logo') && $schoolProfile->logo) {
                Storage::disk('public')->delete($schoolProfile->logo);
                $schoolProfile->logo = null;
            }

            if ($request->hasFile('logo')) {
                if ($schoolProfile->logo) {
                    Storage::disk('public')->delete($schoolProfile->logo);
                }

                $schoolProfile->logo = $request->file('logo')->store('logos', 'public');
            }

            $schoolProfile->save();

            return back()->with('status', __('Logo sekolah berhasil diperbarui.'));
        }

        if ($intent === 'logo_right') {
            if (! $schoolProfile->exists) {
                return back()->withErrors([
                    'logo_right' => __('Lengkapi identitas sekolah sebelum mengelola logo kanan.'),
                ]);
            }

            $request->validate([
                'logo_right' => ['nullable', 'image', 'max:2048'],
                'remove_logo_right' => ['nullable', 'boolean'],
            ]);

            if ($request->boolean('remove_logo_right') && $schoolProfile->logo_right) {
                Storage::disk('public')->delete($schoolProfile->logo_right);
                $schoolProfile->logo_right = null;
            }

            if ($request->hasFile('logo_right')) {
                if ($schoolProfile->logo_right) {
                    Storage::disk('public')->delete($schoolProfile->logo_right);
                }

                $schoolProfile->logo_right = $request->file('logo_right')->store('logos', 'public');
            }

            $schoolProfile->save();

            return back()->with('status', __('Logo kanan berhasil diperbarui.'));
        }

        if ($intent === 'leadership') {
            if (! $schoolProfile->exists) {
                return back()->withErrors([
                    'headmaster' => __('Lengkapi identitas sekolah sebelum mengelola data pimpinan.'),
                ]);
            }

            $data = $request->validate([
                'headmaster' => ['nullable', 'string', 'max:255'],
                'nip_headmaster' => ['nullable', 'string', 'max:255'],
            ]);

            $schoolProfile->fill($data);
            $schoolProfile->save();

            return back()->with('status', __('Data pimpinan berhasil diperbarui.'));
        }

        $uniqueNsmRule = Rule::unique('school_profiles', 'nsm');
        $uniqueNpsnRule = Rule::unique('school_profiles', 'npsn');
        $uniqueEmailRule = Rule::unique('school_profiles', 'email');

        if ($schoolProfile->exists) {
            $uniqueNsmRule = $uniqueNsmRule->ignore($schoolProfile->id);
            $uniqueNpsnRule = $uniqueNpsnRule->ignore($schoolProfile->id);
            $uniqueEmailRule = $uniqueEmailRule->ignore($schoolProfile->id);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nsm' => ['required', 'string', 'max:255', $uniqueNsmRule],
            'npsn' => ['required', 'string', 'max:255', $uniqueNpsnRule],
            'email' => ['required', 'email', 'max:255', $uniqueEmailRule],
            'address' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
        ]);

        $schoolProfile->fill($data);
        $schoolProfile->save();

        return back()->with('status', __('Profil sekolah berhasil diperbarui.'));
    }
}
