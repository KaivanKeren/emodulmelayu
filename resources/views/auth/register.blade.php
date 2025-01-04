<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register</title>
</head>

<body>
    <h1 class="">Register</h1>
    <form action="{{ route('postRegister') }}" method="post">
        @csrf
        <label for="name">Name</label>
        <input type="text" name="name" id="name">
        <label for="email">Email</label>
        <input type="email" name="email" id="email">
        <label for="school">Asal Sekolah:</label>
        <select name="school" id="school">
            <option value="smk 1 riau">SMK 1 RIAU</option>
            <option value="smk 2 riau">SMK 2 RIAU</option>
            <option value="smk 3 riau">SMK 3 RIAU</option>
        </select>
        <label for="nisn_nip">NISN/NIP</label>
        <input type="number" name="nisn_nip" id="nisn_nip">
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation">
        <button type="submit">Register</button>
    </form>
    <a href="{{ route('login') }}">Login</a>
</body>

</html>
