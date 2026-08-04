{{-- Modales de alta de marca / modelo. Requiere $marcas. --}}
<div id="marca-overlay" style="display:none;position:fixed;inset:0;z-index:1050;background:rgba(0,0,0,.6);
     align-items:flex-start;justify-content:center;padding:60px 16px;overflow:auto">
    <div class="gcard" style="width:100%;max-width:420px;margin:0">
        <div class="gcard-hd">
            <span class="gcard-title">Nueva marca</span>
            <button type="button" class="gbtn gbtn-ghost gbtn-xs" onclick="cerrarModal('marca-overlay')">✕</button>
        </div>
        <div class="gcard-bd">
            <div id="marca-error" style="display:none;padding:8px 12px;border-radius:7px;background:#1f0a0a;border:1px solid #3d1a1a;color:#e05555;font-size:13px;margin-bottom:12px"></div>
            <div class="gfg mb-0">
                <label class="glabel">Nombre de la marca *</label>
                <input type="text" id="marca-nombre" class="ginput" autocomplete="off" placeholder="Ford, Chevrolet…">
            </div>
            <div style="display:flex;gap:8px;margin-top:16px">
                <button type="button" id="marca-guardar" class="gbtn gbtn-primary" onclick="guardarMarca()">Guardar marca</button>
                <button type="button" class="gbtn gbtn-ghost" onclick="cerrarModal('marca-overlay')">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div id="modelo-overlay" style="display:none;position:fixed;inset:0;z-index:1050;background:rgba(0,0,0,.6);
     align-items:flex-start;justify-content:center;padding:60px 16px;overflow:auto">
    <div class="gcard" style="width:100%;max-width:420px;margin:0">
        <div class="gcard-hd">
            <span class="gcard-title">Nuevo modelo</span>
            <button type="button" class="gbtn gbtn-ghost gbtn-xs" onclick="cerrarModal('modelo-overlay')">✕</button>
        </div>
        <div class="gcard-bd">
            <div id="modelo-error" style="display:none;padding:8px 12px;border-radius:7px;background:#1f0a0a;border:1px solid #3d1a1a;color:#e05555;font-size:13px;margin-bottom:12px"></div>
            <div class="gfg">
                <label class="glabel">Marca *</label>
                <select id="modelo-marca" class="gselect">
                    <option value="">— Marca —</option>
                    @foreach($marcas as $m)
                        <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gfg mb-0">
                <label class="glabel">Nombre del modelo *</label>
                <input type="text" id="modelo-nombre" class="ginput" autocomplete="off" placeholder="Transit, S10…">
            </div>
            <div style="display:flex;gap:8px;margin-top:16px">
                <button type="button" id="modelo-guardar" class="gbtn gbtn-primary" onclick="guardarModelo()">Guardar modelo</button>
                <button type="button" class="gbtn gbtn-ghost" onclick="cerrarModal('modelo-overlay')">Cancelar</button>
            </div>
        </div>
    </div>
</div>
