<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Dusun;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    /**
     * @var array<string, string>
     */
    private array $roles = [
        User::ROLE_ADMIN => 'Admin / Perangkat Desa',
        User::ROLE_KEPALA_DESA => 'Kepala Desa',
        User::ROLE_KEPALA_DUSUN => 'Kepala Dusun',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $query = User::with('dusun')->latest();

            if ($request->filled('q')) {
                $keyword = (string) $request->string('q');
                $query->where(function ($subQuery) use ($keyword): void {
                    $subQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            }

            if ($request->filled('role')) {
                $query->where('role', (string) $request->string('role'));
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->input('status') === 'aktif');
            }

            if ($request->filled('dusun_id')) {
                $query->where('dusun_id', $request->integer('dusun_id'));
            }

            return view('admin.users.index', [
                'users' => $query->paginate(10)->withQueryString(),
                'roles' => $this->roles,
                'dusuns' => $this->activeDusuns(),
                'filters' => [
                    'q' => $request->input('q', ''),
                    'role' => $request->input('role', ''),
                    'status' => $request->input('status', ''),
                    'dusun_id' => $request->input('dusun_id', ''),
                ],
                'totalUser' => User::count(),
                'totalAktif' => User::active()->count(),
                'totalAdmin' => User::role(User::ROLE_ADMIN)->count(),
                'totalKepalaDesa' => User::role(User::ROLE_KEPALA_DESA)->count(),
                'totalKepalaDusun' => User::role(User::ROLE_KEPALA_DUSUN)->count(),
            ]);
        } catch (Throwable $e) {
            $this->logError('USER_INDEX_FAILED', 'Gagal menampilkan data user', $e);

            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Terjadi kesalahan saat menampilkan data user. Kode Error: USER_INDEX_FAILED');
        }
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'userData' => new User(['role' => User::ROLE_KEPALA_DUSUN, 'is_active' => true]),
            'roles' => $this->roles,
            'dusuns' => $this->activeDusuns(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'dusun_id' => $data['role'] === User::ROLE_KEPALA_DUSUN ? $data['dusun_id'] : null,
                'password' => Hash::make($data['password']),
                'is_active' => $request->boolean('is_active', true),
            ]);

            Log::info('[USER_CREATED] User berhasil dibuat', [
                'actor_id' => $request->user()->id,
                'target_user_id' => $user->id,
                'role' => $user->role,
            ]);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan.');
        } catch (Throwable $e) {
            $this->logError('USER_STORE_FAILED', 'Gagal menyimpan user', $e, $request);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan user. Kode Error: USER_STORE_FAILED');
        }
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'userData' => $user->load('dusun'),
            'roles' => $this->roles,
            'dusuns' => $this->activeDusuns(),
            'isSelf' => auth()->id() === $user->id,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        try {
            $data = $request->validated();
            $isSelf = $request->user()->id === $user->id;
            $isActive = $request->boolean('is_active', false);

            if ($isSelf && ! $isActive) {
                return back()
                    ->withInput()
                    ->with('error', 'Anda tidak dapat menonaktifkan akun sendiri. Kode Error: USER_SELF_ACTION_BLOCKED');
            }

            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'dusun_id' => $data['role'] === User::ROLE_KEPALA_DUSUN ? $data['dusun_id'] : null,
                'is_active' => $isActive,
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);

            Log::info('[USER_UPDATED] User berhasil diperbarui', [
                'actor_id' => $request->user()->id,
                'target_user_id' => $user->id,
                'role' => $user->role,
            ]);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil diperbarui.');
        } catch (Throwable $e) {
            $this->logError('USER_UPDATE_FAILED', 'Gagal memperbarui user', $e, $request, $user);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui user. Kode Error: USER_UPDATE_FAILED');
        }
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        try {
            if (auth()->id() === $user->id) {
                return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri. Kode Error: USER_SELF_ACTION_BLOCKED');
            }

            $user->update(['is_active' => ! $user->is_active]);

            Log::info('[USER_STATUS_TOGGLED] Status user berhasil diubah', [
                'actor_id' => auth()->id(),
                'target_user_id' => $user->id,
                'is_active' => $user->is_active,
            ]);

            return back()->with('success', 'Status user berhasil diperbarui.');
        } catch (Throwable $e) {
            $this->logError('USER_TOGGLE_STATUS_FAILED', 'Gagal mengubah status user', $e, null, $user);

            return back()->with('error', 'Terjadi kesalahan saat mengubah status user. Kode Error: USER_TOGGLE_STATUS_FAILED');
        }
    }

    public function resetPasswordForm(User $user): View
    {
        return view('admin.users.reset-password', [
            'userData' => $user->load('dusun'),
        ]);
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        try {
            $user->update(['password' => Hash::make($data['password'])]);

            Log::info('[USER_PASSWORD_RESET] Password user berhasil direset', [
                'actor_id' => $request->user()->id,
                'target_user_id' => $user->id,
            ]);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Password user berhasil direset.');
        } catch (Throwable $e) {
            $this->logError('USER_RESET_PASSWORD_FAILED', 'Gagal reset password user', $e, $request, $user);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat reset password user. Kode Error: USER_RESET_PASSWORD_FAILED');
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        try {
            if (auth()->id() === $user->id) {
                return back()->with('error', 'Anda tidak dapat menghapus akun sendiri. Kode Error: USER_SELF_ACTION_BLOCKED');
            }

            if ($this->hasImportantHistory($user)) {
                Log::warning('[USER_DELETE_BLOCKED] User memiliki histori data', [
                    'actor_id' => auth()->id(),
                    'target_user_id' => $user->id,
                ]);

                return back()->with('error', 'User sudah memiliki histori data sehingga tidak dapat dihapus. Silakan nonaktifkan user. Kode Error: USER_DELETE_BLOCKED');
            }

            $user->delete();

            Log::info('[USER_DELETED] User berhasil dihapus', [
                'actor_id' => auth()->id(),
                'target_user_id' => $user->id,
            ]);

            return back()->with('success', 'User berhasil dihapus.');
        } catch (Throwable $e) {
            $this->logError('USER_DELETE_FAILED', 'Gagal menghapus user', $e, null, $user);

            return back()->with('error', 'Terjadi kesalahan saat menghapus user. Kode Error: USER_DELETE_FAILED');
        }
    }

    /**
     * @return Collection<int, Dusun>
     */
    private function activeDusuns()
    {
        return Dusun::aktif()
            ->orderBy('kode_alternatif')
            ->orderBy('nama_dusun')
            ->get();
    }

    private function hasImportantHistory(User $user): bool
    {
        return $user->usulanPembangunans()->exists()
            || $user->penilaianAlternatifs()->exists()
            || $user->electreCalculations()->exists()
            || $user->keputusanAkhirs()->exists();
    }

    private function logError(string $code, string $message, Throwable $e, ?Request $request = null, ?User $targetUser = null): void
    {
        Log::error("[{$code}] {$message}", [
            'actor_id' => $request?->user()?->id ?? auth()->id(),
            'target_user_id' => $targetUser?->id,
            'role' => $request?->input('role') ?? $targetUser?->role,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}
