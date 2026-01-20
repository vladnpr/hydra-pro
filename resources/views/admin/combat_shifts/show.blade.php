@extends('adminlte::page')

@section('title', 'Деталі чергування')

@section('content_header')
    <h1>Чергування #{{ $shift->id }}</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Основна інформація</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">Позиція</th>
                            <td>{{ $shift->position_name }}</td>
                        </tr>
                        <tr>
                            <th>Статус</th>
                            <td>
                                <span class="badge badge-{{ $shift->status_color }}">
                                    {{ $shift->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Час початку</th>
                            <td>{{ $shift->started_at }}</td>
                        </tr>
                        <tr>
                            <th>Час завершення</th>
                            <td>{{ $shift->ended_at ?? 'В процесі' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('combat_shifts.index') }}" class="btn btn-default">Назад до списку</a>
                    <a href="{{ route('combat_shifts.edit', $shift->id) }}" class="btn btn-info float-right">Редагувати</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Ресурси на чергуванні</h3>
                </div>
                <div class="card-body">
                    <h5>Дрони</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Назва</th>
                                <th>Кількість</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shift->drones as $drone)
                                <tr>
                                    <td>{{ $drone['name'] }}</td>
                                    <td>{{ $drone['quantity'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">Дрони не вказані</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <h5 class="mt-4">Боєприпаси</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Назва</th>
                                <th>Кількість</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shift->ammunition as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['quantity'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">Боєприпаси не вказані</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
