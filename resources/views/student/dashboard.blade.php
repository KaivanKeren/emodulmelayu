@extends('layouts.student')

@section('title', 'Dashboard Siswa')

@section('content')
    <!-- Stats Cards -->
   Hello {{Auth::user()->role}}
@endsection
