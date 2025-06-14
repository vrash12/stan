<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\MedicalDetail;
use App\Models\AdmissionDetail;
use App\Models\BillingInformation;
use App\Models\InsuranceProvider;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use App\Models\Bed;  

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin')->except(['login', 'authenticate']);
    }

    public function login()
    {
        return view('auth.login');
    }

    public function createPatient()
    {
        $departments = Department::all();
        $doctors = Doctor::all();
        $insuranceProviders = InsuranceProvider::all();
        $paymentMethods = PaymentMethod::all();

        return view('admissions.create', compact(
            'departments',
            'doctors',
            'insuranceProviders',
            'paymentMethods'
        ));
    }

    public function storePatient(Request $request)
    {
        try {
            DB::beginTransaction();

        
            $validated = $request->validate([
          
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'birthday' => 'nullable|date',
                'civil_status' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|unique:patients,email',
                'address' => 'nullable|string',
                'city' => 'nullable|string',

           
                'primary_reason' => 'required|string',
                'temperature' => 'nullable|numeric',
                'blood_pressure' => 'nullable|string',
                'weight' => 'nullable|numeric',
                'height' => 'nullable|numeric',
                'heart_rate' => 'nullable|numeric',
                'medical_history' => 'nullable|array',
                'other_medical_history' => 'nullable|string',
                'allergies' => 'nullable|array',
                'other_allergies' => 'nullable|string',

     
                'admission_date' => 'required|date',
                'admission_type' => 'required|string',
                'admission_source' => 'required|string',
                'department_id' => 'required|exists:departments,department_id',
                'doctor_id' => 'required|exists:doctors,doctor_id',
                'room_number' => 'required|string',
                'bed_number' => 'required|string',
                'admission_notes' => 'nullable|string',


                'payment_method_id' => 'required|exists:payment_methods,payment_method_id',
                'insurance_provider_id' => 'nullable|exists:insurance_providers,insurance_provider_id',
                'policy_number' => 'nullable|string',
                'group_number' => 'nullable|string',
                'billing_contact_name' => 'nullable|string',
                'billing_contact_phone' => 'nullable|string',
                'billing_address' => 'nullable|string',
                'billing_city' => 'nullable|string',
                'billing_state' => 'nullable|string',
                'billing_zip' => 'nullable|string',
                'billing_notes' => 'nullable|string',
            ]);

            // Create patient
            $patient = Patient::create([
                'patient_first_name' => $validated['first_name'],
                'patient_last_name' => $validated['last_name'],
                'patient_birthday' => $validated['birthday'],
                'email' => $validated['email'] ?? null,
                'phone_number' => $validated['phone'] ?? null,
                'civil_status' => $validated['civil_status'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            // Create medical details
            MedicalDetail::create([
                'patient_id' => $patient->patient_id,
                'primary_reason' => $validated['primary_reason'],
                'temperature' => $validated['temperature'],
                'blood_pressure' => $validated['blood_pressure'],
                'weight' => $validated['weight'],
                'height' => $validated['height'],
                'heart_rate' => $validated['heart_rate'],
                'medical_history' => json_encode($validated['medical_history'] ?? []),
                'allergies' => json_encode($validated['allergies'] ?? []),
                'other_medical_history' => $validated['other_medical_history'],
                'other_allergies' => $validated['other_allergies'],
            ]);

            // Create admission details
            AdmissionDetail::create([
                'patient_id' => $patient->patient_id,
                'admission_date' => $validated['admission_date'],
                'admission_type' => $validated['admission_type'],
                'admission_source' => $validated['admission_source'],
                'department_id' => $validated['department_id'],
                'doctor_id' => $validated['doctor_id'],
                'room_number' => $validated['room_number'],
                'bed_number' => $validated['bed_number'],
                'admission_notes' => $validated['admission_notes'],
            ]);

            // Create billing information
            BillingInformation::create([
                'patient_id' => $patient->patient_id,
                'payment_method_id' => $validated['payment_method_id'],
                'insurance_provider_id' => $validated['insurance_provider_id'],
                'policy_number' => $validated['policy_number'],
                'group_number' => $validated['group_number'],
                'billing_contact_name' => $validated['billing_contact_name'],
                'billing_contact_phone' => $validated['billing_contact_phone'],
                'billing_address' => $validated['billing_address'],
                'billing_city' => $validated['billing_city'],
                'billing_state' => $validated['billing_state'],
                'billing_zip' => $validated['billing_zip'],
                'billing_notes' => $validated['billing_notes'],
            ]);

            DB::commit();

            return redirect()->route('admin.dashboard')
                           ->with('success', 'Patient admitted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error admitting patient: ' . $e->getMessage());
            return back()->with('error', 'Error admitting patient: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
public function dashboard()
{
    $recentAdmissions = AdmissionDetail::with(['patient','doctor'])
        ->latest()
        ->take(5)
        ->get();

    $pendingBillings = BillingInformation::whereNull('payment_status')
        ->with('patient')
        ->take(5)
        ->get();

   
    $availableBeds = Bed::where('status','available')->count();

    return view('admin.dashboard', [
        'admin'            => Auth::guard('admin')->user(),
        'recentAdmissions' => $recentAdmissions,
        'pendingBillings'  => $pendingBillings,
        'availableBeds'    => $availableBeds,   
    ]);
}
    public function patients()
    {
        $patients = Patient::with(['admissionDetails', 'medicalDetails', 'billingInformation'])
            ->latest()
            ->paginate(10);
        return view('admin.patients.index', compact('patients'));
    }

    public function getDepartments()
    {
        $departments = Department::all();
        return response()->json($departments);
    }

    public function getDoctorsByDepartment($departmentId)
    {
        $doctors = Doctor::where('department_id', $departmentId)->get();
        return response()->json($doctors);
    }

    public function getRoomsByDepartment($departmentId)
    {

        $rooms = Room::where('department_id', $departmentId)
            ->where('status', 'available')
            ->get();
        return response()->json($rooms);
    }

    public function getBedsByRoom($roomId)
    {
      
        $beds = Bed::where('room_id', $roomId)
            ->where('status', 'available')
            ->get();
        return response()->json($beds);
    }

    public function getInsuranceProviders()
    {
        $providers = InsuranceProvider::all();
        return response()->json($providers);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

  
    protected function generatePatientId()
    {
        $year = date('Y');
        $lastPatient = Patient::whereYear('created_at', $year)
            ->orderByDesc('patient_id')
            ->first();

        $sequence = $lastPatient ? intval(substr($lastPatient->patient_id, -4)) + 1 : 1;
        return sprintf('%d%04d', $year, $sequence);
    }

    protected function checkRoomAvailability($roomNumber, $admissionDate)
    {
        return !AdmissionDetail::where('room_number', $roomNumber)
            ->where('admission_date', $admissionDate)
            ->whereNull('discharge_date')
            ->exists();
    }

    protected function checkDoctorAvailability($doctorId, $admissionDate)
    {
      
        $maxPatientsPerDay = 10; 
        
        $currentPatients = AdmissionDetail::where('doctor_id', $doctorId)
            ->whereDate('admission_date', $admissionDate)
            ->count();

        return $currentPatients < $maxPatientsPerDay;
    }
}