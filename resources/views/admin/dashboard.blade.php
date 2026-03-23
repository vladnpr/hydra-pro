@extends('adminlte::page')

@section('title', 'Дашборд')

@section('content_header')
    <h1>Статистика бойової роботи</h1>
@endsection

@section('content')
    @if($activeShift)
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-1"></i>
                            Ваша активна зміна
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('flight_operations.index') }}" class="btn btn-tool">
                                <i class="fas fa-paper-plane mr-1"></i> До вильотів
                            </a>
                            <a href="{{ route('combat_shifts.show', $activeShift->id) }}" class="btn btn-tool">
                                <i class="fas fa-eye mr-1"></i> Деталі
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Позиція:</strong> {{ $activeShift->position_name }}
                            </div>
                            <div class="col-md-4">
                                <strong>Початок:</strong> {{ $activeShift->started_at }}
                            </div>
                            <div class="col-md-4 text-right">
                                <form action="{{ route('combat_shifts.finish', $activeShift->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Завершити чергування?')">
                                        <i class="fas fa-stop-circle mr-1"></i> Завершити
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if(!empty($activeShift->crew))
                            <div class="mt-2">
                                <strong>Екіпаж:</strong>
                                @foreach($activeShift->crew as $member)
                                    <span class="badge badge-info">{{ $member['callsign'] }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @php
        $fpvStats = $stats['total']['fpv'];
        $reconStats = $stats['total']['recon'];
        $vampireStats = $stats['total']['vampire'];
        $ugvStats = $stats['total']['ugv'];
        $fpvActiveStats = $stats['active']['fpv'];
        $reconActiveStats = $stats['active']['recon'];
        $vampireActiveStats = $stats['active']['vampire'];
        $ugvActiveStats = $stats['active']['ugv'];
        $positionsStats = $stats['positions'];
        $activeShiftsStats = $stats['active_shifts'];
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="dashboard-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="fpv-tab" data-toggle="pill" href="#fpv-content" role="tab" aria-controls="fpv-content" aria-selected="true">
                                <i class="fas fa-crosshairs mr-1"></i> FPV
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="recon-tab" data-toggle="pill" href="#recon-content" role="tab" aria-controls="recon-content" aria-selected="false">
                                <i class="fas fa-binoculars mr-1"></i> Розвідка
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="vampire-tab" data-toggle="pill" href="#vampire-content" role="tab" aria-controls="vampire-content" aria-selected="false">
                                <i class="fas fa-ghost mr-1"></i> Вампіри
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="ugv-tab" data-toggle="pill" href="#ugv-content" role="tab" aria-controls="ugv-content" aria-selected="false">
                                <i class="fas fa-truck-pickup mr-1"></i> НРК
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="dashboard-tabs-content">
                        <!-- FPV Tab -->
                        <div class="tab-pane fade show active" id="fpv-content" role="tabpanel" aria-labelledby="fpv-tab">
                            <div class="row">
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3>{{ $fpvStats['total_flights'] }}</h3>
                                            <p>Всього вильотів</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-paper-plane"></i>
                                        </div>
                                        <a href="{{ route('combat_shifts.index') }}" class="small-box-footer">Детальніше <i class="fas fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3>{{ $fpvStats['total_hits'] }}</h3>
                                            <p>Влучання</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-bullseye"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-warning">
                                        <div class="inner">
                                            <h3>{{ $fpvStats['total_area_hits'] }}</h3>
                                            <p>В районі цілі</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-crosshairs"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-danger">
                                        <div class="inner">
                                            <h3>{{ $fpvStats['total_misses'] }}</h3>
                                            <p>Втрата борту</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">Статистика детонацій</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-success"><i class="fas fa-bomb"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Детонація відбулася</span>
                                                    <span class="info-box-number text-success">{{ $fpvStats['total_detonations'] }}</span>
                                                </div>
                                            </div>
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-skull-crossbones"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Не розірвався</span>
                                                    <span class="info-box-number text-danger">{{ $fpvStats['total_non_detonations'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-outline card-info">
                                        <div class="card-header">
                                            <h3 class="card-title">Ефективність</h3>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $hitRate = $fpvStats['hit_rate'];
                                                $detonationRate = $fpvStats['detonation_rate'];

                                                $activeHitRate = $fpvActiveStats['hit_rate'];
                                                $activeDetonationRate = $fpvActiveStats['detonation_rate'];
                                            @endphp

                                            <div class="progress-group">
                                                Відсоток влучань (Загальний)
                                                <span class="float-right"><b>{{ $fpvStats['total_hits'] }}</b>/{{ $fpvStats['combat_flights_for_hit'] }}</span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-primary" style="width: {{ $hitRate }}%"></div>
                                                </div>
                                                <small>{{ $hitRate }}% від загальної кількості</small>
                                            </div>

                                            <div class="progress-group mt-3">
                                                Відсоток влучань (Активні зміни)
                                                <span class="float-right"><b>{{ $fpvActiveStats['total_hits'] }}</b>/{{ $fpvActiveStats['combat_flights_for_hit'] }}</span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-info" style="width: {{ $activeHitRate }}%"></div>
                                                </div>
                                                <small>{{ $activeHitRate }}% від активних змін</small>
                                            </div>

                                            <hr>

                                            <div class="progress-group">
                                                Надійність БК (Загальний)
                                                <span class="float-right"><b>{{ $fpvStats['total_detonations'] }}</b>/{{ $fpvStats['combat_flights_for_detonation'] }}</span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-success" style="width: {{ $detonationRate }}%"></div>
                                                </div>
                                                <small>{{ $detonationRate }}% від загальної кількості</small>
                                            </div>

                                            <div class="progress-group mt-3">
                                                Надійність БК (Активні зміни)
                                                <span class="float-right"><b>{{ $fpvActiveStats['total_detonations'] }}</b>/{{ $fpvActiveStats['combat_flights_for_detonation'] }}</span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-olive" style="width: {{ $activeDetonationRate }}%"></div>
                                                </div>
                                                <small>{{ $activeDetonationRate }}% від активних змін</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- FPV Stats by Active Shifts -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Статистика FPV по активних змінах</h3>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Зміна / Екіпаж</th>
                                                        <th>Всього вильотів</th>
                                                        <th>Влучання</th>
                                                        <th>В районі цілі</th>
                                                        <th>Втрати</th>
                                                        <th>Ефективність</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($activeShiftsStats as $shiftStat)
                                                        @if($shiftStat['type'] === 'fpv' || $shiftStat['fpv']['total_flights'] > 0)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $shiftStat['position_name'] }}</strong><br>
                                                                    @foreach($shiftStat['crew'] as $callsign)
                                                                        <span class="badge badge-info">{{ $callsign }}</span>
                                                                    @endforeach
                                                                </td>
                                                                <td>{{ $shiftStat['fpv']['total_flights'] }}</td>
                                                                <td>{{ $shiftStat['fpv']['total_hits'] }}</td>
                                                                <td>{{ $shiftStat['fpv']['total_area_hits'] }}</td>
                                                                <td>{{ $shiftStat['fpv']['total_misses'] }}</td>
                                                                <td>
                                                                    @php
                                                                        $pRate = $shiftStat['fpv']['hit_rate'];
                                                                    @endphp
                                                                    <div class="progress progress-xs">
                                                                        <div class="progress-bar bg-primary" style="width: {{ $pRate }}%"></div>
                                                                    </div>
                                                                    <small>{{ $pRate }}%</small>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                    @if(count(array_filter($activeShiftsStats, fn($s) => $s['type'] === 'fpv' || $s['fpv']['total_flights'] > 0)) === 0)
                                                        <tr>
                                                            <td colspan="6" class="text-center">Немає активних FPV змін</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recon Tab -->
                        <div class="tab-pane fade" id="recon-content" role="tabpanel" aria-labelledby="recon-tab">
                            <div class="row">
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3>{{ $reconStats['total_flights'] }}</h3>
                                            <p>Всього вильотів розвідки</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-binoculars"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3>{{ $reconStats['total_success'] }}</h3>
                                            <p>Успішні місії</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-danger">
                                        <div class="inner">
                                            <h3>{{ $reconStats['total_loosed'] }}</h3>
                                            <p>Втрата борту</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-secondary">
                                        <div class="inner">
                                            <h3>{{ $reconStats['total_other'] }}</h3>
                                            <p>Інші результати</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-question-circle"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card card-outline card-info">
                                        <div class="card-header">
                                            <h3 class="card-title">Ефективність розвідки</h3>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $reconSuccessRate = $reconStats['success_rate'];
                                                $activeReconSuccessRate = $reconActiveStats['success_rate'];
                                            @endphp

                                            <div class="progress-group">
                                                Відсоток успішних вильотів (Загальний)
                                                <span class="float-right"><b>{{ $reconStats['total_success'] }}</b>/{{ $reconStats['combat_flights_for_success'] }}</span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-success" style="width: {{ $reconSuccessRate }}%"></div>
                                                </div>
                                                <small>{{ $reconSuccessRate }}% від загальної кількості</small>
                                            </div>

                                            <div class="progress-group mt-3">
                                                Відсоток успішних вильотів (Активні зміни)
                                                <span class="float-right"><b>{{ $reconActiveStats['total_success'] }}</b>/{{ $reconActiveStats['combat_flights_for_success'] }}</span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-info" style="width: {{ $activeReconSuccessRate }}%"></div>
                                                </div>
                                                <small>{{ $activeReconSuccessRate }}% від активних змін</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recon Stats by Active Shifts -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Статистика розвідки по активних змінах</h3>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Зміна / Екіпаж</th>
                                                        <th>Всього вильотів</th>
                                                        <th>Успішні</th>
                                                        <th>Втрати</th>
                                                        <th>Ефективність</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($activeShiftsStats as $shiftStat)
                                                        @if($shiftStat['type'] === 'recon' || $shiftStat['recon']['total_flights'] > 0)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $shiftStat['position_name'] }}</strong><br>
                                                                    @foreach($shiftStat['crew'] as $callsign)
                                                                        <span class="badge badge-info">{{ $callsign }}</span>
                                                                    @endforeach
                                                                </td>
                                                                <td>{{ $shiftStat['recon']['total_flights'] }}</td>
                                                                <td>{{ $shiftStat['recon']['total_success'] }}</td>
                                                                <td>{{ $shiftStat['recon']['total_loosed'] }}</td>
                                                                <td>
                                                                    @php
                                                                        $pReconRate = $shiftStat['recon']['success_rate'];
                                                                    @endphp
                                                                    <div class="progress progress-xs">
                                                                        <div class="progress-bar bg-success" style="width: {{ $pReconRate }}%"></div>
                                                                    </div>
                                                                    <small>{{ $pReconRate }}%</small>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                    @if(count(array_filter($activeShiftsStats, fn($s) => $s['type'] === 'recon' || $s['recon']['total_flights'] > 0)) === 0)
                                                        <tr>
                                                            <td colspan="5" class="text-center">Немає активних змін розвідки</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vampire Tab -->
                        <div class="tab-pane fade" id="vampire-content" role="tabpanel" aria-labelledby="vampire-tab">
                            <div class="row">
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3>{{ $vampireStats['total_flights'] }}</h3>
                                            <p>Всього вильотів Вампіра</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-ghost"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3>{{ $vampireStats['total_success'] }}</h3>
                                            <p>Успішні місії</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-warning">
                                        <div class="inner">
                                            <h3>{{ $vampireStats['total_failed'] }}</h3>
                                            <p>Не успішні</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-danger">
                                        <div class="inner">
                                            <h3>{{ $vampireStats['total_loosed'] }}</h3>
                                            <p>Втрата борту</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card card-outline card-info">
                                        <div class="card-header">
                                            <h3 class="card-title">Ефективність Вампіра</h3>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $vampireSuccessRate = $vampireStats['success_rate'];
                                                $activeVampireSuccessRate = $vampireActiveStats['success_rate'];
                                            @endphp

                                            <div class="progress-group">
                                                Відсоток успішних вильотів (Загальний)
                                                <span class="float-right"><b>{{ $vampireStats['total_success'] }}</b>/{{ $vampireStats['combat_flights_for_success'] }}</span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-success" style="width: {{ $vampireSuccessRate }}%"></div>
                                                </div>
                                                <small>{{ $vampireSuccessRate }}% від загальної кількості</small>
                                            </div>

                                            <div class="progress-group mt-3">
                                                Відсоток успішних вильотів (Активні зміни)
                                                <span class="float-right"><b>{{ $vampireActiveStats['total_success'] }}</b>/{{ $vampireActiveStats['combat_flights_for_success'] }}</span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-info" style="width: {{ $activeVampireSuccessRate }}%"></div>
                                                </div>
                                                <small>{{ $activeVampireSuccessRate }}% від активних змін</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vampire Stats by Active Shifts -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Статистика Вампіра по активних змінах</h3>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Зміна / Екіпаж</th>
                                                        <th>Всього вильотів</th>
                                                        <th>Успішні</th>
                                                        <th>Втрати</th>
                                                        <th>Ефективність</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($activeShiftsStats as $shiftStat)
                                                        @if($shiftStat['type'] === 'vampire' || $shiftStat['vampire']['total_flights'] > 0)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $shiftStat['position_name'] }}</strong><br>
                                                                    @foreach($shiftStat['crew'] as $callsign)
                                                                        <span class="badge badge-info">{{ $callsign }}</span>
                                                                    @endforeach
                                                                </td>
                                                                <td>{{ $shiftStat['vampire']['total_flights'] }}</td>
                                                                <td>{{ $shiftStat['vampire']['total_success'] }}</td>
                                                                <td>{{ $shiftStat['vampire']['total_loosed'] }}</td>
                                                                <td>
                                                                    @php
                                                                        $pVampireRate = $shiftStat['vampire']['success_rate'];
                                                                    @endphp
                                                                    <div class="progress progress-xs">
                                                                        <div class="progress-bar bg-success" style="width: {{ $pVampireRate }}%"></div>
                                                                    </div>
                                                                    <small>{{ $pVampireRate }}%</small>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                    @if(count(array_filter($activeShiftsStats, fn($s) => $s['type'] === 'vampire' || $s['vampire']['total_flights'] > 0)) === 0)
                                                        <tr>
                                                            <td colspan="5" class="text-center">Немає активних змін Вампіра</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- UGV Tab -->
                        <div class="tab-pane fade" id="ugv-content" role="tabpanel" aria-labelledby="ugv-tab">
                            <div class="row">
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3>{{ $ugvStats['total_flights'] }}</h3>
                                            <p>Всього вильотів НРК</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-truck-pickup"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3>{{ $ugvStats['worked'] }}</h3>
                                            <p>Успішні місії</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-warning">
                                        <div class="inner">
                                            <h3>{{ $ugvStats['not_worked'] }}</h3>
                                            <p>Не успішні</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-danger">
                                        <div class="inner">
                                            <h3>{{ $ugvStats['loss'] }}</h3>
                                            <p>Втрата борту</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                        <div class="small-box-footer" style="height: 30px;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card card-outline card-info">
                                        <div class="card-header">
                                            <h3 class="card-title">Ефективність НРК</h3>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $ugvSuccessRate = $ugvStats['success_rate'];
                                                $activeUgvSuccessRate = $ugvActiveStats['success_rate'];
                                            @endphp

                                            <div class="progress-group">
                                                Відсоток успішних вильотів (Загальний)
                                                <span class="float-right"><b>{{ $ugvStats['worked'] }}</b>/{{ $ugvStats['combat_flights_for_success'] }}</span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-success" style="width: {{ $ugvSuccessRate }}%"></div>
                                                </div>
                                                <small>{{ $ugvSuccessRate }}% від загальної кількості</small>
                                            </div>

                                            <div class="progress-group mt-3">
                                                Відсоток успішних вильотів (Активні зміни)
                                                <span class="float-right"><b>{{ $ugvActiveStats['worked'] }}</b>/{{ $ugvActiveStats['combat_flights_for_success'] }}</span>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-info" style="width: {{ $activeUgvSuccessRate }}%"></div>
                                                </div>
                                                <small>{{ $activeUgvSuccessRate }}% від активних змін</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- UGV Stats by Active Shifts -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Статистика НРК по активних змінах</h3>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Зміна / Екіпаж</th>
                                                        <th>Всього вильотів</th>
                                                        <th>Успішні</th>
                                                        <th>Втрати</th>
                                                        <th>Ефективність</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($activeShiftsStats as $shiftStat)
                                                        @if($shiftStat['type'] === 'ugv' || $shiftStat['ugv']['total_flights'] > 0)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $shiftStat['position_name'] }}</strong><br>
                                                                    @foreach($shiftStat['crew'] as $callsign)
                                                                        <span class="badge badge-info">{{ $callsign }}</span>
                                                                    @endforeach
                                                                </td>
                                                                <td>{{ $shiftStat['ugv']['total_flights'] }}</td>
                                                                <td>{{ $shiftStat['ugv']['worked'] }}</td>
                                                                <td>{{ $shiftStat['ugv']['loss'] }}</td>
                                                                <td>
                                                                    @php
                                                                        $pUgvRate = $shiftStat['ugv']['success_rate'];
                                                                    @endphp
                                                                    <div class="progress progress-xs">
                                                                        <div class="progress-bar bg-success" style="width: {{ $pUgvRate }}%"></div>
                                                                    </div>
                                                                    <small>{{ $pUgvRate }}%</small>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                    @if(count(array_filter($activeShiftsStats, fn($s) => $s['type'] === 'ugv' || $s['ugv']['total_flights'] > 0)) === 0)
                                                        <tr>
                                                            <td colspan="5" class="text-center">Немає активних змін НРК</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
