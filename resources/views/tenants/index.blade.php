@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        {{ __('Tenants') }}
                        <a href="{{route('tenants.create')}}" class="btn btn-primary btn-sm float-end">
                            {{ __('Tenant') }}
                        </a>
                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th scope="col">{{ __('#') }}</th>
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col">{{ __('Email') }}</th>
                                    <th scope="col">{{ __('Domain') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($tenants as $tenant)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>{{ $tenant['name'] }}</td>
                                        <td>{{ $tenant['email'] }}</td>
                                        <td>
{{--                                            <ul>--}}
                                                @foreach($tenant['domains'] as $domain)
                                                    {{$domain['domain']}} {{$loop->last?'':','}}
{{--                                                    <li>{{ $domain['domain'] }}</li>--}}
                                                @endforeach
{{--                                            </ul>--}}
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
