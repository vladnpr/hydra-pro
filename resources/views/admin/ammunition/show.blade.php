@extends('adminlte::page')

@section('title', 'Деталі боєприпасу')

@section('content_header')
    <h1>Деталі боєприпасу</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Інформація про боєприпас</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">ID</th>
                            <td>{{ $item->id }}</td>
                        </tr>
                        <tr>
                            <th>Назва</th>
                            <td>{{ $item->name }}</td>
                        </tr>
                        <tr>
                            <th>Статус</th>
                            <td>
                                <span class="badge badge-{{ $item->status_color }}">
                                    {{ $item->status ? 'активний' : 'неактивний'}}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('ammunition.edit', $item->id) }}" class="btn btn-info">Редагувати</a>
                    <a href="{{ route('ammunition.index') }}" class="btn btn-default float-right">Назад до списку</a>
                </div>
            </div>
        </div>
    </div>
@endsection
