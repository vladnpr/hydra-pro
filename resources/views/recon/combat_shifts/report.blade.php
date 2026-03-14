@extends('adminlte::page')

@section('title', 'Звіт по залишку розвідки')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіт по залишку розвідки</h1>
        <div class="no-print">
            <a href="{{ route('recon.combat_shifts.show', $shift->id) }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад до чергування
            </a>
            <button type="button" class="btn btn-primary ml-2" onclick="printReport()">
                <i class="fas fa-print"></i> Друкувати
            </button>
            <button id="copy-report" class="btn btn-info ml-2">
                <i class="fas fa-copy"></i> Копіювати
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3 no-print">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('recon.combat_shifts.report', $shift->id) }}" method="GET" class="form-inline">
                        <div class="btn-group btn-group-toggle mr-3" data-toggle="buttons">
                            <label class="btn btn-outline-warning {{ $activeShiftType === 'day' ? 'active' : '' }}">
                                <input type="radio" name="shift_type" value="day" {{ $activeShiftType === 'day' ? 'checked' : '' }} onchange="this.form.submit()">
                                <i class="fas fa-sun"></i> Денна
                            </label>
                            <label class="btn btn-outline-secondary {{ $activeShiftType === 'night' ? 'active' : '' }}">
                                <input type="radio" name="shift_type" value="night" {{ $activeShiftType === 'night' ? 'checked' : '' }} onchange="this.form.submit()">
                                <i class="fas fa-moon"></i> Нічна
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body p-5" id="printableArea">
                    <div class="report-content">
                        <h4 class="mb-4">Позиція "{{ $shift->position_name }}" (розвідка)</h4>

                        @php
                            $now = \Carbon\Carbon::now();
                            $startedAt = \Carbon\Carbon::parse($shift->started_at);
                            $dayOfShift = (int) $startedAt->diffInDays($now) + 1;;

                            $shiftLabel = $activeShiftType === 'day' ? 'День' : 'Ніч';

                            $dayCrew = array_filter($shift->crew, fn($member) => $member['shift_type'] === 'day');
                            $nightCrew = array_filter($shift->crew, fn($member) => $member['shift_type'] === 'night');
                        @endphp

                        <p class="mb-1"><strong>{{ $shiftLabel }} ({{ $dayOfShift }})</strong></p>
                        @if($activeShiftType === 'day')
                            @forelse($dayCrew as $member)
                                <p class="mb-0">{{ $member['callsign'] }}</p>
                            @empty
                                <p class="mb-0">Екіпаж не вказано</p>
                            @endforelse
                        @else
                            @forelse($nightCrew as $member)
                                <p class="mb-0">{{ $member['callsign'] }}</p>
                            @empty
                                <p class="mb-0">Екіпаж не вказано</p>
                            @endforelse
                        @endif

                        @php
                            $bgDrones = array_filter($shift->recon_drones, fn($d) => $d['status'] === 'active');
                            $nonBgDrones = array_filter($shift->recon_drones, fn($d) => $d['status'] !== 'active' && $d['status'] !== 'lost');

                            if ($activeShiftType === 'day') {
                                $bgDrones = array_filter($bgDrones, fn($d) => $d['shift_type'] === 'day');
                                $nonBgDrones = array_filter($nonBgDrones, fn($d) => $d['shift_type'] === 'day');
                            } else {
                                $bgDrones = array_filter($bgDrones, fn($d) => $d['shift_type'] === 'night');
                                $nonBgDrones = array_filter($nonBgDrones, fn($d) => $d['shift_type'] === 'night');
                            }
                        @endphp

                        @if(count($bgDrones) > 0)
                            <h5 class="mt-4"><strong>БГ Борти:</strong></h5>
                            @foreach($bgDrones as $drone)
                                <p class="mb-0">{{ $drone['name'] }} {{ $drone['serial_number'] }}</p>
                            @endforeach
                        @endif

                        @if(count($nonBgDrones) > 0)
                            <h5 class="mt-4"><strong>Не БГ Борти:</strong></h5>
                            @foreach($nonBgDrones as $drone)
                                <p class="mb-0">{{ $drone['name'] }} {{ $drone['serial_number'] }}</p>
                            @endforeach
                        @endif

                        @php
                            $availableAmmunition = array_filter($shift->ammunition, fn($a) => $a['quantity'] > 0);
                        @endphp

                        @if(count($availableAmmunition) > 0)
                            <h5 class="mt-4"><strong>Кількість БК:</strong></h5>
                            @foreach($availableAmmunition as $item)
                                <p class="mb-0">{{ $item['name'] }} - {{ $item['quantity'] }} шт.</p>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .report-content {
            font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            font-size: 1.1rem;
            line-height: 1.5;
            color: #212529;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .content-wrapper {
                background: white !important;
            }
            .main-header, .main-sidebar, .main-footer {
                display: none !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .p-5 {
                padding: 0 !important;
            }
        }
    </style>
@endsection

@section('js')
    <script>
        function printReport() {
            window.print();
        }

        function copyToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            } else {
                let textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.left = "-9999px";
                textArea.style.top = "0";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                return new Promise((res, rej) => {
                    document.execCommand('copy') ? res() : rej();
                    textArea.remove();
                });
            }
        }

        document.getElementById('copy-report').addEventListener('click', function() {
            copyReport(this);
        });

        function copyReport(btn) {
            const content = document.getElementById('printableArea').innerText;
            copyToClipboard(content).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Скопійовано!';
                btn.classList.replace('btn-info', 'btn-success');
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.replace('btn-success', 'btn-info');
                }, 2000);
            }).catch(err => {
                console.error('Помилка копіювання: ', err);
                alert('Не вдалося скопіювати текст');
            });
        }
    </script>
@endsection
