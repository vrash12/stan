{{-- resources/views/layouts/patients.blade.php --}}
<!DOCTYPE html>
<html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>Patient Panel • PatientCare</title>

      {{-- Bootstrap 5 --}}
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
      {{-- Font Awesome --}}
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
      {{-- CSS --}}
      <style>
          .sidebar{width:240px; display:flex; flex-direction:column; background-color:#00529A ;}
          .logo{ width:80px } 
          .avatar { width:90px; height: 90px; background-color: aliceblue; }
          .nav-link {transition: background-color 0.2s ease; border-radius: 0.375rem;}
          .nav-link:hover { background-color: rgba(255, 255, 255, 0.2); color: #fff !important; }
          nav .icon{ width: 30px;}
          .hdng{font-size: 1.5em;}
      </style>
  </head>
  <body>

    @php
        // Pull the authenticated patient from the patient guard
        $patient = Auth::guard('patient')->user();
    @endphp

    <div class="d-flex">

      {{-- Sidebar --}}
      <aside class="sidebar bg-primary text-white p-3 vh-100 flex-shrink-0">

        {{-- Top --}}
        <div class="text-center mb-6">
            <img src="{{ asset('images/patientcare-logo-white.png') }}" alt="Logo" class="logo img-fluid mt-2 mb-4">
            <div class="avatar rounded-circle mx-auto mb-2"></div>
            <strong>{{ $patient->username ?? 'Patient User' }}</strong><br>
            <small>Patient ID: {{ Auth::guard('patient')->id() }}</small>
        </div>

        {{-- Navigation --}}
        <nav class="mb-auto ms-2 mt-4">
            <a href="{{ route('patient.dashboard') }}" 
               class="nav-link d-flex text-white gap-2 px-2 py-2">
                <span class="icon justify-content-center align-items-center">
                    <i class="fas fa-home fa-xl"></i>
                </span>
                <span class="ms-2">Home</span>
            </a>

            <a href="#" 
               class="nav-link d-flex text-white gap-2 px-2 py-2">
                <span class="icon justify-content-center align-items-center">
                    <i class="fas fa-user-circle fa-xl"></i>
                </span>
                <span class="ms-2">My Account</span>
            </a>

            <a href="#" 
               class="nav-link d-flex text-white gap-2 px-2 py-2">
               <span class="icon justify-content-center align-items-center">
                    <i class="fas fa-file-invoice-dollar fa-xl"></i>
               </span>
                <span class="ms-2">Billing</span>
            </a>

            <a href="#" 
               class="nav-link d-flex text-white gap-2 px-2 py-2">
               <span class="icon justify-content-center align-items-center">
                    <i class="fas fa-bell fa-xl"></i>
               </span>
                <span class="ms-2">Notifications</span>
            </a>
        </nav>

        {{-- Footer --}}
        <div class="footer text-center mt-auto pt-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light w-100 text-start p-2">
                    <i class="fas fa-sign-out-alt fa-xl me-2"></i> Logout
                </button>
            </form>
            <small class="d-block mt-3">PatientCare © {{ date('Y') }}</small>
            <sup>V1.0.0</sup>
        </div>

      </aside>

      {{-- Main Content --}}
      <main class="flex-grow-1 p-4">
          @yield('content')
      </main>

    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
  </body>
</html>
