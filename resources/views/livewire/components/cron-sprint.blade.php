<div class="flex flex-col overflow-hidden h-full text-[#d4d4d4]"
     x-data="{
        isCollapsed: localStorage.getItem('cronSprintCollapsed') === 'true',
        mode: 'idle', // 'idle', 'sprint', 'free', 'assigning'
        activeSprint: null,
        timeRemaining: 0,
        sprintTotalSeconds: 0,
        timeElapsed: 0, // tenths of seconds
        intervalId: null,
        taskId: localStorage.getItem('lastCronSprintTaskId') || '',
        subtaskId: localStorage.getItem('lastCronSprintSubtaskId') || '',
        clockSize: localStorage.getItem('cronSprintClockSize') || 'md',
        alarmSound: localStorage.getItem('cronSprintAlarmSound') || 'End Solo.mp3',
        alarmVolume: parseInt(localStorage.getItem('cronSprintAlarmVolume')) || 50,
        currentAudioTest: null,
        minutesToAssign: 0,
        tasksData: @js($tasks),

        filteredSubtasks() {
            if (!this.taskId || !this.tasksData) return [];
            let task = this.tasksData.find(t => t.id == this.taskId);
            if (!task || !task.subtasks) return [];
            return task.subtasks.filter(st => !st.is_completed);
        },
        
        toggleClockSize() {
            if (this.clockSize === 'sm') { this.clockSize = 'md'; }
            else if (this.clockSize === 'md') { this.clockSize = 'lg'; }
            else { this.clockSize = 'sm'; }
            localStorage.setItem('cronSprintClockSize', this.clockSize);
        },
        
        init() {
            // Sincronizar tasksData con el DOM al redibujar
            Livewire.hook('morph.updated', ({ el, component }) => {
                if (document.getElementById('cron-tasks-data')) {
                    try {
                        let parsed = JSON.parse(document.getElementById('cron-tasks-data').innerText);
                        this.tasksData = parsed;
                    } catch(e) {}
                }
            });

            let serverState = @js($timerState);
            
            // Si el backend dictamina un estado oficial de este usuario (ej: viene de volver a loguear o desde otro dispositivo)
            if (serverState && serverState.mode) {
                localStorage.setItem('cronSprint_mode', serverState.mode);
                if (serverState.activeSprint) localStorage.setItem('cronSprint_activeSprint', serverState.activeSprint);
                if (serverState.sprintTotalSeconds) localStorage.setItem('cronSprint_totalSeconds', serverState.sprintTotalSeconds);
                if (serverState.targetTs) localStorage.setItem('cronSprint_targetTs', serverState.targetTs);
                if (serverState.startTs) localStorage.setItem('cronSprint_startTs', serverState.startTs);
                if (serverState.isPaused) {
                    localStorage.setItem('cronSprint_isPaused', 'true');
                    localStorage.setItem('cronSprint_pausedTimeRemaining', serverState.pausedTimeRemaining || 0);
                    localStorage.setItem('cronSprint_pausedTimeElapsed', serverState.pausedTimeElapsed || 0);
                } else {
                    localStorage.removeItem('cronSprint_isPaused');
                }
                if (serverState.minutesToAssign) localStorage.setItem('cronSprint_minutesToAssign', serverState.minutesToAssign);
            }

            let savedMode = localStorage.getItem('cronSprint_mode');
            if (!savedMode) return;
            
            let isPaused = localStorage.getItem('cronSprint_isPaused') === 'true';
            
            if (savedMode === 'sprint') {
                this.mode = 'sprint';
                this.activeSprint = localStorage.getItem('cronSprint_activeSprint');
                this.sprintTotalSeconds = parseInt(localStorage.getItem('cronSprint_totalSeconds') || 0);
                
                if (isPaused) {
                    this.timeRemaining = parseInt(localStorage.getItem('cronSprint_pausedTimeRemaining') || 0);
                } else {
                    let target = parseInt(localStorage.getItem('cronSprint_targetTs') || 0);
                    this.timeRemaining = Math.max(0, Math.floor((target - Date.now()) / 1000));
                }
                
                if (!isPaused && this.timeRemaining > 0) {
                    this.startInterval();
                } else if (!isPaused && this.timeRemaining <= 0) {
                    this.calculateMinutesAndAssign();
                }
            } else if (savedMode === 'free') {
                this.mode = 'free';
                if (isPaused) {
                    this.timeElapsed = parseInt(localStorage.getItem('cronSprint_pausedTimeElapsed') || 0);
                } else {
                    let start = parseInt(localStorage.getItem('cronSprint_startTs') || 0);
                    this.timeElapsed = Math.max(0, Math.floor((Date.now() - start) / 100));
                    this.startInterval();
                }
            } else if (savedMode === 'assigning') {
                 this.mode = 'assigning';
                 this.minutesToAssign = parseFloat(localStorage.getItem('cronSprint_minutesToAssign') || 0);
                 this.activeSprint = localStorage.getItem('cronSprint_activeSprint');
                 this.sprintTotalSeconds = parseInt(localStorage.getItem('cronSprint_totalSeconds') || 0);
            }
        },
        
        syncState() {
            $wire.saveTimerState({
                mode: this.mode,
                activeSprint: this.activeSprint,
                sprintTotalSeconds: this.sprintTotalSeconds,
                targetTs: localStorage.getItem('cronSprint_targetTs'),
                startTs: localStorage.getItem('cronSprint_startTs'),
                isPaused: localStorage.getItem('cronSprint_isPaused') === 'true',
                pausedTimeRemaining: this.timeRemaining,
                pausedTimeElapsed: this.timeElapsed,
                minutesToAssign: this.minutesToAssign
            });
        },

        startSprint(minutes, seconds) {
            this.mode = 'sprint';
            this.activeSprint = `${minutes}:${seconds}`;
            this.sprintTotalSeconds = (minutes * 60) + seconds;
            this.timeRemaining = this.sprintTotalSeconds;
            
            localStorage.setItem('cronSprint_mode', 'sprint');
            localStorage.setItem('cronSprint_activeSprint', this.activeSprint);
            localStorage.setItem('cronSprint_totalSeconds', this.sprintTotalSeconds);
            localStorage.setItem('cronSprint_targetTs', Date.now() + (this.sprintTotalSeconds * 1000));
            localStorage.removeItem('cronSprint_isPaused');
            
            this.syncState();
            this.startInterval();
        },
        
        startFreeTimer() {
            this.mode = 'free';
            this.timeElapsed = 0;
            localStorage.setItem('cronSprint_mode', 'free');
            localStorage.setItem('cronSprint_startTs', Date.now());
            localStorage.removeItem('cronSprint_isPaused');
            this.syncState();
            this.startInterval();
        },
        
        pauseTimer() {
            clearInterval(this.intervalId);
            this.intervalId = null;
            localStorage.setItem('cronSprint_isPaused', 'true');
            if (this.mode === 'free') {
                localStorage.setItem('cronSprint_pausedTimeElapsed', this.timeElapsed);
            } else if (this.mode === 'sprint') {
                localStorage.setItem('cronSprint_pausedTimeRemaining', this.timeRemaining);
            }
            this.syncState();
        },
        
        resumeTimer() {
            localStorage.removeItem('cronSprint_isPaused');
            if (this.mode === 'free') {
                localStorage.setItem('cronSprint_startTs', Date.now() - (this.timeElapsed * 100));
            } else if (this.mode === 'sprint') {
                localStorage.setItem('cronSprint_targetTs', Date.now() + (this.timeRemaining * 1000));
            }
            this.syncState();
            this.startInterval();
        },
        
        stopTimer() {
            clearInterval(this.intervalId);
            this.intervalId = null;
            if (this.mode === 'sprint' || this.mode === 'free') {
                this.calculateMinutesAndAssign();
            } else {
                this.reset();
            }
        },

        calculateMinutesAndAssign() {
            if (this.mode === 'sprint') {
                // Calculamos cuánto tiempo pasó realmente en lugar de todo el sprint, 
                // o asumimos que se anula todo? Si se cancela a la mitad, sumamos lo trabajado.
                let secondsWorked = this.sprintTotalSeconds - this.timeRemaining;
                this.minutesToAssign = secondsWorked / 60;
            } else if (this.mode === 'free') {
                let totalSeconds = this.timeElapsed / 10;
                this.minutesToAssign = totalSeconds / 60;
            }
            
            if (this.minutesToAssign < (1/60)) { 
                // Menos de 1 segundo
                this.$dispatch('toast', { message: 'Tiempo demasiado corto para registrar', type: 'info' });
                this.reset();
                return;
            }

            this.mode = 'assigning';
            localStorage.setItem('cronSprint_mode', 'assigning');
            localStorage.setItem('cronSprint_minutesToAssign', this.minutesToAssign);
            
            // Auto-expand panel when assigning time so it isn't hidden from the user
            this.isCollapsed = false;
            localStorage.setItem('cronSprintCollapsed', 'false');

            this.syncState();
        },
        
        startInterval() {
            if (this.intervalId) return;
            
            if (this.mode === 'free') {
                this.intervalId = setInterval(() => {
                    let start = parseInt(localStorage.getItem('cronSprint_startTs') || Date.now());
                    this.timeElapsed = Math.floor((Date.now() - start) / 100);
                }, 100);
            } else if (this.mode === 'sprint') {
                this.intervalId = setInterval(() => {
                    let target = parseInt(localStorage.getItem('cronSprint_targetTs') || Date.now());
                    this.timeRemaining = Math.max(0, Math.floor((target - Date.now()) / 1000));
                    
                    if (this.timeRemaining <= 0) {
                        this.playAlarm();
                        clearInterval(this.intervalId);
                        this.intervalId = null;
                        this.calculateMinutesAndAssign();
                    }
                }, 500); // Verificamos cada medio segundo para máxima precisión sin sobrecargar
            }
        },
        
        playAlarm() {
            try {
                // Detener audio previo si está sonando para reiniciar (útil al spamear el test)
                if (this.currentAudioTest) {
                    this.currentAudioTest.pause();
                    this.currentAudioTest.currentTime = 0;
                }

                // If it's empty, use the browser beep fallback
                if (!this.alarmSound) {
                    this.playBeep();
                    return;
                }
                
                this.currentAudioTest = new Audio('/sounds/' + this.alarmSound);
                this.currentAudioTest.volume = this.alarmVolume / 100;
                this.currentAudioTest.play().catch(e => {
                    console.log('Error reproduciendo sonido, usando fallback', e);
                    this.playBeep();
                });
            } catch(e) {
                console.log('Audio API falló', e);
                this.playBeep();
            }
        },

        updateVolume() {
            localStorage.setItem('cronSprintAlarmVolume', this.alarmVolume);
            if (this.currentAudioTest) {
                this.currentAudioTest.volume = this.alarmVolume / 100;
            }
        },

        playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                
                // Función helper para hacer un bip corto
                const makeBeep = (startTime) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(440, startTime);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    gain.gain.setValueAtTime(0.05, startTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, startTime + 0.15);
                    osc.start(startTime);
                    osc.stop(startTime + 0.2);
                };

                // Bios style beep - 2 bips cortos
                makeBeep(ctx.currentTime);
                makeBeep(ctx.currentTime + 0.3);
            } catch(e) {
                console.log('Web Audio API not supported');
            }
        },
        
        assignTime() {
            if (!this.taskId) {
                this.$dispatch('toast', { message: 'Debes seleccionar una tarea', type: 'error' });
                return;
            }
            
            // Validar que la subtarea realmente corresponda a la tarea seleccionada actualmente
            // Si el valor es de una sesión vieja (stale) que quedó en la variable, lo limpiamos
            let currentTask = this.tasksData.find(t => t.id == this.taskId);
            if (!currentTask || !currentTask.subtasks || !currentTask.subtasks.find(st => st.id == this.subtaskId)) {
                this.subtaskId = '';
            }
            
            localStorage.setItem('lastCronSprintTaskId', this.taskId);
            if (this.subtaskId) {
                localStorage.setItem('lastCronSprintSubtaskId', this.subtaskId);
            } else {
                localStorage.removeItem('lastCronSprintSubtaskId');
            }
            
            // Livewire call (se envía totalmente asíncrono en segundo plano)
            $wire.assignTime(this.taskId, this.subtaskId, this.minutesToAssign.toFixed(2));
            
            // UI Optimista: Reseteo instantáneo de la interfaz SIN esperar la respuesta del servidor
            this.reset();
        },

        reset() {
            this.mode = 'idle';
            this.minutesToAssign = 0;
            this.activeSprint = null;
            this.timeRemaining = 0;
            this.timeElapsed = 0;
            if(this.intervalId) clearInterval(this.intervalId);
            this.intervalId = null;
            
            // Limpieza total del storage local
            localStorage.removeItem('cronSprint_mode');
            localStorage.removeItem('cronSprint_activeSprint');
            localStorage.removeItem('cronSprint_totalSeconds');
            localStorage.removeItem('cronSprint_targetTs');
            localStorage.removeItem('cronSprint_startTs');
            localStorage.removeItem('cronSprint_isPaused');
            localStorage.removeItem('cronSprint_pausedTimeElapsed');
            localStorage.removeItem('cronSprint_pausedTimeRemaining');
            localStorage.removeItem('cronSprint_minutesToAssign');

            // Limpieza de caché de backend
            $wire.clearTimerState();
        },
        
        formatSprintTime(seconds) {
            let m = Math.floor(seconds / 60);
            let s = Math.floor(seconds % 60);
            return m.toString().padStart(2, '0') + ':' + s.toString().padStart(2, '0');
        },
        
        formatFreeTime(tenths) {
            let totalSeconds = Math.floor(tenths / 10);
            let ds = tenths % 10;
            let m = Math.floor(totalSeconds / 60);
            let s = totalSeconds % 60;
            return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}.${ds}`;
        },

        get displayTime() {
            if (this.mode === 'sprint') {
                return this.formatSprintTime(this.timeRemaining);
            } else if (this.mode === 'free') {
                return this.formatFreeTime(this.timeElapsed);
            }
            return '00:00.0';
        },

        get progressPercent() {
            if (this.mode !== 'sprint' || this.sprintTotalSeconds === 0) return 0;
            return ((this.sprintTotalSeconds - this.timeRemaining) / this.sprintTotalSeconds) * 100;
        }
     }">

    <!-- Header (Vuelve a estar arriba) -->
    <div @click="isCollapsed = !isCollapsed; localStorage.setItem('cronSprintCollapsed', isCollapsed)" 
         class="px-3 md:px-4 py-2 border-b border-[#333] flex justify-between items-center bg-[#252526] shrink-0 cursor-pointer hover:bg-[#2d2d2d] transition-colors select-none">
        
        <h2 class="text-xs font-bold text-[#7b7b7b] uppercase tracking-wider flex items-center gap-2">
            <span class="text-[#007fd4]">⏱</span> CronSprint
        </h2>
        
        <div class="flex items-center gap-3">
            <!-- Timer miniatura -->
            <div x-show="isCollapsed && (mode === 'sprint' || mode === 'free')" 
                 x-cloak
                 class="text-[10px] font-mono font-bold font-variant-numeric: tabular-nums tracking-tighter"
                 :class="{
                    'text-[#007fd4]': mode === 'sprint' && activeSprint === '33:33',
                    'text-[#4ec9b0]': mode === 'sprint' && activeSprint === '22:22',
                    'text-[#ce9178]': mode === 'sprint' && activeSprint === '15:15',
                    'text-[#d2a8ff]': mode === 'sprint' && activeSprint === '6:36',
                    'text-[#d4d4d4]': mode === 'free'
                 }">
                <span x-text="displayTime"></span>
            </div>
            
            <!-- Icono Chevron (DOWN cuando colapsado (apunta al panel guardado), UP cuando expandido) -->
            <svg :class="isCollapsed ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#7b7b7b] transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </div>
    </div>

    <!-- Scrollable Content con transición CSS manual -->
    <div x-show="!isCollapsed"
         x-transition:enter="transition-all duration-300 ease-out"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition-all duration-300 ease-in"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4">
        <div class="overflow-y-auto custom-scrollbar p-3 md:p-4 bg-[#1e1e1e] flex flex-col gap-4 max-h-[400px]">

        <!-- IDLE START -->
        <div x-show="mode === 'idle'" class="space-y-4">
            
            <div class="text-[10px] text-[#7b7b7b] uppercase tracking-wider mb-2">Sprints fijos</div>
            
            <div class="grid grid-cols-2 gap-2">
                <!-- 33:33 -->
                <button @click="startSprint(33, 33)" class="bg-[#3c3c3c] bg-opacity-50 border border-[#444] hover:border-[#007fd4] hover:text-[#007fd4] text-[#8b949e] transition-all rounded p-2 text-center flex flex-col items-center group shadow-sm">
                    <span class="text-[10px] font-medium uppercase opacity-70 group-hover:opacity-100 transition-opacity">Sprint L</span>
                    <span class="font-mono text-sm font-bold">33:33</span>
                </button>
                <!-- 22:22 -->
                <button @click="startSprint(22, 22)" class="bg-[#3c3c3c] bg-opacity-50 border border-[#444] hover:border-[#4ec9b0] hover:text-[#4ec9b0] text-[#8b949e] transition-all rounded p-2 text-center flex flex-col items-center group shadow-sm">
                    <span class="text-[10px] font-medium uppercase opacity-70 group-hover:opacity-100 transition-opacity">Sprint M</span>
                    <span class="font-mono text-sm font-bold">22:22</span>
                </button>
                <!-- 15:15 -->
                <button @click="startSprint(15, 15)" class="bg-[#3c3c3c] bg-opacity-50 border border-[#444] hover:border-[#ce9178] hover:text-[#ce9178] text-[#8b949e] transition-all rounded p-2 text-center flex flex-col items-center group shadow-sm">
                    <span class="text-[10px] font-medium uppercase opacity-70 group-hover:opacity-100 transition-opacity">Sprint S</span>
                    <span class="font-mono text-sm font-bold">15:15</span>
                </button>
                <!-- 6:36 -->
                <button @click="startSprint(6, 36)" class="bg-[#3c3c3c] bg-opacity-50 border border-[#444] hover:border-[#d2a8ff] hover:text-[#d2a8ff] text-[#8b949e] transition-all rounded p-2 text-center flex flex-col items-center group shadow-sm">
                    <span class="text-[10px] font-medium uppercase opacity-70 group-hover:opacity-100 transition-opacity">Sprint XS</span>
                    <span class="font-mono text-sm font-bold">06:36</span>
                </button>
            </div>

            <div class="h-px bg-[#333] my-2 w-full"></div>

            <!-- Free timer -->
            <button @click="startFreeTimer()" class="w-full bg-[#3c3c3c] hover:bg-[#4d4d4d] border border-[#555] transition-all rounded p-3 flex flex-col items-center shadow-sm">
                <span class="text-[10px] uppercase text-[#969696] mb-1">Cronómetro libre</span>
                <span class="text-lg font-mono tracking-widest">00:00.0</span>
            </button>

            <div class="h-px bg-[#333] my-2 w-full"></div>

            <!-- Ajustes de Alarma -->
            <div class="space-y-3">
                <div class="text-[10px] text-[#7b7b7b] uppercase tracking-wider mb-2">Ajustes de Alarma</div>
                
                <div class="flex items-center gap-2">
                    <select x-model="alarmSound" @change="localStorage.setItem('cronSprintAlarmSound', alarmSound)" class="flex-1 px-2 py-1.5 text-xs bg-[#3c3c3c] border border-[#444] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:outline-none">
                        <option value="">(Sin sonido mp3 - Default)</option>
                        <option value="End Bells.mp3">End Bells</option>
                        <option value="End Royal.mp3">End Royal</option>
                        <option value="End Solo.mp3">End Solo</option>
                    </select>
                    
                    <button @click="playAlarm()" class="px-2 py-1.5 bg-[#4d4d4d] hover:bg-[#555] border border-[#555] rounded text-[#d4d4d4] transition-colors" title="Probar sonido">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M18.364 5.636a9 9 0 010 12.728M11 19l-7-7H2v-4h2l7-7v18z" />
                        </svg>
                    </button>
                </div>
                
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#7b7b7b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9m-2.828 9.9M11 19l-7-7H2v-4h2l7-7v18z" /></svg>
                    <input type="range" min="0" max="100" x-model="alarmVolume" @input="updateVolume()" class="w-full accent-[#007fd4] h-1 bg-[#444] rounded-lg appearance-none cursor-pointer">
                    <span class="text-[10px] text-[#7b7b7b] w-6 text-right font-mono" x-text="alarmVolume + '%'"></span>
                </div>
            </div>
        </div>

        <!-- ACTIVE TIMER -->
        <div x-show="mode === 'sprint' || mode === 'free'" x-cloak class="flex flex-col items-center justify-center h-full space-y-4 py-4">
            
            <div class="flex flex-col items-center">
                <div class="text-[10px] text-[#7b7b7b] uppercase tracking-wider" x-text="mode === 'sprint' ? 'Sprint Activo' : 'Cronómetro Libre'"></div>
                <div x-show="mode === 'sprint'" class="text-[10px] text-[#555] font-mono font-bold mt-0.5"><span x-text="activeSprint"></span></div>
            </div>
            
            <div class="font-mono font-bold font-variant-numeric: tabular-nums tracking-tighter cursor-pointer transition-all duration-300 hover:opacity-80"
                 @click="toggleClockSize"
                 title="Clica para cambiar el tamaño"
                 :class="{
                    'text-3xl': clockSize === 'sm',
                    'text-5xl md:text-5xl': clockSize === 'md',
                    'text-6xl md:text-7xl': clockSize === 'lg',
                    'text-[#007fd4]': mode === 'sprint' && activeSprint === '33:33',
                    'text-[#4ec9b0]': mode === 'sprint' && activeSprint === '22:22',
                    'text-[#ce9178]': mode === 'sprint' && activeSprint === '15:15',
                    'text-[#d2a8ff]': mode === 'sprint' && activeSprint === '6:36',
                    'text-[#d4d4d4]': mode === 'free'
                 }"
                 x-text="displayTime">
            </div>

            <!-- Botones explícitos de tamaño -->
            <div class="flex gap-2 justify-center mt-2 opacity-50 hover:opacity-100 transition-opacity">
                <button type="button" @click="clockSize = 'sm'; localStorage.setItem('cronSprintClockSize', 'sm')" :class="clockSize === 'sm' ? 'bg-[#555] text-white' : 'text-[#7b7b7b] hover:text-[#d4d4d4]'" class="px-2 py-0.5 rounded text-[10px] border border-[#444]">SM</button>
                <button type="button" @click="clockSize = 'md'; localStorage.setItem('cronSprintClockSize', 'md')" :class="clockSize === 'md' ? 'bg-[#555] text-white' : 'text-[#7b7b7b] hover:text-[#d4d4d4]'" class="px-2 py-0.5 rounded text-[10px] border border-[#444]">MD</button>
                <button type="button" @click="clockSize = 'lg'; localStorage.setItem('cronSprintClockSize', 'lg')" :class="clockSize === 'lg' ? 'bg-[#555] text-white' : 'text-[#7b7b7b] hover:text-[#d4d4d4]'" class="px-2 py-0.5 rounded text-[10px] border border-[#444]">LG</button>
            </div>

            <!-- Progress Bar for Sprint -->
            <div x-show="mode === 'sprint'" class="w-full h-1.5 bg-[#3c3c3c] rounded-full overflow-hidden mt-2">
                <div class="h-full bg-current transition-all duration-1000 ease-linear" :style="`width: ${progressPercent}%`"
                     :class="{
                        'text-[#007fd4]': mode === 'sprint' && activeSprint === '33:33',
                        'text-[#4ec9b0]': mode === 'sprint' && activeSprint === '22:22',
                        'text-[#ce9178]': mode === 'sprint' && activeSprint === '15:15',
                        'text-[#d2a8ff]': mode === 'sprint' && activeSprint === '6:36'
                     }"></div>
            </div>

            <!-- Controls -->
            <div class="flex items-center gap-3 mt-4">
                <button type="button" @click="intervalId ? pauseTimer() : resumeTimer()" class="w-10 h-10 rounded-full flex justify-center items-center bg-[#3c3c3c] hover:bg-[#4d4d4d] border border-[#555] transition-colors">
                    <!-- PAUSE ICON -->
                    <svg x-show="intervalId" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#d4d4d4]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <!-- PLAY ICON -->
                    <svg x-show="!intervalId" xmlns="http://www.w3.org/2000/svg" x-cloak class="h-4 w-4 text-[#d4d4d4]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </button>
                <button type="button" @click="stopTimer()" class="w-10 h-10 rounded-full flex justify-center items-center bg-[#3b1219] hover:bg-[#f85149] border border-[#da3633] transition-colors group">
                    <!-- STOP ICON -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#f85149] group-hover:text-[#1e1e1e]" viewBox="0 0 20 20" fill="currentColor"><rect x="6" y="6" width="8" height="8" /></svg>
                </button>
            </div>
        </div>

        <!-- ASSIGNING MODE -->
        <div x-show="mode === 'assigning'" x-cloak class="space-y-4">
            
            <div class="bg-[#1e3a23] border border-[#2ea043] rounded p-3 text-center">
                <div class="text-[10px] text-[#7ee787] uppercase mb-1">Tiempo a registrar</div>
                <div class="text-xl font-mono font-bold text-white"><span x-text="formatSprintTime(Math.floor(minutesToAssign * 60))"></span></div>
            </div>

            <!-- Selector de Tarea -->
            <div>
                <label class="block text-xs text-[#7b7b7b] mb-1">Destino del tiempo (Tarea):</label>
                <select x-model="taskId" @change="subtaskId = ''" class="w-full px-2 py-2 text-sm bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] focus:outline-none">
                    <option value="">-- Selecciona una tarea --</option>
                    @foreach($tasks as $task)
                        <option value="{{ $task->id }}">{{ $task->title }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Selector de Subtarea -->
            <div x-show="taskId && filteredSubtasks().length > 0" x-cloak>
                <label class="block text-xs text-[#7b7b7b] mb-1">Subtarea (Opcional):</label>
                <select x-model="subtaskId" class="w-full px-2 py-2 text-sm bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] focus:outline-none">
                    <option value="">-- Ninguna --</option>
                    <template x-for="st in filteredSubtasks()" :key="st.id">
                        <option :value="st.id" x-text="st.title"></option>
                    </template>
                </select>
            </div>
            
            <!-- Hidden payload to sync Alpine on Livewire morphs -->
            <div id="cron-tasks-data" class="hidden">@json($tasks)</div>

            <div class="flex gap-2 pt-2 border-t border-[#333]">
                <button type="button" @click="reset()" class="flex-1 py-2 bg-[#3c3c3c] border border-[#555] rounded text-xs font-medium hover:bg-[#4d4d4d] transition-colors">
                    Descartar
                </button>
                <button type="button" @click="assignTime()" :disabled="!taskId" :class="!taskId ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#152e42] border-[#007fd4]'" class="flex-1 py-2 bg-[#007fd4] bg-opacity-80 border rounded text-white text-xs font-medium transition-colors">
                    ✓ Asignar
                </button>
            </div>

        </div>
        </div>
    </div>
</div>
