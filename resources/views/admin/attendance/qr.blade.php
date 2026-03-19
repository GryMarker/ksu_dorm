<x-ksu-layout page-title="QR Attendance">
    <div
        class="space-y-8"
        x-data="qrAttendancePage(@js($qrPayload), @js($recentLogs))"
        x-init="init()"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">QR Attendance Screen</h1>
                <p class="text-sm text-slate-600">Show this screen to students. The QR code refreshes every 30 seconds.</p>
            </div>
            <x-ksu-badge variant="info">Dorm Master</x-ksu-badge>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
            <x-ksu-card title="Live QR Code">
                <div class="flex flex-col items-center gap-5">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-ksu">
                        <img x-ref="qrImage" alt="Attendance QR code" class="h-72 w-72 rounded-2xl object-contain sm:h-80 sm:w-80">
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-semibold text-ksu-900">Refreshes in <span x-text="countdown"></span>s</p>
                        <p class="mt-1 text-xs text-slate-500">Students scan the code, log in if needed, and confirm attendance on their phone.</p>
                    </div>
                </div>
            </x-ksu-card>

            <x-ksu-card title="Recent Scans">
                <div class="space-y-3">
                    <template x-for="log in recentLogs" :key="`${log.tenant}-${log.timestamp}-${log.type}`">
                        <div class="rounded-2xl border border-slate-200/70 bg-slate-50/60 px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-ksu-900" x-text="log.tenant"></p>
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase"
                                    :class="log.type === 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                    x-text="log.type"
                                ></span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500" x-text="`${log.timestamp} • ${log.mode}`"></p>
                        </div>
                    </template>

                    <p x-show="recentLogs.length === 0" class="text-sm text-slate-500">No attendance scans yet.</p>
                </div>
            </x-ksu-card>
        </div>
    </div>

    @push('scripts')
        <script>
            window.qrAttendancePage = (initialQr, initialLogs) => ({
                qr: initialQr,
                recentLogs: initialLogs,
                countdown: 30,
                timer: null,
                poller: null,
                async renderQr() {
                    if (!window.QRCode?.toDataURL) return;

                    this.$refs.qrImage.src = await window.QRCode.toDataURL(this.qr.scan_url, {
                        width: 512,
                        margin: 1,
                        color: { dark: '#0f172a', light: '#ffffff' },
                    });
                },
                updateCountdown() {
                    this.countdown = Math.max(0, Math.ceil((new Date(this.qr.expires_at).getTime() - Date.now()) / 1000));
                },
                async refresh() {
                    const response = await fetch('{{ route('admin.attendance.qr.current') }}', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                    });

                    if (!response.ok) return;

                    const payload = await response.json();
                    if (payload.qr.scan_url !== this.qr.scan_url) {
                        this.qr = payload.qr;
                        await this.renderQr();
                    } else {
                        this.qr = payload.qr;
                    }

                    this.recentLogs = payload.recent_logs ?? [];
                    this.updateCountdown();
                },
                init() {
                    this.renderQr();
                    this.updateCountdown();
                    this.timer = setInterval(() => this.updateCountdown(), 1000);
                    this.poller = setInterval(() => this.refresh(), 5000);
                },
            });
        </script>
    @endpush
</x-ksu-layout>
