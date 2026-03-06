<div
    x-data="pizarra(@js($items))"
    x-init="init()"
    wire:ignore
    class="pizarra-root"
    :class="{ 'is-dragging': dragging, 'is-panning': isPanning }"
    style="width:100%; height:100%; position:relative; overflow:hidden; background:#1e1e1e; font-family: inherit;"
    @mousemove.window="onMousemove($event)"
    @mouseup.window="onMouseup($event)"
>

    {{-- ═══════════════════════════════════════════
         ESTILOS (scoped con .pizarra-root)
    ═══════════════════════════════════════════ --}}
    <style>
        .pizarra-root { user-select: none; }
        .pizarra-root.is-dragging, .pizarra-root.is-panning { cursor: grabbing !important; }
        .pizarra-root.is-dragging *, .pizarra-root.is-panning * { cursor: grabbing !important; }
        .pizarra-root *, .pizarra-root *::before, .pizarra-root *::after { box-sizing: border-box; }

        /* CANVAS */
        .pizarra-root #pizarra-canvas {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            cursor: default;
        }

        /* TOOLBAR */
        .pizarra-root .pz-toolbar {
            position: absolute; bottom: 16px; right: 16px;
            display: flex; gap: 4px; align-items: center;
            background: rgba(37,37,38,0.95);
            border: 1px solid #333;
            border-radius: 8px; padding: 4px 10px;
            backdrop-filter: blur(12px);
            z-index: 100;
            box-shadow: 0 4px 24px rgba(0,0,0,0.5);
        }
        .pizarra-root .pz-toolbar button {
            background: none; border: none; cursor: pointer;
            color: #7b7b7b; font-size: 12px; padding: 4px 8px;
            border-radius: 5px; transition: all .15s;
            font-family: inherit;
            display: flex; align-items: center; gap: 4px;
        }
        .pizarra-root .pz-toolbar button:hover { background: #333; color: #d4d4d4; }
        .pizarra-root .pz-toolbar button.active { background: rgba(0,127,212,0.2); color: #007fd4; }
        .pizarra-root .pz-toolbar .sep { width: 1px; height: 20px; background: #333; margin: 0 2px; }
        .pizarra-root .pz-zoom-label { color: #7b7b7b; font-size: 11px; min-width: 40px; text-align: center; font-family: monospace; }

        /* CAJAS */
        .pizarra-root .pz-item {
            position: absolute;
            border-radius: 6px;
            border: 1px solid #333;
            background: #252526;
            box-shadow: 0 2px 16px rgba(0,0,0,0.4);
            cursor: grab;
            transition: box-shadow .15s, border-color .15s;
            display: flex; flex-direction: column;
            overflow: hidden;
        }
        .pizarra-root .pz-item:hover { box-shadow: 0 4px 28px rgba(0,0,0,0.6); border-color: #444; }
        .pizarra-root .pz-item.selected { border-color: #007fd4 !important; box-shadow: 0 0 0 2px rgba(0,127,212,0.25), 0 4px 28px rgba(0,0,0,0.6); }
        .pizarra-root .pz-item.connecting-source { border-color: #d29922 !important; box-shadow: 0 0 0 2px rgba(210,153,34,0.2); }
        .pizarra-root .pz-item-header {
            padding: 8px 10px 6px;
            display: flex; align-items: center; gap: 6px;
            border-bottom: 1px solid #333;
            cursor: grab;
        }
        .pizarra-root .pz-item-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .pizarra-root .pz-item-title {
            flex: 1; font-size: 12px; font-weight: 600;
            color: #d4d4d4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .pizarra-root .pz-item-actions { display: flex; gap: 2px; opacity: 0; transition: opacity .15s; }
        .pizarra-root .pz-item:hover .pz-item-actions { opacity: 1; }
        .pizarra-root .pz-item-actions button {
            background: none; border: none; cursor: pointer;
            color: #7b7b7b; font-size: 11px; padding: 2px 4px;
            border-radius: 4px; transition: all .12s;
        }
        .pizarra-root .pz-item-actions button:hover { background: #333; color: #d4d4d4; }
        .pizarra-root .pz-item-actions button.connect-btn:hover { color: #d29922; }
        .pizarra-root .pz-item-body { flex: 1; padding: 8px 10px; overflow: hidden; }
        .pizarra-root .pz-item-notes { font-size: 11px; color: #7b7b7b; line-height: 1.5; margin-bottom: 6px; }
        .pizarra-root .pz-subtask {
            display: flex; align-items: center; gap: 6px;
            padding: 2px 0; font-size: 11px; color: #8b949e;
        }
        .pizarra-root .pz-subtask input[type=checkbox] { accent-color: #007fd4; width: 11px; height: 11px; cursor: pointer; }
        .pizarra-root .pz-subtask.done span { text-decoration: line-through; color: #555; }

        /* PANEL LATERAL */
        .pizarra-root .pz-panel {
            position: absolute; top: 0; right: 0; bottom: 0; width: 280px;
            background: rgba(30,30,30,0.98);
            border-left: 1px solid #333;
            backdrop-filter: blur(16px);
            z-index: 200;
            display: flex; flex-direction: column;
            transform: translateX(100%);
            transition: transform .2s cubic-bezier(.4,0,.2,1);
        }
        .pizarra-root .pz-save-btn {
            background: rgba(0,127,212,0.12); border: 1px solid rgba(0,127,212,0.3);
            color: #007fd4; font-size: 11px; font-family: inherit;
            padding: 7px 12px; border-radius: 5px; cursor: pointer;
            transition: all .15s; width: 100%;
        }
        .pizarra-root .pz-save-btn:hover { background: rgba(0,127,212,0.25); border-color: #007fd4; }
        .pizarra-root .pz-panel.open { transform: translateX(0); }
        .pizarra-root .pz-panel-header {
            padding: 16px; border-bottom: 1px solid #333;
            display: flex; align-items: center; gap: 8px;
        }
        .pizarra-root .pz-panel-dot { width: 10px; height: 10px; border-radius: 50%; }
        .pizarra-root .pz-panel-title { flex: 1; font-weight: 600; font-size: 14px; color: #d4d4d4; }
        .pizarra-root .pz-panel-close {
            background: none; border: none; cursor: pointer;
            color: #7b7b7b; font-size: 16px; padding: 2px 6px; border-radius: 5px;
            transition: all .12s;
        }
        .pizarra-root .pz-panel-close:hover { background: #333; color: #d4d4d4; }
        .pizarra-root .pz-panel-body { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 14px; }
        .pizarra-root .pz-panel-body::-webkit-scrollbar { width: 10px; background-color: #1e1e1e; }
        .pizarra-root .pz-panel-body::-webkit-scrollbar-thumb { background-color: #424242; border-radius: 5px; border: 2px solid #1e1e1e; }
        .pizarra-root .pz-panel-body::-webkit-scrollbar-thumb:hover { background-color: #4f4f4f; }

        .pizarra-root .pz-field label { display: block; font-size: 10px; color: #7b7b7b; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
        .pizarra-root .pz-field input[type=text], .pizarra-root .pz-field textarea {
            width: 100%; background: #3c3c3c;
            border: 1px solid #333; border-radius: 5px;
            color: #d4d4d4; font-size: 12px; font-family: inherit;
            padding: 7px 10px; outline: none; resize: none; transition: border-color .15s;
        }
        .pizarra-root .pz-field input[type=text]:focus, .pizarra-root .pz-field textarea:focus { border-color: #007fd4; box-shadow: 0 0 0 1px #007fd4; }

        .pizarra-root .pz-colors { display: flex; gap: 6px; flex-wrap: wrap; }
        .pizarra-root .pz-color-opt {
            width: 22px; height: 22px; border-radius: 50%; cursor: pointer;
            border: 2px solid transparent; transition: all .12s;
        }
        .pizarra-root .pz-color-opt:hover, .pizarra-root .pz-color-opt.active { border-color: #d4d4d4; transform: scale(1.15); }

        .pizarra-root .pz-subtasks-list { display: flex; flex-direction: column; gap: 4px; }
        .pizarra-root .pz-subtask-row {
            display: flex; align-items: center; gap: 6px;
            padding: 4px 6px; border-radius: 5px;
            transition: background .12s;
        }
        .pizarra-root .pz-subtask-row:hover { background: #333; }
        .pizarra-root .pz-subtask-row input[type=checkbox] { accent-color: #007fd4; cursor: pointer; }
        .pizarra-root .pz-subtask-row span { flex: 1; font-size: 11px; color: #8b949e; }
        .pizarra-root .pz-subtask-row.done span { text-decoration: line-through; color: #555; }
        .pizarra-root .pz-subtask-del {
            background: none; border: none; cursor: pointer;
            color: #555; font-size: 12px; padding: 1px 4px;
            border-radius: 3px; transition: all .12s; opacity: 0;
        }
        .pizarra-root .pz-subtask-row:hover .pz-subtask-del { opacity: 1; }
        .pizarra-root .pz-subtask-del:hover { background: #3b1219; color: #f85149; }

        .pizarra-root .pz-add-subtask { display: flex; gap: 6px; margin-top: 4px; }
        .pizarra-root .pz-add-subtask input {
            flex: 1; background: #3c3c3c;
            border: 1px solid #333; border-radius: 5px;
            color: #d4d4d4; font-size: 11px; font-family: inherit;
            padding: 5px 8px; outline: none;
        }
        .pizarra-root .pz-add-subtask input:focus { border-color: #007fd4; }
        .pizarra-root .pz-add-subtask button {
            background: rgba(0,127,212,0.15); border: none; cursor: pointer;
            color: #007fd4; font-size: 14px; padding: 5px 10px;
            border-radius: 5px; transition: all .12s;
        }
        .pizarra-root .pz-add-subtask button:hover { background: rgba(0,127,212,0.3); }

        .pizarra-root .pz-danger-btn {
            background: #3b1219; border: 1px solid rgba(218,54,51,0.3);
            color: #f85149; font-size: 11px; font-family: inherit;
            padding: 7px 12px; border-radius: 5px; cursor: pointer;
            transition: all .15s; width: 100%;
        }
        .pizarra-root .pz-danger-btn:hover { background: rgba(218,54,51,0.25); border-color: #da3633; }

        /* MENU CONTEXTUAL */
        .pizarra-root .pz-context-menu {
            position: fixed; z-index: 999;
            background: #252526;
            border: 1px solid #333;
            border-radius: 6px; padding: 4px;
            min-width: 160px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.6);
        }
        .pizarra-root .pz-context-item {
            display: flex; align-items: center; gap: 8px;
            padding: 7px 10px; border-radius: 4px;
            font-size: 12px; color: #d4d4d4; cursor: pointer;
            transition: all .1s;
        }
        .pizarra-root .pz-context-item:hover { background: #094771; color: white; }
        .pizarra-root .pz-context-item.danger { color: #f85149; }
        .pizarra-root .pz-context-item.danger:hover { background: #3b1219; color: #f85149; }
        .pizarra-root .pz-context-sep { height: 1px; background: #333; margin: 4px 0; }

        /* HINT */
        .pizarra-root .pz-hint {
            position: absolute; bottom: 16px; left: 16px;
            font-size: 10px; color: #555;
            pointer-events: none; white-space: nowrap;
            font-family: monospace;
        }

        /* MODO CONEXION */
        .pizarra-root .pz-connecting-cursor { cursor: crosshair !important; }
        .pizarra-root .pz-connect-badge {
            position: absolute; top: 8px; left: 50%; transform: translateX(-50%);
            background: #362808; border: 1px solid #9e6a03;
            color: #d29922; font-size: 11px; padding: 4px 12px; border-radius: 20px;
            z-index: 150; pointer-events: none; font-family: monospace;
        }
    </style>

    {{-- ═══════════════════════════════════════════
         TOOLBAR
    ═══════════════════════════════════════════ --}}
    <div class="pz-toolbar">
        <button @click="addItemCenter()" title="Nueva caja">＋ Caja</button>
        <div class="sep"></div>
        <button @click="zoom(-0.1)" title="Alejar">−</button>
        <span class="pz-zoom-label" x-text="Math.round(scale*100)+'%'"></span>
        <button @click="zoom(0.1)" title="Acercar">＋</button>
        <button @click="resetView()" title="Resetear vista">⊙</button>
        <div class="sep"></div>
        <button :class="connectMode ? 'active' : ''" @click="toggleConnectMode()">
            ⟶ Conectar
        </button>
    </div>

    {{-- BADGE modo conexión --}}
    <div class="pz-connect-badge" x-show="connectMode" x-cloak>
        Seleccioná la caja destino · ESC para cancelar
    </div>

    {{-- ═══════════════════════════════════════════
         CANVAS SVG + CAJAS
    ═══════════════════════════════════════════ --}}
    <svg
        id="pizarra-canvas"
        @mousedown="onCanvasMousedown($event)"
        @mousedown.middle.prevent=""
        @wheel.prevent="onWheel($event)"
        @dblclick="onCanvasDblclick($event)"
        @contextmenu.prevent=""
    >
        {{-- Grid de fondo --}}
        <defs>
            <pattern id="pz-grid" width="32" height="32" patternUnits="userSpaceOnUse"
                :patternTransform="`translate(${panX % 32} ${panY % 32}) scale(${scale})`">
                <path d="M 32 0 L 0 0 0 32" fill="none" stroke="rgba(255,255,255,0.04)" stroke-width="0.5"/>
            </pattern>
            <marker id="pz-arrow" markerWidth="8" markerHeight="8" refX="6" refY="3" orient="auto">
                <path d="M0,0 L0,6 L8,3 z" fill="rgba(0,127,212,0.7)"/>
            </marker>
            <marker id="pz-arrow-hover" markerWidth="8" markerHeight="8" refX="6" refY="3" orient="auto">
                <path d="M0,0 L0,6 L8,3 z" fill="#007fd4"/>
            </marker>
        </defs>
        <rect width="100%" height="100%" fill="url(#pz-grid)"/>

        {{-- Conexiones (renderizadas programáticamente porque Alpine x-for no funciona dentro de SVG) --}}
        <g :transform="`translate(${panX},${panY}) scale(${scale})`"
           id="pz-connections-layer"
           x-effect="renderConnections()"
        ></g>

        {{-- Línea de conexión en progreso --}}
        <g :transform="`translate(${panX},${panY}) scale(${scale})`">
            <line
                x-show="connectMode && connectSource && tempLine"
                :x1="tempLine ? tempLine.x1 : 0"
                :y1="tempLine ? tempLine.y1 : 0"
                :x2="tempLine ? tempLine.x2 : 0"
                :y2="tempLine ? tempLine.y2 : 0"
                stroke="rgba(210,153,34,0.6)"
                stroke-width="1.5"
                stroke-dasharray="6,4"
            />
        </g>
    </svg>

    {{-- ═══════════════════════════════════════════
         CAJAS (HTML sobre el SVG)
    ═══════════════════════════════════════════ --}}
    <div
        style="position:absolute;inset:0;pointer-events:none;"
        id="pz-items-layer"
    >
        <template x-for="item in items" :key="item.id">
            <div
                class="pz-item"
                :class="{
                    'selected': selectedId === item.id,
                    'connecting-source': connectSource && connectSource.id === item.id
                }"
                :style="{
                    left: (panX + item.pos_x * scale) + 'px',
                    top:  (panY + item.pos_y * scale) + 'px',
                    width:  (item.width  * scale) + 'px',
                    minHeight: Math.max(70, item.height) * scale + 'px',
                    height: 'auto',
                    borderColor: item.color + '55',
                    pointerEvents: 'all',
                    minWidth: '120px',
                    zIndex: item.z_index || 0,
                }"
                @mousedown.stop="onItemMousedown($event, item)"
                @dblclick.stop="openPanel(item)"
                @contextmenu.prevent="onItemRightclick($event, item)"
                @click.stop="onItemClick($event, item)"
            >
                {{-- Header --}}
                <div class="pz-item-header">
                    <div class="pz-item-dot" :style="{ background: item.color }"></div>
                    <div class="pz-item-title" x-text="item.title"></div>
                    <div class="pz-item-actions">
                        <button class="connect-btn" title="Conectar" @click.stop="startConnect(item)">⟶</button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="pz-item-body" :style="{ fontSize: Math.max(9, 11 * scale) + 'px' }">
                    <div class="pz-item-notes" x-show="item.notes" x-text="item.notes"></div>
                    <template x-for="st in (item.subtasks || [])" :key="st.id">
                        <div class="pz-subtask" :class="{ done: st.is_completed }">
                            <input type="checkbox" :checked="st.is_completed"
                                @change.stop="$wire.toggleSubtask(st.id).then(() => $wire.loadItems().then(d => items = d))"
                            />
                            <span x-text="st.title"></span>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- ═══════════════════════════════════════════
         PANEL DE EDICIÓN
    ═══════════════════════════════════════════ --}}
    <div class="pz-panel" :class="{ open: panelOpen }"
        
    >
        <template x-if="editItem">
            <div style="display:flex;flex-direction:column;height:100%;">
                <div class="pz-panel-header">
                    <div class="pz-panel-dot" :style="{ background: editItem.color }"></div>
                    <div class="pz-panel-title" x-text="editItem.title || 'Sin título'"></div>
                    <button class="pz-panel-close" @click="closePanel()">✕</button>
                </div>

                <div class="pz-panel-body">

                    {{-- Título --}}
                    <div class="pz-field">
                        <label>Título</label>
                        <input type="text" x-model="editItem.title"
                            @keydown.enter.stop="saveField('title', editItem.title); $el.blur()"
                            @keydown.escape="closePanel()"
                            placeholder="Nombre de la caja..."
                        />
                    </div>

                    {{-- Notas --}}
                    <div class="pz-field">
                        <label>Notas</label>
                        <textarea rows="4" x-model="editItem.notes"
                            @keydown.ctrl.enter="saveField('notes', editItem.notes); $el.blur()"
                            @keydown.escape="closePanel()"
                            placeholder="Descripción, ideas, links..."
                        ></textarea>
                    </div>

                    {{-- Color --}}
                    <div class="pz-field">
                        <label>Color</label>
                        <div class="pz-colors">
                            <template x-for="c in colors" :key="c">
                                <div class="pz-color-opt"
                                    :class="{ active: editItem.color === c }"
                                    :style="{ background: c }"
                                    @click="editItem.color = c"
                                ></div>
                            </template>
                        </div>
                    </div>

                    {{-- Subtareas --}}
                    <div class="pz-field">
                        <label>Subtareas</label>
                        <div class="pz-subtasks-list">
                            <template x-for="st in (editItem.subtasks || [])" :key="st.id">
                                <div class="pz-subtask-row" :class="{ done: st.is_completed }">
                                    <input type="checkbox" :checked="st.is_completed"
                                        @change="toggleSubtaskPanel(st)"
                                    />
                                    <span x-text="st.title"></span>
                                    <button class="pz-subtask-del" @click="deleteSubtaskPanel(st.id)">✕</button>
                                </div>
                            </template>
                        </div>
                        <div class="pz-add-subtask">
                            <input type="text" x-model="newSubtaskTitle"
                                placeholder="Nueva subtarea..."
                                @keydown.enter="addSubtaskPanel()"
                            />
                            <button @click="addSubtaskPanel()">＋</button>
                        </div>
                    </div>
                    {{-- Guardar --}}
                    <button class="pz-save-btn" @click.stop="saveAllPanelFields()">
                        ✓ Guardar caja
                    </button>
                    {{-- Eliminar --}}
                    <button class="pz-danger-btn" @click="deleteItemPanel()">
                        ✕ Eliminar caja
                    </button>

                </div>
            </div>
        </template>
    </div>

    {{-- ═══════════════════════════════════════════
         MENÚ CONTEXTUAL
    ═══════════════════════════════════════════ --}}
    <div
        class="pz-context-menu"
        x-show="contextMenu.visible"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }"
        @click.outside="contextMenu.visible = false"
    >
        <template x-if="contextMenu.type === 'item'">
            <div>
                <div class="pz-context-item" @click="openPanel(contextMenu.target); contextMenu.visible=false">
                    ✏️ Editar
                </div>
                <div class="pz-context-item" @click="openPeriodSelector(contextMenu.target); contextMenu.visible=false">
                    📋 Agregar a semana
                </div>
                <div class="pz-context-item" @click="startConnect(contextMenu.target); contextMenu.visible=false">
                    ⟶ Conectar
                </div>
                <div class="pz-context-sep"></div>
                
                {{-- Submenú de Ordenar --}}
                <div class="pz-context-item group relative">
                    ↕️ Ordenar <span style="margin-left: auto; font-size: 10px;">▶</span>
                    <div class="absolute left-[95%] top-0 hidden group-hover:block bg-[#252526] border border-[#333] rounded-md py-1 w-40 z-[300] shadow-xl">
                        <div class="pz-context-item" @click.stop="$wire.bringToFront(contextMenu.target.id).then(() => $wire.loadItems().then(d => items = d)); contextMenu.visible=false">
                            ⬆️ Traer al frente
                        </div>
                        <div class="pz-context-item" @click.stop="$wire.sendToBack(contextMenu.target.id).then(() => $wire.loadItems().then(d => items = d)); contextMenu.visible=false">
                            ⬇️ Enviar al fondo
                        </div>
                    </div>
                </div>

                <div class="pz-context-sep"></div>
                <div class="pz-colors" style="padding: 6px 8px; gap:5px;">
                    <template x-for="c in colors" :key="c">
                        <div class="pz-color-opt"
                            :style="{ background: c, width:'18px', height:'18px' }"
                            @click="quickColor(contextMenu.target, c)"
                        ></div>
                    </template>
                </div>
                <div class="pz-context-sep"></div>
                <div class="pz-context-item danger" @click="quickDelete(contextMenu.target); contextMenu.visible=false">
                    ✕ Eliminar
                </div>
            </div>
        </template>
        <template x-if="contextMenu.type === 'connection'">
            <div>
                <div class="pz-context-item danger" @click="quickDeleteConnection(contextMenu.target); contextMenu.visible=false">
                    ✕ Eliminar conexión
                </div>
            </div>
        </template>
    </div>

    {{-- ═══════════════════════════════════════════
         MODAL SELECCION DE PERIODO
    ═══════════════════════════════════════════ --}}
    <div x-show="periodModal.visible" x-cloak class="fixed inset-0 bg-black/50 z-[200] flex items-center justify-center p-4">
        <div class="bg-[#252526] border border-[#333] rounded-lg shadow-2xl w-full max-w-sm overflow-hidden" @click.outside="periodModal.visible = false">
            <div class="px-4 py-3 border-b border-[#333] flex justify-between items-center">
                <h3 class="text-[#d4d4d4] font-medium text-sm">Agregar a semana</h3>
                <button @click="periodModal.visible = false" class="text-[#7b7b7b] hover:text-white">✕</button>
            </div>
            <div class="p-2 max-h-64 overflow-y-auto custom-scrollbar">
                <template x-if="periodModal.loading">
                    <div class="text-center py-4 text-[#7b7b7b] text-sm">Cargando semanas...</div>
                </template>
                <template x-if="!periodModal.loading && periodModal.periods.length === 0">
                    <div class="text-center py-4 text-[#7b7b7b] text-sm">No hay semanas activas</div>
                </template>
                <template x-for="p in periodModal.periods" :key="p.id">
                    <button @click="promoteToPeriod(p.id)" class="w-full text-left px-3 py-2 rounded mb-1 hover:bg-[#333] transition-colors flex flex-col group">
                        <span class="text-[#d4d4d4] text-sm font-medium group-hover:text-white" x-text="p.name || 'Semana sin nombre'"></span>
                        <span class="text-[#7b7b7b] text-xs font-mono mt-0.5" x-text="(p.start_date || '').substring(5,10) + ' - ' + (p.end_date || '').substring(5,10)"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- HINT --}}
    <div class="pz-hint">doble click para crear · click derecho para opciones · scroll para zoom</div>

    {{-- ═══════════════════════════════════════════
         ALPINE COMPONENT
    ═══════════════════════════════════════════ --}}
    @script
    <script>
    Alpine.data('pizarra', (initialItems) => ({
        // Estado canvas
        items: initialItems || [],
        scale: 1,
        panX: 0,
        panY: 0,
        isPanning: false,
        panStart: { x: 0, y: 0 },

        // Drag cajas
        dragging: null,
        dragStart: { x: 0, y: 0 },
        itemStart: { x: 0, y: 0 },
        hasDragged: false,

        // Panel
        panelOpen: false,
        editItem: null,
        selectedId: null,
        newSubtaskTitle: '',

        // Conexiones
        connectMode: false,
        connectSource: null,
        tempLine: null,
        undoStack: [],

        // Menú contextual
        contextMenu: { visible: false, x: 0, y: 0, type: null, target: null },

        // Modal de periodos
        periodModal: { visible: false, targetItem: null, periods: [], loading: false },

        // UX Drag
        currentDropZone: null,

        // Colores disponibles
        colors: ['#007fd4','#569cd6','#8B5CF6','#EC4899','#4ec9b0','#F59E0B','#f85149','#06B6D4','#b5cea8','#6a9955'],

        // ─── INIT ───────────────────────────────
        init() {
            document.addEventListener('keydown', (e) => {
                // Escape — cerrar panel / cancelar modo conexión
                if (e.key === 'Escape') {
                    this.connectMode = false;
                    this.connectSource = null;
                    this.tempLine = null;
                    this.contextMenu.visible = false;
                    this.periodModal.visible = false;
                    if (this.panelOpen) {
                        this.closePanel();
                    }
                }
            
                // Ctrl+Z — undo
                if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
                    e.preventDefault();
                    this.undo();
                }
            
                // Enter sin campo activo — guardar cambios del panel
                if (e.key === 'Enter' && this.panelOpen && this.editItem) {
                    const tag = document.activeElement?.tagName;
                    const isEditing = tag === 'INPUT' || tag === 'TEXTAREA';
                    if (!isEditing) {
                        e.preventDefault();
                        this.saveAllPanelFields();
                    }
                }
            });
        },

        // ─── COMPUTED ────────────────────────────
        get allConnections() {
            const conns = [];
            this.items.forEach(item => {
                (item.connections_from || []).forEach(c => conns.push(c));
            });
            return conns;
        },

        // ─── RENDER CONEXIONES (programático, Alpine x-for no funciona en SVG) ────
        renderConnections() {
            const layer = document.getElementById('pz-connections-layer');
            if (!layer) return;
            const conns = this.allConnections;
            const ns = 'http://www.w3.org/2000/svg';

            // Limpiar
            layer.innerHTML = '';

            conns.forEach(conn => {
                const pathD = this.getConnectionPath(conn);
                if (!pathD) return;

                // Color de la caja origen
                const fromItem = this.items.find(i => i.id == conn.from_item_id);
                const color = fromItem ? fromItem.color : '#007fd4';

                // Hitbox invisible (más ancho para facilitar click derecho)
                const hitbox = document.createElementNS(ns, 'path');
                hitbox.setAttribute('d', pathD);
                hitbox.setAttribute('fill', 'none');
                hitbox.setAttribute('stroke', 'transparent');
                hitbox.setAttribute('stroke-width', '12');
                hitbox.style.cursor = 'pointer';
                hitbox.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    this.onConnectionRightclick(e, conn);
                });

                // Línea visible con color de origen
                const path = document.createElementNS(ns, 'path');
                path.setAttribute('d', pathD);
                path.setAttribute('fill', 'none');
                path.setAttribute('stroke', color);
                path.setAttribute('stroke-opacity', '0.6');
                path.setAttribute('stroke-width', '1.5');
                path.style.cursor = 'pointer';
                path.style.transition = 'stroke-opacity .15s';
                path.addEventListener('mouseenter', () => {
                    path.setAttribute('stroke-opacity', '1');
                });
                path.addEventListener('mouseleave', () => {
                    path.setAttribute('stroke-opacity', '0.6');
                });
                path.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    this.onConnectionRightclick(e, conn);
                });

                // Flecha (triángulo) en el punto final con color de origen
                const endPoint = this.getConnectionEndpoint(conn);
                if (endPoint) {
                    const arrow = document.createElementNS(ns, 'polygon');
                    const { x, y, angle } = endPoint;
                    const size = 7;
                    const a1 = angle + Math.PI * 0.8;
                    const a2 = angle - Math.PI * 0.8;
                    const points = `${x},${y} ${x + size * Math.cos(a1)},${y + size * Math.sin(a1)} ${x + size * Math.cos(a2)},${y + size * Math.sin(a2)}`;
                    arrow.setAttribute('points', points);
                    arrow.setAttribute('fill', color);
                    layer.appendChild(arrow);
                }

                layer.appendChild(hitbox);
                layer.appendChild(path);
            });
        },

        // ─── CANVAS EVENTS ───────────────────────
        onCanvasMousedown(e) {
            // Pan solo con botón medio (ruedita)
            if (e.button === 1) {
                e.preventDefault();
                this.contextMenu.visible = false;
                this.isPanning = true;
                this.panStart = { x: e.clientX - this.panX, y: e.clientY - this.panY };
            }
        },

        onMousemove(e) {
            if (this.isPanning && !this.dragging) {
                this.panX = e.clientX - this.panStart.x;
                this.panY = e.clientY - this.panStart.y;
            }
            if (this.dragging) {
                this.hasDragged = true;
                const dx = (e.clientX - this.dragStart.x) / this.scale;
                const dy = (e.clientY - this.dragStart.y) / this.scale;
                this.dragging.pos_x = this.itemStart.x + dx;
                this.dragging.pos_y = this.itemStart.y + dy;

                // Opacidad si está sobre el sidebar y desactivar eventos para elementFromPoint
                const el = document.getElementById('pz-box-' + this.dragging.id);
                if (el && el.style.pointerEvents !== 'none') {
                    el.style.pointerEvents = 'none'; // Clave para que elementFromPoint vea lo que hay debajo
                }

                // Optimización: solo procesar si el mouse está cerca del sidebar (mitad izquierda)
                let dropZone = null;
                if (e.clientX < 400) {
                    const elUnderMouse = document.elementFromPoint(e.clientX, e.clientY);
                    dropZone = elUnderMouse ? elUnderMouse.closest('.period-drop-zone') : null;
                }

                // Solo modificar el DOM si cambiamos de zona
                if (this.currentDropZone !== dropZone) {
                    if (this.currentDropZone) {
                        this.currentDropZone.classList.remove('ring-2', 'ring-[#007fd4]', 'bg-[#2a2d2e]');
                    }
                    if (dropZone) {
                        dropZone.classList.add('ring-2', 'ring-[#007fd4]', 'bg-[#2a2d2e]');
                    }
                    this.currentDropZone = dropZone;

                    if (el) {
                        el.style.opacity = dropZone ? '0.4' : '1';
                    }
                }
            }
            if (this.connectMode && this.connectSource) {
                const svgRect = document.getElementById('pizarra-canvas').getBoundingClientRect();
                const mx = (e.clientX - svgRect.left - this.panX) / this.scale;
                const my = (e.clientY - svgRect.top  - this.panY) / this.scale;
                const cx = this.connectSource.pos_x + this.connectSource.width  / 2;
                const cy = this.connectSource.pos_y + this.connectSource.height / 2;
                this.tempLine = { x1: cx, y1: cy, x2: mx, y2: my };
            }
        },

        onMouseup(e) {
            if (this.dragging && this.hasDragged) {
                // El destino es la currentDropZone
                const dropZone = this.currentDropZone;
                
                // Limpiar highlights siempre
                if (this.currentDropZone) {
                    this.currentDropZone.classList.remove('ring-2', 'ring-[#007fd4]', 'bg-[#2a2d2e]');
                    this.currentDropZone = null;
                }

                if (dropZone) {
                    const periodId = dropZone.getAttribute('data-period-id');
                    if (periodId) {
                        this.$wire.promoteToTask(this.dragging.id, periodId).then(() => {
                            this.reload();
                        });
                    }
                } else {
                    // Si no cayó en periodo, guardar posición en canvas
                    this.savePosition(this.dragging);
                }

                // Restaurar estilos si quedó pegada
                const el = document.getElementById('pz-box-' + this.dragging.id);
                if (el) {
                    el.style.opacity = '';
                    el.style.pointerEvents = '';
                }
            }
            this.isPanning = false;
            this.dragging = null;
            this.hasDragged = false;
        },

        onCanvasDblclick(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            const x = (e.clientX - rect.left - this.panX) / this.scale;
            const y = (e.clientY - rect.top  - this.panY) / this.scale;
            this.$wire.addItem(x, y).then(() => this.reload());
        },

        onWheel(e) {
            const delta = e.deltaY > 0 ? -0.08 : 0.08;
            this.zoom(delta, e.clientX, e.clientY);
        },

        // ─── ITEM EVENTS ─────────────────────────
        onItemMousedown(e, item) {
            if (e.button !== 0) return;
            if (this.connectMode) return;
            e.stopPropagation();
            this.dragging = item;
            this.hasDragged = false;
            this.dragStart = { x: e.clientX, y: e.clientY };
            this.itemStart = { x: item.pos_x, y: item.pos_y };
        },

        onItemClick(e, item) {
            if (this.connectMode) {
                if (!this.connectSource) {
                    // Primer click: seleccionar caja origen
                    this.connectSource = item;
                    return;
                }
                if (this.connectSource.id !== item.id) {
                    // Segundo click: crear conexión
                    this.$wire.addConnection(this.connectSource.id, item.id, 'depends_start')
                        .then(() => this.reload().then(() => {
                            // Buscar la conexión recién creada y pushear al undo stack
                            const conns = this.allConnections;
                            if (conns.length) {
                                this.undoStack.push({ type: 'connection', id: conns[conns.length - 1].id });
                            }
                        }));
                }
                this.connectMode = false;
                this.connectSource = null;
                this.tempLine = null;
                return;
            }
            if (!this.hasDragged) {
                this.selectedId = item.id;
                // Si el panel está abierto, cambiar a esta idea
                if (this.panelOpen) {
                    this.openPanel(item);
                }
            }
        },

        onItemRightclick(e, item) {
            this.contextMenu = { visible: true, x: e.clientX, y: e.clientY, type: 'item', target: item };
        },

        onConnectionRightclick(e, conn) {
            this.contextMenu = { visible: true, x: e.clientX, y: e.clientY, type: 'connection', target: conn };
        },

        // ─── PROMOCION A TAREA ───────────────────
        openPeriodSelector(item) {
            this.periodModal.targetItem = item;
            this.periodModal.loading = true;
            this.periodModal.visible = true;
            this.$wire.getActivePeriods().then(periods => {
                this.periodModal.periods = periods;
                this.periodModal.loading = false;
            });
        },

        promoteToPeriod(periodId) {
            if (!this.periodModal.targetItem) return;
            const itemId = this.periodModal.targetItem.id;
            this.periodModal.visible = false;
            // Al promover, se recargará automáticamente la Pizarra en el backend
            this.$wire.promoteToTask(itemId, periodId);
        },

        // ─── PANEL ───────────────────────────────
        openPanel(item) {
            this.editItem = JSON.parse(JSON.stringify(item));
            this.panelOpen = true;
            this.selectedId = item.id;
        },

        closePanel() {
            // Descartar cambios no guardados — recargar datos originales
            this.reload();
            this.panelOpen = false;
            this.editItem = null;
            this.selectedId = null;
        },

        saveField(field, value) {
            const data = {};
            data[field] = value;
            this.$wire.updateItem(this.editItem.id, data).then(() => {
                this.reload().then(() => {
                    const updated = this.items.find(i => i.id === this.editItem.id);
                    if (updated) this.editItem = JSON.parse(JSON.stringify(updated));
                });
            });
        },
        onPanelKeydown(e) {
            if (!this.panelOpen) return;
            const tag = document.activeElement?.tagName;
            const isEditing = tag === 'INPUT' || tag === 'TEXTAREA';
            if (e.key === 'Enter' && !isEditing) {
                e.preventDefault();
                this.saveAllPanelFields();
            }
        },

        saveAllPanelFields() {
            if (!this.editItem) return;
            this.$wire.updateItem(this.editItem.id, {
                title: this.editItem.title,
                notes: this.editItem.notes,
                color: this.editItem.color,
            }).then(() => {
                this.reload().then(() => {
                    this.panelOpen = false;
                    this.editItem = null;
                    this.selectedId = null;
                });
            });
        },
        addSubtaskPanel() {
            if (!this.newSubtaskTitle.trim()) return;
            this.$wire.addSubtask(this.editItem.id, this.newSubtaskTitle).then(() => {
                this.newSubtaskTitle = '';
                this.reload().then(() => {
                    const updated = this.items.find(i => i.id === this.editItem.id);
                    if (updated) this.editItem = JSON.parse(JSON.stringify(updated));
                });
            });
        },

        toggleSubtaskPanel(st) {
            st.is_completed = !st.is_completed;
            this.$wire.toggleSubtask(st.id).then(() => this.reload());
        },

        deleteSubtaskPanel(stId) {
            this.$wire.deleteSubtask(stId).then(() => {
                this.reload().then(() => {
                    const updated = this.items.find(i => i.id === this.editItem.id);
                    if (updated) this.editItem = JSON.parse(JSON.stringify(updated));
                });
            });
        },

        deleteItemPanel() {
            this.$wire.deleteItem(this.editItem.id).then(() => {
                this.closePanel();
                this.reload();
            });
        },

        // ─── CONEXIONES ──────────────────────────
        startConnect(item) {
            this.connectMode = true;
            this.connectSource = item;
        },

        toggleConnectMode() {
            this.connectMode = !this.connectMode;
            if (!this.connectMode) {
                this.connectSource = null;
                this.tempLine = null;
            }
        },

        // ─── UNDO ────────────────────────────────
        undo() {
            if (!this.undoStack.length) return;
            const action = this.undoStack.pop();
            if (action.type === 'connection') {
                this.$wire.deleteConnection(action.id).then(() => this.reload());
            }
        },

        getConnectionPath(conn) {
            const from = this.items.find(i => i.id == conn.from_item_id);
            const to   = this.items.find(i => i.id == conn.to_item_id);
            if (!from || !to) return '';
            const fx = parseFloat(from.pos_x), fy = parseFloat(from.pos_y);
            const fw = parseFloat(from.width), fh = parseFloat(from.height);
            const tx = parseFloat(to.pos_x),   ty = parseFloat(to.pos_y);
            const tw = parseFloat(to.width),    th = parseFloat(to.height);

            // Centros de cada caja
            const fcx = fx + fw / 2, fcy = fy + fh / 2;
            const tcx = tx + tw / 2, tcy = ty + th / 2;

            // Puntos centrales de cada lado
            const getSidePoint = (bx, by, bw, bh, targetX, targetY) => {
                const cx = bx + bw / 2, cy = by + bh / 2;
                const sides = [
                    { x: cx,      y: by,      dir: 'top' },    // top
                    { x: bx + bw, y: cy,      dir: 'right' },  // right
                    { x: cx,      y: by + bh, dir: 'bottom' }, // bottom
                    { x: bx,      y: cy,      dir: 'left' },   // left
                ];
                let best = sides[0], bestDist = Infinity;
                for (const s of sides) {
                    const d = Math.hypot(s.x - targetX, s.y - targetY);
                    if (d < bestDist) { bestDist = d; best = s; }
                }
                return best;
            };

            const p1 = getSidePoint(fx, fy, fw, fh, tcx, tcy);
            const p2 = getSidePoint(tx, ty, tw, th, fcx, fcy);

            // Control points para curva suave según la dirección de salida/entrada
            const offset = 50;
            const cp = (p, dir) => {
                if (dir === 'top')    return { x: p.x, y: p.y - offset };
                if (dir === 'bottom') return { x: p.x, y: p.y + offset };
                if (dir === 'left')   return { x: p.x - offset, y: p.y };
                return { x: p.x + offset, y: p.y };
            };
            const c1 = cp(p1, p1.dir);
            const c2 = cp(p2, p2.dir);

            return `M ${p1.x} ${p1.y} C ${c1.x} ${c1.y}, ${c2.x} ${c2.y}, ${p2.x} ${p2.y}`;
        },

        getConnectionEndpoint(conn) {
            const from = this.items.find(i => i.id == conn.from_item_id);
            const to   = this.items.find(i => i.id == conn.to_item_id);
            if (!from || !to) return null;
            const tx = parseFloat(to.pos_x), ty = parseFloat(to.pos_y);
            const tw = parseFloat(to.width), th = parseFloat(to.height);
            const fcx = parseFloat(from.pos_x) + parseFloat(from.width) / 2;
            const fcy = parseFloat(from.pos_y) + parseFloat(from.height) / 2;
            const tcx = tx + tw / 2, tcy = ty + th / 2;

            // Mismo cálculo de lado más cercano
            const sides = [
                { x: tcx,      y: ty,      dir: 'top' },
                { x: tx + tw,  y: tcy,     dir: 'right' },
                { x: tcx,      y: ty + th, dir: 'bottom' },
                { x: tx,       y: tcy,     dir: 'left' },
            ];
            let best = sides[0], bestDist = Infinity;
            for (const s of sides) {
                const d = Math.hypot(s.x - fcx, s.y - fcy);
                if (d < bestDist) { bestDist = d; best = s; }
            }

            // Ángulo de entrada según dirección
            const angles = { top: Math.PI / 2, bottom: -Math.PI / 2, left: 0, right: Math.PI };
            return { x: best.x, y: best.y, angle: angles[best.dir] };
        },

        // ─── ACCIONES RÁPIDAS ────────────────────
        quickDelete(item) {
            this.$wire.deleteItem(item.id).then(() => this.reload());
            if (this.editItem && this.editItem.id === item.id) this.closePanel();
        },

        quickColor(item, color) {
            this.contextMenu.visible = false;
            this.$wire.updateItem(item.id, { color }).then(() => this.reload());
        },

        quickDeleteConnection(conn) {
            this.$wire.deleteConnection(conn.id).then(() => this.reload());
        },

        // ─── ZOOM / PAN ──────────────────────────
        zoom(delta, originX, originY) {
            const newScale = Math.min(2, Math.max(0.2, this.scale + delta));
            if (originX !== undefined) {
                this.panX = originX - (originX - this.panX) * (newScale / this.scale);
                this.panY = originY - (originY - this.panY) * (newScale / this.scale);
            }
            this.scale = newScale;
        },

        resetView() {
            this.scale = 1; this.panX = 0; this.panY = 0;
        },

        addItemCenter() {
            const el = document.getElementById('pizarra-canvas');
            const rect = el.getBoundingClientRect();
            const x = (rect.width  / 2 - this.panX) / this.scale;
            const y = (rect.height / 2 - this.panY) / this.scale;
            this.$wire.addItem(x, y).then(() => this.reload());
        },

        savePosition(item) {
            this.$wire.updateItem(item.id, { pos_x: item.pos_x, pos_y: item.pos_y });
        },

        // ─── RELOAD ──────────────────────────────
        async reload() {
            const data = await this.$wire.loadItems();
            if (data) this.items = data;
            return Promise.resolve();
        },
    }));
    </script>
    @endscript

</div>