@extends('layouts.teacher')

@section('title', 'Dashboard Guru')

@section('content')
    <!-- Stats Cards -->
   Yth. Bpk/Ibu {{Auth::user()->role}}
@endsection
