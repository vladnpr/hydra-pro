@extends('adminlte::page')

@section('title', 'Редагувати чергування')

@section('content_header')
    <h1>Редагувати чергування #{{ $shift->id }}</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('combat_shifts.update', $shift->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Основна інформація</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="position_id">Позиція</label>
                                    <select name="position_id" id="position_id" class="form-control @error('position_id') is-invalid @enderror" required>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" {{ old('position_id', $shift->position_name) == $position->name ? 'selected' : '' }}>
                                                {{ $position->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('position_id')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Статус</label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="opened" {{ old('status', $shift->status) == 'opened' ? 'selected' : '' }}>Відкрито</option>
                                        <option value="closed" {{ old('status', $shift->status) == 'closed' ? 'selected' : '' }}>Закрито</option>
                                    </select>
                                    @error('status')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="started_at">Час початку</label>
                                    <input type="datetime-local" name="started_at" id="started_at" class="form-control @error('started_at') is-invalid @enderror" value="{{ old('started_at', date('Y-m-d\TH:i', strtotime($shift->started_at))) }}" required>
                                    @error('started_at')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="ended_at">Час завершення</label>
                                    <input type="datetime-local" name="ended_at" id="ended_at" class="form-control @error('ended_at') is-invalid @enderror" value="{{ old('ended_at', $shift->ended_at ? date('Y-m-d\TH:i', strtotime($shift->ended_at)) : '') }}">
                                    @error('ended_at')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Наявність ресурсів</h3>
                            </div>
                            <div class="card-body">
                                <h5>Дрони</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Назва</th>
                                                <th style="width: 100px;">Кількість</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($drones as $drone)
                                                <tr>
                                                    <td>{{ $drone->name }} ({{ $drone->model }})</td>
                                                    <td>
                                                        <input type="number" name="drones[{{ $drone->id }}]" class="form-control form-control-sm" value="{{ old("drones.$drone->id", $currentDrones[$drone->id] ?? 0) }}" min="0">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <h5 class="mt-4">Боєприпаси</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Назва</th>
                                                <th style="width: 100px;">Кількість</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ammunition as $item)
                                                <tr>
                                                    <td>{{ $item->name }}</td>
                                                    <td>
                                                        <input type="number" name="ammunition[{{ $item->id }}]" class="form-control form-control-sm" value="{{ old("ammunition.$item->id", $currentAmmunition[$item->id] ?? 0) }}" min="0">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Оновити</button>
                                <a href="{{ route('combat_shifts.index') }}" class="btn btn-default float-right">Скасувати</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
