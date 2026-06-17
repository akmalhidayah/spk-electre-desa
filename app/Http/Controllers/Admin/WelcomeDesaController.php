<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StrukturOrganisasiDesa;
use App\Models\WelcomeDesaSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class WelcomeDesaController extends Controller
{
    public function index(): View
    {
        $setting = $this->getOrCreateSetting();

        return view('admin.welcome-desa.index', [
            'setting' => $setting,
            'strukturList' => $setting->strukturOrganisasi()
                ->orderBy('urutan')
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_desa' => ['nullable', 'string', 'max:150'],
            'kecamatan' => ['nullable', 'string', 'max:150'],
            'kabupaten' => ['nullable', 'string', 'max:150'],
            'provinsi' => ['nullable', 'string', 'max:150'],
            'alamat' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:150'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'logo_desa' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gambar_peta' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'judul_welcome' => ['nullable', 'string', 'max:255'],
            'deskripsi_welcome' => ['nullable', 'string'],
            'visi' => ['nullable', 'string'],
            'misi' => ['nullable', 'string'],
            'judul_infografis' => ['nullable', 'string', 'max:255'],
            'deskripsi_infografis' => ['nullable', 'string'],
            'maps_embed' => ['nullable', 'string'],
            'maps_link' => ['nullable', 'url'],
            'status_aktif' => ['nullable', 'boolean'],
        ]);

        try {
            $setting = $this->getOrCreateSetting();
            $validated['status_aktif'] = $request->boolean('status_aktif');
            $validated['maps_embed'] = $this->sanitizeMapsEmbed($validated['maps_embed'] ?? null);

            foreach (['logo_desa', 'hero_image', 'gambar_peta'] as $field) {
                if ($request->hasFile($field)) {
                    $this->deletePublicFile($setting->{$field});
                    $validated[$field] = $request->file($field)->store('welcome-desa', 'public');
                }
            }

            $setting->update($validated);

            return redirect()
                ->route('admin.welcome-desa.index')
                ->with('success', 'Konten landing page berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[WELCOME_DESA_UPDATE_FAILED] Gagal memperbarui welcome desa', [
                'user_id' => $request->user()?->id,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui landing page. Kode Error: WELCOME_DESA_UPDATE_FAILED');
        }
    }

    public function storeStruktur(Request $request): RedirectResponse
    {
        $validated = $this->validateStruktur($request);

        try {
            $setting = $this->getOrCreateSetting();
            $validated['welcome_desa_setting_id'] = $setting->id;
            $validated['status_aktif'] = $request->boolean('status_aktif');

            if ($request->hasFile('foto')) {
                $validated['foto'] = $request->file('foto')->store('welcome-desa/struktur', 'public');
            }

            StrukturOrganisasiDesa::create($validated);

            return redirect()
                ->route('admin.welcome-desa.index')
                ->with('success', 'Data struktur organisasi berhasil ditambahkan.');
        } catch (Throwable $e) {
            Log::error('[WELCOME_DESA_STRUKTUR_STORE_FAILED] Gagal menambah struktur organisasi', [
                'user_id' => $request->user()?->id,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambah struktur organisasi. Kode Error: WELCOME_DESA_STRUKTUR_STORE_FAILED');
        }
    }

    public function updateStruktur(Request $request, StrukturOrganisasiDesa $struktur): RedirectResponse
    {
        $validated = $this->validateStruktur($request);

        try {
            $validated['status_aktif'] = $request->boolean('status_aktif');

            if ($request->hasFile('foto')) {
                $this->deletePublicFile($struktur->foto);
                $validated['foto'] = $request->file('foto')->store('welcome-desa/struktur', 'public');
            }

            $struktur->update($validated);

            return redirect()
                ->route('admin.welcome-desa.index')
                ->with('success', 'Data struktur organisasi berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[WELCOME_DESA_STRUKTUR_UPDATE_FAILED] Gagal memperbarui struktur organisasi', [
                'user_id' => $request->user()?->id,
                'struktur_id' => $struktur->id,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui struktur organisasi. Kode Error: WELCOME_DESA_STRUKTUR_UPDATE_FAILED');
        }
    }

    public function destroyStruktur(Request $request, StrukturOrganisasiDesa $struktur): RedirectResponse
    {
        try {
            $this->deletePublicFile($struktur->foto);
            $struktur->delete();

            return redirect()
                ->route('admin.welcome-desa.index')
                ->with('success', 'Data struktur organisasi berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error('[WELCOME_DESA_STRUKTUR_DELETE_FAILED] Gagal menghapus struktur organisasi', [
                'user_id' => $request->user()?->id,
                'struktur_id' => $struktur->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menghapus struktur organisasi. Kode Error: WELCOME_DESA_STRUKTUR_DELETE_FAILED');
        }
    }

    public function toggleStrukturStatus(Request $request, StrukturOrganisasiDesa $struktur): RedirectResponse
    {
        try {
            $struktur->update([
                'status_aktif' => ! $struktur->status_aktif,
            ]);

            return back()->with('success', 'Status struktur organisasi berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[WELCOME_DESA_STRUKTUR_TOGGLE_FAILED] Gagal mengubah status struktur organisasi', [
                'user_id' => $request->user()?->id,
                'struktur_id' => $struktur->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat mengubah status struktur organisasi. Kode Error: WELCOME_DESA_STRUKTUR_TOGGLE_FAILED');
        }
    }

    private function getOrCreateSetting(): WelcomeDesaSetting
    {
        return WelcomeDesaSetting::query()->firstOrCreate([], [
            'nama_desa' => 'Desa Barambang',
            'kecamatan' => 'Kecamatan Sinjai Borong',
            'kabupaten' => 'Kabupaten Sinjai',
            'provinsi' => 'Sulawesi Selatan',
            'judul_welcome' => 'Selamat Datang di Website Resmi Desa Barambang',
            'deskripsi_welcome' => 'Sistem informasi desa dan pendukung keputusan prioritas pembangunan antar dusun.',
            'visi' => 'Terwujudnya desa yang maju, transparan, partisipatif, dan berbasis data dalam pembangunan.',
            'misi' => "Meningkatkan pelayanan publik desa.\nMendorong partisipasi masyarakat dalam pembangunan.\nMengelola data desa secara transparan dan akuntabel.",
            'judul_infografis' => 'Infografis Desa',
            'deskripsi_infografis' => 'Informasi wilayah dan peta desa.',
            'status_aktif' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateStruktur(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jabatan' => ['required', 'string', 'max:150'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'deskripsi' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer'],
            'status_aktif' => ['nullable', 'boolean'],
        ]);
    }

    private function sanitizeMapsEmbed(?string $embed): ?string
    {
        if (! $embed) {
            return null;
        }

        if (! preg_match('/<iframe\b[^>]*\bsrc=["\']([^"\']+)["\'][^>]*><\/iframe>/i', $embed, $matches)) {
            return null;
        }

        $src = $matches[1];
        $host = parse_url($src, PHP_URL_HOST);

        if (! is_string($host) || ! str_ends_with(strtolower($host), 'google.com')) {
            return null;
        }

        $safeSrc = e($src);

        return '<iframe src="'.$safeSrc.'" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>';
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
