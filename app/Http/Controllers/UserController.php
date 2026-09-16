<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Http\Requests\UserRequest;
use App\Models\Alp;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function index(Request $request): View
    {
        $this->authorize('users.view');

        $users = User::query()
            ->with(['roles', 'alp'])
            ->when($request->string('cari')->isNotEmpty(), function ($q) use ($request) {
                $term = $request->string('cari');
                $q->where(fn ($sub) => $sub
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('users.create');

        return view('users.create', [
            'roles' => $this->assignableRoles(),
            'alps' => Alp::orderBy('ref_code')->get(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->authorize('users.create');

        // Jana kata laluan sementara; pengguna wajib menukarnya semasa log masuk pertama.
        $tempPassword = $this->generateTempPassword();

        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'unit' => $request->validated('unit'),
            'alp_id' => $request->validated('alp_id'),
            'password' => Hash::make($tempPassword),
            'status' => UserStatus::ACTIVE,
            'must_change_password' => true,
            'created_by' => $request->user()->id,
        ]);

        $this->syncRole($request, $user);

        $this->audit->log('USER_CREATED', $user, null, [
            'email' => $user->email,
            'role' => $request->validated('role'),
        ]);

        return redirect()->route('users.index')
            ->with('status', "Pengguna {$user->name} berjaya dicipta.")
            ->with('temp_password', $tempPassword)
            ->with('temp_password_email', $user->email);
    }

    public function edit(User $user): View
    {
        $this->authorize('users.update');

        return view('users.edit', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
            'alps' => Alp::orderBy('ref_code')->get(),
            'currentRole' => $user->roles->first()?->name,
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('users.update');

        $user->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'unit' => $request->validated('unit'),
            'alp_id' => $request->validated('alp_id'),
        ]);

        if ($request->user()->can('users.assign_role')) {
            $this->syncRole($request, $user);
        }

        $this->audit->log('USER_UPDATED', $user, null, [
            'email' => $user->email,
            'role' => $request->validated('role'),
        ]);

        return redirect()->route('users.index')
            ->with('status', "Pengguna {$user->name} berjaya dikemas kini.");
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $this->authorize('users.deactivate');
        $this->guardSelf($request, $user);

        $user->update(['status' => UserStatus::INACTIVE]);

        $this->audit->log('USER_DEACTIVATED', $user);

        return back()->with('status', "Akaun {$user->name} telah dinyahaktifkan.");
    }

    public function activate(User $user): RedirectResponse
    {
        $this->authorize('users.deactivate');

        $user->update(['status' => UserStatus::ACTIVE]);

        $this->audit->log('USER_ACTIVATED', $user);

        return back()->with('status', "Akaun {$user->name} telah diaktifkan semula.");
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('users.update');

        $tempPassword = $this->generateTempPassword();
        $user->update([
            'password' => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);

        $this->audit->log('USER_PASSWORD_RESET', $user);

        return back()
            ->with('status', "Kata laluan {$user->name} telah ditetapkan semula.")
            ->with('temp_password', $tempPassword)
            ->with('temp_password_email', $user->email);
    }

    // ── Bantuan ─────────────────────────────────────────────────

    /** Peranan yang boleh diberikan oleh pentadbir semasa. */
    private function assignableRoles(): array
    {
        return collect(RoleName::cases())
            ->mapWithKeys(fn (RoleName $r) => [$r->value => $r->label()])
            ->all();
    }

    private function syncRole(Request $request, User $user): void
    {
        $role = $request->validated('role');
        if ($role) {
            $user->syncRoles([$role]);
        }
    }

    private function generateTempPassword(): string
    {
        // Sekurang-kurangnya satu huruf & satu nombor untuk memenuhi dasar.
        return 'Alp'.Str::upper(Str::random(3)).Str::random(4).random_int(1000, 9999);
    }

    /** Halang pentadbir menyahaktifkan akaunnya sendiri. */
    private function guardSelf(Request $request, User $user): void
    {
        abort_if($request->user()->id === $user->id, 403, 'Anda tidak boleh menyahaktifkan akaun sendiri.');
    }
}
