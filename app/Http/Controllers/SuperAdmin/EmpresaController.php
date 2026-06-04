<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\BienvenidaEmpresa;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ArcaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmpresaController extends Controller
{
    /**
     * AJAX — consulta CUIT en el padrón ARCA usando las credenciales centrales.
     * GET /super-admin/consultar-cuit?cuit=XXXXXXXXXXX
     */
    public function consultarCuit(Request $request)
    {
        $cuit = preg_replace('/\D/', '', $request->get('cuit', ''));

        if (strlen($cuit) !== 11) {
            return response()->json(['error' => 'El CUIT debe tener 11 dígitos.'], 422);
        }

        try {
            $datos = (new ArcaService())->consultarPadron($cuit);
            return response()->json($datos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function index()
    {
        $empresas = Tenant::withTrashed()->with('domains')->latest()->get();
        return view('super-admin.empresas.index', compact('empresas'));
    }

    public function create()
    {
        return view('super-admin.empresas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:100',
            'nombre_fantasia' => 'nullable|string|max:100',
            'slug'            => 'required|alpha_dash|max:30|unique:tenants,id',
            'cuit'            => 'nullable|string|max:20',
            'condicion_iva'   => 'nullable|in:monotributo,responsable_inscripto,exento',
            'email'           => 'required|email|max:100',
            'telefono'        => 'nullable|string|max:30',
            'direccion'       => 'nullable|string|max:200',
        ]);

        $slug = Str::lower($request->slug);

        // ── Crear tenant → dispara CreateDatabase + MigrateDatabase (sync) ──
        $tenant = Tenant::create([
            'id'              => $slug,
            'nombre'          => $request->nombre,
            'nombre_fantasia' => $request->input('nombre_fantasia') ?: null,
            'cuit'            => $request->input('cuit')            ?: null,
            'email'           => $request->input('email'),
            'telefono'        => $request->input('telefono')        ?: null,
            'direccion'       => $request->input('direccion')       ?: null,
        ]);

        // Crear el subdominio
        $tenant->domains()->create(['domain' => $slug]);

        // ── Guardar condicion_iva en configuracion del tenant ─────────────────
        if ($request->filled('condicion_iva')) {
            $condIva = $request->condicion_iva;
            $tenant->run(fn() => \App\Models\Configuracion::set('empresa_condicion_iva', $condIva));
        }

        // ── Crear 3 usuarios en la BD del tenant ─────────────────────────────
        $usuarios   = $this->crearUsuariosTenant($tenant, $slug);
        $loginUrl   = $tenant->panelUrl() . '/login';

        // ── Enviar email de bienvenida con credenciales ───────────────────────
        $mailEnviado = false;
        try {
            Mail::to($tenant->email)
                ->send(new BienvenidaEmpresa($tenant->nombre, $loginUrl, $usuarios));
            $mailEnviado = true;
        } catch (\Exception $e) {
            Log::error('Error enviando email de bienvenida: ' . $e->getMessage());
        }

        $msg = "Empresa \"{$tenant->nombre}\" creada. BD tenant_{$slug} inicializada.";
        $msg .= $mailEnviado
            ? " Email de bienvenida enviado a {$tenant->email}."
            : " (No se pudo enviar el email — revisá la config de correo.)";

        return redirect()
            ->route('super-admin.empresas.show', $tenant->id)
            ->with('success', $msg);
    }

    /**
     * Crea admin, ventas y produccion en la BD del tenant.
     * Devuelve el array de usuarios con sus contraseñas en texto plano (solo para el email).
     */
    private function crearUsuariosTenant(Tenant $tenant, string $slug): array
    {
        $roles = [
            'admin'      => ['nombre' => 'Administrador', 'email' => $tenant->email],
            'ventas'     => ['nombre' => 'Ventas',        'email' => "ventas@{$slug}.plote.ar"],
            'produccion' => ['nombre' => 'Producción',    'email' => "produccion@{$slug}.plote.ar"],
        ];

        $resultado = [];

        $tenant->run(function () use ($roles, &$resultado) {
            foreach ($roles as $rol => $datos) {
                $password = $this->generarPassword();

                User::create([
                    'name'     => $datos['nombre'],
                    'email'    => $datos['email'],
                    'password' => Hash::make($password),
                    'rol'      => $rol,
                ]);

                $resultado[] = [
                    'rol'      => $rol,
                    'nombre'   => $datos['nombre'],
                    'email'    => $datos['email'],
                    'password' => $password,
                ];
            }
        });

        return $resultado;
    }

    /**
     * Genera una contraseña legible y segura: Xx·xxxxxx·99
     * Ejemplo: Kp·mxrqzta·47
     */
    private function generarPassword(): string
    {
        return Str::ucfirst(Str::lower(Str::random(2)))
             . Str::lower(Str::random(6))
             . rand(10, 99);
    }

    public function show(string $id)
    {
        $tenant   = Tenant::withTrashed()->with('domains')->findOrFail($id);
        $usuarios = $this->usuariosTenant($tenant->id);

        return view('super-admin.empresas.show', compact('tenant', 'usuarios'));
    }

    public function blanquearPassword(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $request->validate(['user_id' => 'required|integer']);

        $conn = $this->tenantConnection($tenant->id);

        $user = DB::connection($conn)
            ->table('users')
            ->where('id', $request->user_id)
            ->first();

        abort_if(! $user, 404);

        $password = $this->generarPassword();

        DB::connection($conn)
            ->table('users')
            ->where('id', $request->user_id)
            ->update(['password' => Hash::make($password)]);

        DB::purge($conn);

        return back()->with('password_reset', [
            'nombre'   => $user->name,
            'email'    => $user->email,
            'rol'      => $user->rol,
            'password' => $password,
        ]);
    }

    /** Consulta directa a la BD del tenant sin inicializar el contexto de tenancy. */
    private function tenantConnection(string $tenantId): string
    {
        $base = config('database.connections.mysql');
        $base['database'] = 'tenant_' . $tenantId;
        $name = 'sa_tenant_' . $tenantId;
        config(["database.connections.{$name}" => $base]);
        return $name;
    }

    private function usuariosTenant(string $tenantId): \Illuminate\Support\Collection
    {
        try {
            $conn     = $this->tenantConnection($tenantId);
            $usuarios = DB::connection($conn)
                ->table('users')
                ->orderBy('rol')
                ->get(['id', 'name', 'email', 'rol']);
            DB::purge($conn);
            return $usuarios;
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function edit(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        return view('super-admin.empresas.edit', compact('tenant'));
    }

    public function update(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $request->validate([
            'nombre'           => 'required|string|max:100',
            'nombre_fantasia'  => 'nullable|string|max:100',
            'cuit'             => 'nullable|string|max:20',
            'condicion_iva'    => 'nullable|in:monotributo,responsable_inscripto,exento',
            'email'            => 'nullable|email|max:100',
            'telefono'         => 'nullable|string|max:30',
            'direccion'        => 'nullable|string|max:200',
            'arca_cuit'        => 'nullable|string|max:20',
            'arca_punto_venta' => 'nullable|integer|min:1',
            'arca_production'  => 'nullable|boolean',
        ]);

        $tenant->update([
            'nombre'           => $request->nombre,
            'nombre_fantasia'  => $request->input('nombre_fantasia') ?: null,
            'cuit'             => $request->input('cuit')            ?: null,
            'email'            => $request->input('email')           ?: null,
            'telefono'         => $request->input('telefono')        ?: null,
            'direccion'        => $request->input('direccion')       ?: null,
            'arca_cuit'        => $request->input('arca_cuit')       ?: null,
            'arca_punto_venta' => $request->filled('arca_punto_venta')
                                    ? (int) $request->arca_punto_venta
                                    : null,
            'arca_production'  => $request->boolean('arca_production'),
        ]);

        // Guardar condicion_iva en configuracion del tenant
        if ($request->filled('condicion_iva')) {
            $condIva = $request->condicion_iva;
            $tenant->run(fn() => \App\Models\Configuracion::set('empresa_condicion_iva', $condIva));
        }

        return redirect()
            ->route('super-admin.empresas.show', $tenant->id)
            ->with('success', 'Empresa actualizada.');
    }

    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $nombre = $tenant->nombre;

        // Soft-delete del tenant (NO borra la BD inmediatamente)
        $tenant->delete();

        return redirect()
            ->route('super-admin.empresas.index')
            ->with('success', "Empresa \"{$nombre}\" desactivada.");
    }

    /**
     * Upload ARCA cert + key para un tenant.
     * Los archivos se guardan en storage/app/tenant_{id}/arca/
     */
    public function uploadCert(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $request->validate([
            'cert' => 'nullable|file|extensions:crt,cer,pem|max:2048',
            'key'  => 'nullable|file|extensions:key,pem|max:2048',
        ]);

        // Guardamos en el disco central (no en disco tenant porque ARCA se lee fuera de contexto)
        $dir = "arca/{$id}";

        if ($request->hasFile('cert')) {
            Storage::disk('local')->putFileAs($dir, $request->file('cert'), 'cert.crt');
        }

        if ($request->hasFile('key')) {
            Storage::disk('local')->putFileAs($dir, $request->file('key'), 'private.key');
        }

        // Marcar que tiene cert (clave individual → stancl la pone en data JSON)
        $tenant->update([
            'has_arca_cert' => Storage::disk('local')->exists("{$dir}/cert.crt"),
        ]);

        return back()->with('success', 'Certificados ARCA actualizados.');
    }

    /**
     * Genera una clave privada RSA 2048 + CSR para presentar en ARCA.
     * La private.key queda guardada en storage/app/arca/{id}/private.key.
     * Solo se devuelve el CSR (la clave nunca sale por esta ruta).
     */
    public function generarCsr(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $cuit = preg_replace('/\D/', '', $request->get('cuit', ''));

        if (strlen($cuit) !== 11) {
            return response()->json(['error' => 'El CUIT debe tener 11 dígitos (sin guiones).'], 422);
        }

        // Generar clave privada RSA 2048
        $privKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if (! $privKey) {
            return response()->json([
                'error' => 'Error al generar la clave: ' . (openssl_error_string() ?: 'OpenSSL no disponible.'),
            ], 500);
        }

        openssl_pkey_export($privKey, $privKeyPem);

        // Generar CSR con el DN que exige ARCA
        $dn = [
            'C'            => 'AR',
            'O'            => $tenant->nombre,
            'CN'           => 'CUIT ' . $cuit,
            'serialNumber' => 'CUIT ' . $cuit,
        ];

        $csr = openssl_csr_new($dn, $privKey, ['digest_alg' => 'sha256']);

        if (! $csr) {
            $opensslErr = '';
            while ($msg = openssl_error_string()) $opensslErr .= $msg . ' | ';
            return response()->json(['error' => 'Error al generar el CSR: ' . ($opensslErr ?: 'OpenSSL error desconocido.')], 500);
        }

        openssl_csr_export($csr, $csrPem);

        // Guardar la clave privada (nunca se expone por ninguna ruta pública)
        Storage::disk('local')->put("arca/{$id}/private.key", $privKeyPem);

        // Marcar que el tenant tiene clave generada
        $tenant->update(['has_arca_key' => true]);

        return response()->json([
            'csr'     => trim($csrPem),
            'mensaje' => 'Clave privada generada y guardada en el servidor.',
        ]);
    }

    /**
     * Descarga la private.key como archivo.
     * Solo accesible por super-admins (middleware protege la ruta).
     */
    public function descargarKey(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $path   = "arca/{$id}/private.key";

        if (! Storage::disk('local')->exists($path)) {
            return back()->with('error', 'No hay clave privada para esta empresa. Generá el CSR primero.');
        }

        // Nombre descriptivo para el archivo descargado
        $filename = "private_{$id}_" . now()->format('Ymd') . '.key';

        return Storage::disk('local')->download($path, $filename);
    }

    /**
     * Impersonar — genera un token HMAC firmado y redirige al tenant para hacer login real.
     * El browser del super-admin NUNCA usa su sesión central en el tenant.
     */
    public function impersonar(string $id)
    {
        $tenant = Tenant::with('domains')->findOrFail($id);
        $sub    = $tenant->subdomain();

        if (!$sub) {
            return back()->with('error', 'Este tenant no tiene dominio configurado.');
        }

        // Payload firmado con APP_KEY — expira en 3 minutos
        $payload = base64_encode(json_encode([
            'tid' => $tenant->id,
            'exp' => now()->addMinutes(3)->timestamp,
        ]));
        $sig = hash_hmac('sha256', $payload, config('app.key'));

        $baseUrl   = config('app.url');
        $tenantUrl = preg_replace('#(https?://)#', "$1{$sub}.", $baseUrl);

        return redirect()->away("{$tenantUrl}/sa-impersonate?t={$payload}&s={$sig}");
    }
}
