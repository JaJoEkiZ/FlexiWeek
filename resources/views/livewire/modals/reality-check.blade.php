<div x-data="realityCheckApp()"
     x-show="show" 
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm"
     style="display: none;">
    
    <div class="bg-[#1e1e1e] border border-[#f85149] rounded-lg shadow-2xl shadow-[#f85149]/20 w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]">
         
        <!-- Header / Quote -->
        <div class="p-8 bg-[#161616] border-b border-[#333] shrink-0">
            <div class="flex items-start gap-4">
                <div class="shrink-0 text-[#f85149] mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white mb-4">Límite de Tiempo Real superado</h2>
                    <blockquote class="border-l-4 border-[#f85149] pl-4 italic text-sm text-[#8b949e] mb-2 leading-relaxed">
                        "El capital exige que siempre parezcamos ocupados, aunque no haya nada que hacer. Si el voluntarismo mágico del neoliberalismo tiene razón, siempre hay oportunidades por perseguir o crear; cualquier tiempo que no se invierta en buscar y presionar es tiempo perdido. Toda la ciudad se ve forzada a una gigantesca simulación de actividad, un fanatismo del productivismo en el que, en definitiva, casi nada se produce."
                    </blockquote>
                    <p class="text-xs text-[#7b7b7b] font-medium">— Mark Fisher, Ghosts of My Life (2014)</p>
                </div>
            </div>
        </div>

        <!-- Body / Logic -->
        <div class="p-6 flex-1 flex flex-col min-h-0">
            <div class="flex justify-between items-center mb-4 shrink-0">
                <p class="text-sm text-[#d4d4d4]">Has superado el límite de carga útil del período <strong class="text-white">(<span x-text="Math.floor(availableMinutes / 60)"></span> horas acumuladas de los <span x-text="Math.round(availableMinutes / 60 / 14)"></span> días seleccionados)</strong>. Libera espacio antes de reprogramar más hiperactividad:</p>
                <div class="text-right whitespace-nowrap pl-4">
                    <span class="text-[10px] text-[#8b949e] uppercase tracking-wider block">Ocupado / Límite</span>
                    <span class="text-xl font-mono font-bold" :class="assignedMinutes > availableMinutes ? 'text-[#f85149]' : 'text-[#4ec9b0]'">
                        <span x-text="Math.floor(assignedMinutes / 60)"></span>h <span x-text="assignedMinutes % 60"></span>m 
                        <span class="text-[#7b7b7b] font-normal text-sm">/ <span x-text="Math.floor(availableMinutes / 60)"></span>h</span>
                    </span>
                </div>
            </div>

            <!-- List of tasks -->
            <div class="overflow-y-auto pr-2 space-y-2 custom-scrollbar flex-1 min-h-[150px]">

                <template x-if="draftAction">
                    <div class="p-3 bg-[#3a201c] border-2 border-[#f85149] rounded">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <span class="px-2 py-0.5 bg-[#f85149]/20 text-[#f85149] text-[10px] uppercase font-bold rounded mb-1 inline-block text-xs" x-text="draftAction.type === 'move_task' ? 'Moviendo' : 'Pendiente de Guardar'">
                                </span>
                                <p class="text-sm font-medium text-white truncate" :title="draftAction.title" x-text="draftAction.title"></p>
                                <p class="text-xs text-[#f85149]">
                                    <span class="font-mono"><span x-text="Math.floor(draftAction.effective_minutes / 60)"></span>h <span x-text="draftAction.effective_minutes % 60"></span>m</span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <template x-if="draftAction.completion_method === 'time'">
                                    <div class="flex items-center gap-1" x-data="{ reduceVal: 30 }">
                                        <input type="number" x-model="reduceVal" class="w-14 px-1 py-1 text-xs bg-[#1e1e1e] border border-[#f85149]/50 rounded text-center text-[#d4d4d4]" min="1">
                                        <button @click="reduceDraftActionTime(reduceVal)" 
                                                class="px-2 py-1 bg-[#1e1e1e] border border-[#f85149]/50 hover:bg-[#f85149] hover:text-white text-[#f85149] text-xs font-medium rounded transition-colors"
                                                title="Restar minutos">
                                            Restar
                                        </button>
                                    </div>
                                </template>
                                <template x-if="draftAction.completion_method !== 'time'">
                                    <span class="text-[10px] text-[#f85149]/80 uppercase px-2 py-1 bg-[#1e1e1e] rounded border border-[#f85149]/30" title="Su tiempo proviene de subtareas">Subtareas*</span>
                                </template>
                                <button @click="$wire.close()" 
                                        class="p-1.5 text-[#f85149] hover:bg-[#f85149]/20 rounded transition-colors"
                                        title="Descartar esta acción completa">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-for="task in tasks" :key="task.id">
                    <div class="p-3 bg-[#252526] border border-[#333] rounded hover:border-[#4ec9b0] transition-colors">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate" :title="task.title" x-text="task.title"></p>
                                <p class="text-xs text-[#8b949e]">
                                    <span class="font-mono"><span x-text="Math.floor(task.estimated_minutes / 60)"></span>h <span x-text="task.estimated_minutes % 60"></span>m</span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <template x-if="task.completion_method === 'time'">
                                    <div class="flex items-center gap-1" x-data="{ reduceVal: 30 }">
                                        <input type="number" x-model="reduceVal" class="w-14 px-1 py-1 text-xs bg-[#1e1e1e] border border-[#333] rounded text-center text-[#d4d4d4]" min="1">
                                        <button @click="reduceTaskTime(task.id, reduceVal)" 
                                                class="px-2 py-1 bg-[#333] hover:bg-[#4ec9b0] hover:text-black text-[#8b949e] text-xs font-medium rounded transition-colors"
                                                title="Restar minutos">
                                            Restar
                                        </button>
                                    </div>
                                </template>
                                <template x-if="task.completion_method !== 'time'">
                                    <span class="text-[10px] text-[#7b7b7b] uppercase px-2 py-1 bg-[#1e1e1e] rounded border border-[#333]">Subtareas</span>
                                </template>
                                <button @click="deleteTask(task.id)" 
                                        class="p-1.5 text-[#f85149] hover:bg-[#f85149]/10 rounded transition-colors"
                                        title="Eliminar tarea">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Subtasks if any -->
                        <template x-if="task.completion_method === 'subtasks' && task.subtasks && task.subtasks.length > 0">
                            <div class="mt-2 pl-4 border-l-2 border-[#333] space-y-2">
                                <template x-for="subtask in task.subtasks" :key="subtask.id">
                                    <div class="flex items-center justify-between gap-2 bg-[#1e1e1e] p-2 rounded">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-[#d4d4d4] truncate" :title="subtask.title" x-text="subtask.title"></p>
                                            <p class="text-[10px] text-[#8b949e]">
                                                <span class="font-mono"><span x-text="Math.floor(subtask.estimated_minutes / 60)"></span>h <span x-text="subtask.estimated_minutes % 60"></span>m</span>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <div class="flex items-center gap-1" x-data="{ subReduceVal: 30 }">
                                                <input type="number" x-model="subReduceVal" class="w-12 px-1 py-1 text-[10px] bg-[#252526] border border-[#333] rounded text-center text-[#d4d4d4]" min="1">
                                                <button @click="reduceSubtaskTime(task.id, subtask.id, subReduceVal)" 
                                                        class="px-2 py-1 bg-[#252526] hover:bg-[#4ec9b0] hover:text-black text-[#8b949e] text-[10px] font-medium rounded transition-colors"
                                                        title="Restar minutos">
                                                    Restar
                                                </button>
                                            </div>
                                            <button @click="deleteSubtask(task.id, subtask.id)" 
                                                    class="p-1 text-[#f85149] hover:bg-[#f85149]/10 rounded transition-colors"
                                                    title="Eliminar subtarea">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
                
                <template x-if="!draftAction && tasks.length === 0">
                    <p class="text-xs text-[#7b7b7b] text-center italic py-4">No hay tareas creadas.</p>
                </template>
            </div>
            
        </div>
        
        <div class="p-4 border-t border-[#333] bg-[#1a1a1a] flex justify-between items-center shrink-0">
            <div>
                <template x-if="assignedMinutes > availableMinutes">
                    <span class="text-xs text-[#f85149] font-medium">Faltan <span x-text="assignedMinutes - availableMinutes"></span> min por liberar.</span>
                </template>
                <template x-if="assignedMinutes <= availableMinutes">
                    <span class="text-xs text-[#4ec9b0] font-medium">Condiciones logradas.</span>
                </template>
            </div>

            <div class="flex gap-3">
                <button @click="$wire.close()" class="px-4 py-2 text-sm font-medium text-[#8b949e] hover:text-[#f85149] transition-colors bg-transparent">
                    Cancelar Acción
                </button>
                <button @click="submit()" 
                        class="px-5 py-2 text-sm font-medium rounded transition-colors"
                        :class="assignedMinutes <= availableMinutes ? 'text-[#d4d4d4] hover:text-white bg-[#007fd4] hover:bg-[#006bb3]' : 'text-[#7b7b7b] bg-[#333] cursor-not-allowed opacity-50'"
                        :disabled="assignedMinutes > availableMinutes">
                    Resolver y Continuar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('realityCheckApp', () => ({
        show: false,
        tasks: [],
        draftAction: null,
        availableMinutes: 0,
        adjustments: [],

        init() {
            this.$watch('$wire.isOpen', (val) => {
                this.show = val;
                if (val) {
                    this.reloadFromWire();
                } else {
                    this.adjustments = [];
                }
            });
        },

        async reloadFromWire() {
            let data = await this.$wire.get('tasksData');
            this.tasks = JSON.parse(JSON.stringify(data));
            
            let draft = await this.$wire.get('draftAction');
            this.draftAction = draft ? JSON.parse(JSON.stringify(draft)) : null;
            
            this.availableMinutes = await this.$wire.get('availableMinutes');
            this.adjustments = [];
        },

        get assignedMinutes() {
            let total = 0;
            this.tasks.forEach(t => {
                total += t.estimated_minutes;
            });
            if (this.draftAction) {
                if (this.draftAction.type === 'create_task' || this.draftAction.type === 'move_task') {
                    total += this.draftAction.effective_minutes;
                } else if (this.draftAction.type === 'edit_task') {
                    total -= this.draftAction.old_minutes;
                    total += this.draftAction.effective_minutes;
                }
            }
            return total;
        },

        reduceDraftActionTime(mins) {
            mins = parseInt(mins);
            if(mins <= 0) return;
            let val = Math.max(0, this.draftAction.effective_minutes - mins);
            this.draftAction.effective_minutes = val;
            if(this.draftAction.payload && this.draftAction.payload.completionMethod === 'time') {
                this.draftAction.payload.hours = Math.floor(val / 60);
                this.draftAction.payload.minutes = val % 60;
            }
        },

        reduceTaskTime(id, mins) {
            mins = parseInt(mins);
            if(mins <= 0) return;
            let target = this.tasks.find(t => t.id === id);
            if(target && target.completion_method === 'time') {
                target.estimated_minutes = Math.max(0, target.estimated_minutes - mins);
                this.adjustments.push({ type: 'reduce_task', id: id, minutes_to_reduce: mins });
            }
        },

        reduceSubtaskTime(taskId, subId, mins) {
            mins = parseInt(mins);
            if(mins <= 0) return;
            let tIndex = this.tasks.findIndex(t => t.id === taskId);
            if(tIndex !== -1) {
                let stTarget = this.tasks[tIndex].subtasks.find(st => st.id === subId);
                if (stTarget) {
                    let oldMins = stTarget.estimated_minutes;
                    let newMins = Math.max(0, oldMins - mins);
                    let diff = oldMins - newMins;
                    stTarget.estimated_minutes = newMins;
                    this.tasks[tIndex].estimated_minutes -= diff;
                    this.adjustments.push({ type: 'reduce_subtask', id: subId, minutes_to_reduce: mins });
                }
            }
        },

        deleteTask(id) {
            this.tasks = this.tasks.filter(t => t.id !== id);
            this.adjustments.push({ type: 'delete_task', id: id });
        },

        deleteSubtask(taskId, subId) {
            let tIndex = this.tasks.findIndex(t => t.id === taskId);
            if(tIndex !== -1) {
                let stIndex = this.tasks[tIndex].subtasks.findIndex(st => st.id === subId);
                if (stIndex !== -1) {
                    let st = this.tasks[tIndex].subtasks[stIndex];
                    this.tasks[tIndex].estimated_minutes -= st.estimated_minutes;
                    this.tasks[tIndex].subtasks.splice(stIndex, 1);
                    this.adjustments.push({ type: 'delete_subtask', id: subId });
                }
            }
        },

        submit() {
            if (this.assignedMinutes <= this.availableMinutes) {
                this.$wire.resolveAndExecute(this.adjustments, this.draftAction);
            }
        }
    }));
});
</script>
