<?php

use App\Http\Controllers\Some\AppointmentController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ClaveUnicaController;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Home;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Parameter\PermissionController;
use App\Http\Controllers\Parameter\OrganizationController;
use App\Http\Controllers\Profile\ProfileController;

use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Some\ObservationController;

use App\Http\Controllers\PatientController;
use App\Http\Controllers\ContactPatientController;

use App\Http\Controllers\Fq\CysticFibrosisRequest;
use App\Http\Controllers\Fq\ContactUserController;
use App\Http\Controllers\Fq\FqRequestController;
use App\Http\Controllers\Some\LocationController;
use App\Http\Controllers\Surveys\TeleconsultationSurveyController;

use App\Http\Controllers\MedicalProgrammer\OperatingRoomProgrammingController;
use App\Http\Controllers\MedicalProgrammer\RrhhController;
use App\Http\Controllers\MedicalProgrammer\ContractController;
use App\Http\Controllers\MedicalProgrammer\ActivityController;
use App\Http\Controllers\MedicalProgrammer\SubActivityController;
use App\Http\Controllers\MedicalProgrammer\TheoreticalProgrammingController;
use App\Http\Controllers\MedicalProgrammer\UnscheduledProgrammingController;
use App\Http\Controllers\MedicalProgrammer\CalendarProgrammingController;
use App\Http\Controllers\MedicalProgrammer\OperatingRoomController;
use App\Http\Controllers\MedicalProgrammer\MotherActivityController;
use App\Http\Controllers\MedicalProgrammer\ServiceController;
use App\Http\Controllers\MedicalProgrammer\SpecialtyController;
use App\Http\Controllers\MedicalProgrammer\ProfessionController;
use App\Http\Controllers\MedicalProgrammer\CutOffDateController;
use App\Http\Controllers\MedicalProgrammer\CloneController;
use App\Http\Controllers\MedicalProgrammer\ReportController;
use App\Http\Controllers\MedicalProgrammer\ProgrammingProposalController;
use App\Http\Controllers\MedicalProgrammer\ProgrammingProposalDetailController;
use App\Http\Controllers\MedicalProgrammer\ProgrammingProposalSignatureFlowController;

use App\Http\Controllers\Aps\MinorAuthorizationController;
use App\Http\Controllers\Aps\AuthorizationTypeController;

use App\Http\Controllers\MedicalLicenceController;
use App\Http\Livewire\Some\AsignAppointment;
use App\Http\Livewire\Some\Reallocate;
use App\Http\Livewire\Some\Interconsultation;
use App\Http\Livewire\Some\ReallocationPending;
use App\Http\Livewire\Some\AppointedAvailable;
use App\Http\Livewire\Some\OpenPending;
use App\Models\Some\Appointment;
use App\Http\Controllers\AbsenceController;

use App\Http\Controllers\RayenController;
use App\Http\Controllers\TestController;

use App\Http\Controllers\RayenWs\SoapController;
use Spatie\Permission\Contracts\Role;



use App\Http\Controllers\CoordinateController;
use App\Http\Livewire\Samu\Dashboard\DashboardIndex;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* Grabar en Google storage  */
// $disk = \Storage::disk('gcs');
// $url = $disk->put('FILE.txt',"hola");

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

//Auth::routes();

/** iOnline login temporal, eliminar parametro {route?} cuando se deje de ocupar */
/** Tuve que bajar el metodo "autenticar" porque tienen un comodin y coincide con callback y logout */
Route::get('/claveunica/callback', [ClaveUnicaController::class, 'callback']);
Route::get('/claveunica/callback-testing', [ClaveUnicaController::class, 'callback']);
Route::get('/claveunica/logout', [ClaveUnicaController::class, 'logout'])->name('claveunica.logout');
Route::get('/claveunica/{route?}', [ClaveUnicaController::class, 'autenticar'])->name('claveunica.login');


Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('authenticate');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');



/** Ejempo con livewire */
//Route::get('/home', Home::class)->middleware('auth')->name('home');
Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');

Route::prefix('parameter')->as('parameter.')->middleware('auth')->group(function () {
    Route::resource('permission', PermissionController::class);
    Route::prefix('organization')->as('organization.')->middleware('auth')->group(function () {
        Route::get('/{type}', [OrganizationController::class, 'index'])->name('index');
        Route::get('/edit', [OrganizationController::class, 'edit'])->name('edit');
    });
    //Route::get('organization/index/{type?}', [OrganizationController::class, 'index'])->name('index');
    //Route::resource('organization', OrganizationController::class);
});

Route::prefix('profile')->name('profile.')->middleware('auth')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('show');
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/', [ProfileController::class, 'update'])->name('update');
    Route::prefix('observation')->name('observation.')->group(function () {
        Route::get('/', [ObservationController::class, 'index'])->name('index');
        Route::get('/download/{id}', [ObservationController::class, 'download'])->name('download');
    });
});

Route::prefix('user')->name('user.')->middleware('auth')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::get('/search_by_name', [UserController::class, 'searchByName'])->name('search_by_name');
    Route::get('{user}/switch', [UserController::class, 'switch'])->name('switch');
});
Route::prefix('patient')->name('patient.')->middleware('auth')->group(function () {
    Route::get('/', [PatientController::class, 'index'])->name('index');
    Route::post('/', [PatientController::class, 'store'])->name('store');
    Route::get('/create', [PatientController::class, 'create'])->name('create');
    Route::get('/create-from-sic/{interconsultationId?}', [PatientController::class, 'create'])->name('create_from_sic');
    Route::get('/{patient}', [PatientController::class, 'show'])->name('show');
    Route::post('/{patient}', [PatientController::class, 'update'])->name('update');
    Route::delete('/{patient}', [PatientController::class, 'destroy'])->name('destroy');
    // Route::get('/{patient}/edit', [PatientController::class, 'edit'])->name('edit');
    Route::match(['get', 'post'], '/{patient}/edit', [PatientController::class, 'edit'])->name('edit');
});

Route::prefix('some')->name('some.')->middleware('auth')->group(function () {
    Route::get('/appointment/{appointmentId?}', AsignAppointment::class)->name('appointment');
    Route::get('/appointment-from-sic/{interconsultationId?}', AsignAppointment::class)->name('appointment.from_interconsultation');
    Route::get('/appointment-pending-practitioner/{pendingPractitionerId}/{from}/{to}', AsignAppointment::class)->name('appointment.pending_practitioner');
    Route::get('/reallocate', Reallocate::class)->name('reallocate');
    // Route::view('/agenda', 'some.agenda')->name('agenda');
    Route::get('/interconsultation', Interconsultation::class)->name('interconsultation');

    Route::get('/agenda', [AppointmentController::class, 'agenda'])->name('agenda');
    Route::get('/reallocation_pending', ReallocationPending::class)->name('reallocationPending');
    Route::post('/open_agenda', [AppointmentController::class, 'openAgenda'])->name('openAgenda');
    Route::match(['get', 'post'], '/open_tprogrammer/{programmingProposal?}', [AppointmentController::class, 'openTProgrammerView'])->name('open_tprogrammer');
    Route::get('appointment_detail/{id}', [AppointmentController::class, 'appointment_detail'])->name('appointment_detail');
    Route::get('/appointed_available', AppointedAvailable::class)->name('appointedAvailable');
    Route::get('/open_pending', OpenPending::class)->name('openPending');


    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('index');
        Route::post('/', [LocationController::class, 'store'])->name('store');
        Route::get('/create', [LocationController::class, 'create'])->name('create');
        Route::get('/{location}', [LocationController::class, 'show'])->name('show');
        Route::put('/{location}', [LocationController::class, 'update'])->name('update');
        Route::delete('/{location}', [LocationController::class, 'destroy'])->name('destroy');
        Route::get('/{location}/edit', [LocationController::class, 'edit'])->name('edit');
    });

    Route::prefix('observations')->name('observations.')->group(function () {
        Route::get('/', [ObservationController::class, 'index'])->name('index');
        Route::post('/', [ObservationController::class, 'store'])->name('store');
        Route::get('/create', [ObservationController::class, 'create'])->name('create');
        Route::get('/{observation}', [ObservationController::class, 'show'])->name('show');
        Route::put('/{observation}', [ObservationController::class, 'update'])->name('update');
        Route::delete('/{observation}', [ObservationController::class, 'destroy'])->name('destroy');
        Route::get('/{observation}/edit', [ObservationController::class, 'edit'])->name('edit');
    });
});

Route::prefix('aps')->name('aps.')->middleware('auth')->group(function () {
    Route::prefix('minor_authorizations')->name('minor_authorizations.')->group(function () {
        Route::get('parents_index', [MinorAuthorizationController::class, 'parents_index'])->name('parents_index');

        Route::get('/', [MinorAuthorizationController::class, 'index'])->name('index');
        Route::post('/', [MinorAuthorizationController::class, 'store'])->name('store');
        Route::get('/{type_id}/create', [MinorAuthorizationController::class, 'create'])->name('create');
        Route::get('/{minorAuthorization}', [MinorAuthorizationController::class, 'show'])->name('show');
        Route::put('/{minorAuthorization}', [MinorAuthorizationController::class, 'update'])->name('update');
        Route::delete('/{minorAuthorization}', [MinorAuthorizationController::class, 'destroy'])->name('destroy');
        Route::get('/{minorAuthorization}/edit', [MinorAuthorizationController::class, 'edit'])->name('edit');
    });

    Route::prefix('authorization_types')->name('authorization_types.')->group(function () {
        Route::get('/', [AuthorizationTypeController::class, 'index'])->name('index');
        Route::post('/', [AuthorizationTypeController::class, 'store'])->name('store');
        Route::get('/create', [AuthorizationTypeController::class, 'create'])->name('create');
        Route::get('/{authorizationType}', [AuthorizationTypeController::class, 'show'])->name('show');
        Route::put('/{authorizationType}', [AuthorizationTypeController::class, 'update'])->name('update');
        Route::delete('/{authorizationType}', [AuthorizationTypeController::class, 'destroy'])->name('destroy');
        Route::get('/{authorizationType}/edit', [AuthorizationTypeController::class, 'edit'])->name('edit');
    });
});

Route::prefix('fq')->as('fq.')->group(function () {
    Route::get('/', [CysticFibrosisRequest::class, 'index'])->name('index');
    Route::get('/home', [CysticFibrosisRequest::class, 'home'])->name('home')->middleware('auth');
    Route::prefix('contact_user')->name('contact_user.')->middleware(['permission:Fq: admin'])->group(function () {
        Route::get('/', [ContactUserController::class, 'index'])->name('index')->middleware('auth');
        Route::get('/create', [ContactUserController::class, 'create'])->name('create')->middleware('auth');
        Route::get('/store/{user}', [ContactUserController::class, 'store'])->name('store')->middleware('auth');
        Route::get('/addPatient/{contactUser}', [ContactUserController::class, 'addPatient'])->name('addPatient')->middleware('auth');
        Route::get('/storeAddPatient/{contactUser}/{user}', [ContactUserController::class, 'storeAddPatient'])->name('storeAddPatient')->middleware('auth');
    });
    Route::prefix('request')->name('request.')->middleware('auth')->group(function () {
        Route::get('/', [FqRequestController::class, 'index'])->name('index')
            ->middleware(['permission:Fq: answer request dispensing|Fq: admin']);
        Route::get('/own_index', [FqRequestController::class, 'own_index'])->name('own_index');
        Route::get('/create', [FqRequestController::class, 'create'])->name('create');
        Route::post('/store/{contactUser}', [FqRequestController::class, 'store'])->name('store');
        Route::put('/{fqRequest}', [FqRequestController::class, 'update'])->name('update')
            ->middleware(['permission:Fq: answer request dispensing|Fq: admin']);
        Route::get('/view_file/{requestFile}', [FqRequestController::class, 'view_file'])->name('view_file');
    });
});

Route::prefix('surveys')->as('surveys.')->middleware('auth')->group(function () {
    Route::prefix('teleconsultation')->name('teleconsultation.')->group(function () {
        Route::get('/', [TeleconsultationSurveyController::class, 'index'])->name('index');
        Route::get('/own_index', [TeleconsultationSurveyController::class, 'own_index'])->name('own_index');
        Route::get('/create', [TeleconsultationSurveyController::class, 'create'])->name('create');
        Route::post('/store', [TeleconsultationSurveyController::class, 'store'])->name('store');
        Route::get('/my_survey', [TeleconsultationSurveyController::class, 'my_survey'])->name('my_survey');
        Route::get('/show/{teleconsultationSurvey}', [TeleconsultationSurveyController::class, 'show'])->name('show');
    });
});

Route::prefix('medical_programmer')->name('medical_programmer.')->middleware('auth')->group(function () {

    Route::prefix('operating_room_programming')->name('operating_room_programming.')->group(function () {
        Route::post('saveMyEvent', [OperatingRoomProgrammingController::class, 'saveMyEvent'])->name('saveMyEvent');
        Route::post('updateMyEvent', [OperatingRoomProgrammingController::class, 'updateMyEvent'])->name('updateMyEvent');
        Route::post('deleteMyEvent', [OperatingRoomProgrammingController::class, 'deleteMyEvent'])->name('deleteMyEvent');
        Route::post('deleteMyEventForce', [OperatingRoomProgrammingController::class, 'deleteMyEventForce'])->name('deleteMyEventForce');

        Route::get('/', [OperatingRoomProgrammingController::class, 'index'])->name('index');
        Route::post('/', [OperatingRoomProgrammingController::class, 'store'])->name('store');
        Route::get('/create', [OperatingRoomProgrammingController::class, 'create'])->name('create');
        Route::get('/{patient}', [OperatingRoomProgrammingController::class, 'show'])->name('show');
        Route::put('/{patient}', [OperatingRoomProgrammingController::class, 'update'])->name('update');
        Route::delete('/{patient}', [OperatingRoomProgrammingController::class, 'destroy'])->name('destroy');
        Route::get('/{patient}/edit', [OperatingRoomProgrammingController::class, 'edit'])->name('edit');
    });

    Route::prefix('rrhh')->name('rrhh.')->group(function () {
        Route::get('/', [RrhhController::class, 'index'])->name('index');
        Route::post('/', [RrhhController::class, 'store'])->name('store');
        Route::get('/create', [RrhhController::class, 'create'])->name('create');
        Route::get('/{user}', [RrhhController::class, 'show'])->name('show');
        Route::put('/{user}', [RrhhController::class, 'update'])->name('update');
        Route::delete('/{user}', [RrhhController::class, 'destroy'])->name('destroy');
        Route::get('/{user}/edit', [RrhhController::class, 'edit'])->name('edit');
    });

    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::post('/', [ContractController::class, 'store'])->name('store');
        Route::get('/create', [ContractController::class, 'create'])->name('create');
        Route::get('/{contract}', [ContractController::class, 'show'])->name('show');
        Route::put('/{contract}', [ContractController::class, 'update'])->name('update');
        Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy');
        Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
    });

    Route::prefix('activities')->name('activities.')->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('index');
        Route::post('/', [ActivityController::class, 'store'])->name('store');
        Route::get('/create', [ActivityController::class, 'create'])->name('create');
        Route::get('/{activity}', [ActivityController::class, 'show'])->name('show');
        Route::put('/{activity}', [ActivityController::class, 'update'])->name('update');
        Route::delete('/{activity}', [ActivityController::class, 'destroy'])->name('destroy');
        Route::get('/{activity}/edit', [ActivityController::class, 'edit'])->name('edit');
    });

    Route::prefix('subactivities')->name('subactivities.')->group(function () {
        Route::get('/', [SubActivityController::class, 'index'])->name('index');
        Route::post('/', [SubActivityController::class, 'store'])->name('store');
        Route::get('/create', [SubActivityController::class, 'create'])->name('create');
        Route::get('/{subactivity}', [SubActivityController::class, 'show'])->name('show');
        Route::put('/{subactivity}', [SubActivityController::class, 'update'])->name('update');
        Route::delete('/{subactivity}', [SubActivityController::class, 'destroy'])->name('destroy');
        Route::get('/{subactivity}/edit', [SubActivityController::class, 'edit'])->name('edit');
    });

    Route::prefix('theoretical_programming')->name('theoretical_programming.')->group(function () {
        Route::post('saveMyEvent', [TheoreticalProgrammingController::class, 'saveMyEvent'])->name('saveMyEvent');
        Route::post('updateMyEvent', [TheoreticalProgrammingController::class, 'updateMyEvent'])->name('updateMyEvent');
        Route::post('deleteMyEvent', [TheoreticalProgrammingController::class, 'deleteMyEvent'])->name('deleteMyEvent');
        Route::post('deleteMyEventForce', [TheoreticalProgrammingController::class, 'deleteMyEventForce'])->name('deleteMyEventForce');
        Route::post('editMyEvent', [TheoreticalProgrammingController::class, 'editMyEvent'])->name('editMyEvent');

        Route::get('event_detail/{rut}/{activity_id}/{contract_id}/{specialty_id}/{profession_id}/{start_date}/{end_date}/{year}', [TheoreticalProgrammingController::class, 'event_detail'])->name('event_detail');
        Route::post('deleteMyEventId/{id}', [TheoreticalProgrammingController::class, 'deleteMyEventId'])->name('deleteMyEventId');
        Route::get('proposal_programmer', [TheoreticalProgrammingController::class, 'proposal_programmer'])->name('proposal_programmer');

        // Route::get('event_detail/{info}', [TheoreticalProgrammingController::class, 'event_detail'])->name('event_detail');
        // Route::post('event_detail', [TheoreticalProgrammingController::class, 'event_detail'])->name('event_detail');

        Route::get('/', [TheoreticalProgrammingController::class, 'index'])->name('index');
        Route::post('/', [TheoreticalProgrammingController::class, 'store'])->name('store');
        Route::get('/create', [TheoreticalProgrammingController::class, 'create'])->name('create');
        Route::get('/{theoreticalProgramming}', [TheoreticalProgrammingController::class, 'show'])->name('show');
        Route::put('/{theoreticalProgramming}', [TheoreticalProgrammingController::class, 'update'])->name('update');
        Route::delete('/{theoreticalProgramming}', [TheoreticalProgrammingController::class, 'destroy'])->name('destroy');
        Route::get('/{theoreticalProgramming}/edit', [TheoreticalProgrammingController::class, 'edit'])->name('edit');
    });

    Route::prefix('unscheduled_programming')->name('unscheduled_programming.')->group(function () {
        Route::get('/', [UnscheduledProgrammingController::class, 'index'])->name('index');
        Route::post('/', [UnscheduledProgrammingController::class, 'store'])->name('store');
        Route::get('/create', [UnscheduledProgrammingController::class, 'create'])->name('create');
        Route::get('/{theoreticalProgramming}', [UnscheduledProgrammingController::class, 'show'])->name('show');
        Route::put('/{theoreticalProgramming}', [UnscheduledProgrammingController::class, 'update'])->name('update');
        Route::delete('/{theoreticalProgramming}', [UnscheduledProgrammingController::class, 'destroy'])->name('destroy');
        Route::get('/{theoreticalProgramming}/edit', [UnscheduledProgrammingController::class, 'edit'])->name('edit');
    });

    Route::prefix('calendar_programming')->name('calendar_programming.')->group(function () {
        Route::get('indexbox', [CalendarProgrammingController::class, 'indexbox'])->name('indexbox');
        Route::post('saveMyEvent', [CalendarProgrammingController::class, 'saveMyEvent'])->name('saveMyEvent');
        Route::post('updateMyEvent', [CalendarProgrammingController::class, 'updateMyEvent'])->name('updateMyEvent');
        Route::post('deleteMyEvent', [CalendarProgrammingController::class, 'deleteMyEvent'])->name('deleteMyEvent');
        Route::post('deleteMyEventForce', [CalendarProgrammingController::class, 'deleteMyEventForce'])->name('deleteMyEventForce');
        Route::post('programed_in_pavilions', [CalendarProgrammingController::class, 'programed_in_pavilions'])->name('programed_in_pavilions');

        Route::get('/', [CalendarProgrammingController::class, 'index'])->name('index');
        Route::post('/', [CalendarProgrammingController::class, 'store'])->name('store');
        Route::get('/create', [CalendarProgrammingController::class, 'create'])->name('create');
        Route::get('/{calendarProgramming}', [CalendarProgrammingController::class, 'show'])->name('show');
        Route::put('/{calendarProgramming}', [CalendarProgrammingController::class, 'update'])->name('update');
        Route::delete('/{calendarProgramming}', [CalendarProgrammingController::class, 'destroy'])->name('destroy');
        Route::get('/{calendarProgramming}/edit', [CalendarProgrammingController::class, 'edit'])->name('edit');
    });

    Route::prefix('operating_rooms')->name('operating_rooms.')->group(function () {
        Route::get('/', [OperatingRoomController::class, 'index'])->name('index');
        Route::post('/', [OperatingRoomController::class, 'store'])->name('store');
        Route::get('/create', [OperatingRoomController::class, 'create'])->name('create');
        Route::get('/{operatingRoom}', [OperatingRoomController::class, 'show'])->name('show');
        Route::put('/{operatingRoom}', [OperatingRoomController::class, 'update'])->name('update');
        Route::delete('/{operatingRoom}', [OperatingRoomController::class, 'destroy'])->name('destroy');
        Route::get('/{operatingRoom}/edit', [OperatingRoomController::class, 'edit'])->name('edit');
    });

    Route::prefix('mother_activities')->name('mother_activities.')->group(function () {
        Route::get('/', [MotherActivityController::class, 'index'])->name('index');
        Route::post('/', [MotherActivityController::class, 'store'])->name('store');
        Route::get('/create', [MotherActivityController::class, 'create'])->name('create');
        Route::get('/{motherActivity}', [MotherActivityController::class, 'show'])->name('show');
        Route::put('/{motherActivity}', [MotherActivityController::class, 'update'])->name('update');
        Route::delete('/{motherActivity}', [MotherActivityController::class, 'destroy'])->name('destroy');
        Route::get('/{motherActivity}/edit', [MotherActivityController::class, 'edit'])->name('edit');
    });



    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::post('/', [ServiceController::class, 'store'])->name('store');
        Route::get('/create', [ServiceController::class, 'create'])->name('create');
        Route::get('/{service}', [ServiceController::class, 'show'])->name('show');
        Route::put('/{service}', [ServiceController::class, 'update'])->name('update');
        Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
        Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit');
    });

    Route::prefix('specialties')->name('specialties.')->group(function () {
        Route::get('/', [SpecialtyController::class, 'index'])->name('index');
        Route::post('/', [SpecialtyController::class, 'store'])->name('store');
        Route::get('/create', [SpecialtyController::class, 'create'])->name('create');
        Route::get('/{specialty}', [SpecialtyController::class, 'show'])->name('show');
        Route::put('/{specialty}', [SpecialtyController::class, 'update'])->name('update');
        Route::delete('/{specialty}', [SpecialtyController::class, 'destroy'])->name('destroy');
        Route::get('/{specialty}/edit', [SpecialtyController::class, 'edit'])->name('edit');
    });

    Route::prefix('professions')->name('professions.')->group(function () {
        Route::get('/', [ProfessionController::class, 'index'])->name('index');
        Route::post('/', [ProfessionController::class, 'store'])->name('store');
        Route::get('/create', [ProfessionController::class, 'create'])->name('create');
        Route::get('/{profession}', [ProfessionController::class, 'show'])->name('show');
        Route::put('/{profession}', [ProfessionController::class, 'update'])->name('update');
        Route::delete('/{profession}', [ProfessionController::class, 'destroy'])->name('destroy');
        Route::get('/{profession}/edit', [ProfessionController::class, 'edit'])->name('edit');
    });

    Route::prefix('cutoffdates')->name('cutoffdates.')->group(function () {
        Route::get('consolidated_programming', [CutOffDateController::class, 'consolidated_programming'])->name('consolidated_programming');
        Route::get('savePerformance', [CutOffDateController::class, 'savePerformance'])->name('savePerformance');

        Route::get('/', [CutOffDateController::class, 'index'])->name('index');
        Route::post('/', [CutOffDateController::class, 'store'])->name('store');
        Route::get('/create', [CutOffDateController::class, 'create'])->name('create');
        Route::get('/{cutoffdate}', [CutOffDateController::class, 'show'])->name('show');
        Route::put('/{cutoffdate}', [CutOffDateController::class, 'update'])->name('update');
        Route::delete('/{cutoffdate}', [CutOffDateController::class, 'destroy'])->name('destroy');
        Route::get('/{cutoffdate}/edit', [CutOffDateController::class, 'edit'])->name('edit');
    });

    Route::prefix('clone')->name('clone.')->group(function () {
        Route::get('/', [CloneController::class, 'index'])->name('index');
        Route::post('/', [CloneController::class, 'store'])->name('store');
        Route::get('/create', [CloneController::class, 'create'])->name('create');
        Route::get('/{theoreticalProgramming}', [CloneController::class, 'show'])->name('show');
        Route::put('/{theoreticalProgramming}', [CloneController::class, 'update'])->name('update');
        Route::delete('/{theoreticalProgramming}', [CloneController::class, 'destroy'])->name('destroy');
        Route::get('/{theoreticalProgramming}/edit', [CloneController::class, 'edit'])->name('edit');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('specialty', [OperatingRoomController::class, 'reportSpecialty'])->name('specialty');
        Route::get('by_profesional', [OperatingRoomController::class, 'reportByProfesional'])->name('by_profesional');
        Route::get('weekly', [OperatingRoomController::class, 'reportWeekly'])->name('weekly');
        Route::get('diary', [OperatingRoomController::class, 'reportDiary'])->name('diary');
        Route::get('report1', [OperatingRoomController::class, 'report1'])->name('report1');
        Route::get('reportProgramedVsTeoric', [OperatingRoomController::class, 'reportProgramedVsTeoric'])->name('reportProgramedVsTeoric');
        Route::get('urgency', [OperatingRoomController::class, 'reportUrgency'])->name('urgency');
        Route::get('reportminsal', [ReportController::class, 'export'])->name('reportminsal');
        Route::get('reportcut', [ReportController::class, 'exportcut'])->name('reportcut');
        Route::get('pendingPractitionersReport', [ReportController::class, 'pendingPractitionersReport'])->name('pendingPractitionersReport');
    });

    Route::prefix('programming_proposal')->name('programming_proposal.')->group(function () {
        Route::get('/programming_by_practioner', [ProgrammingProposalController::class, 'programming_by_practioner'])->name('programming_by_practioner');
        Route::get('/consolidated_programmings', [ProgrammingProposalController::class, 'consolidated_programmings'])->name('consolidated_programmings');

        Route::get('/', [ProgrammingProposalController::class, 'index'])->name('index');
        Route::post('/', [ProgrammingProposalController::class, 'store'])->name('store');
        Route::get('/create', [ProgrammingProposalController::class, 'create'])->name('create');
        Route::get('/{programmingProposal}', [ProgrammingProposalController::class, 'show'])->name('show');
        Route::put('/{programmingProposal}', [ProgrammingProposalController::class, 'update'])->name('update');
        Route::delete('/{programmingProposal}', [ProgrammingProposalController::class, 'destroy'])->name('destroy');
        Route::get('/{programmingProposal}/edit', [ProgrammingProposalController::class, 'edit'])->name('edit');

        Route::put('/{programmingProposal}', [ProgrammingProposalController::class, 'store_confirmation'])->name('store_confirmation');

        Route::prefix('details')->name('details.')->group(function () {
            Route::get('/create/{programmingProposal}', [ProgrammingProposalDetailController::class, 'create'])->name('create');
            Route::post('/', [ProgrammingProposalDetailController::class, 'store'])->name('store');
            Route::delete('/{programmingProposalDetail}', [ProgrammingProposalDetailController::class, 'destroy'])->name('destroy');
        });
    });
});

// Route::prefix('dummy')->name('dummy.')->group(function(){
//    Route::view('/some', 'some')->name('some');
//    Route::view('/crear_usuario', 'crear_usuario')->name('crear_usuario');
//    Route::view('/traspaso_bloqueos', 'traspaso_bloqueos')->name('traspaso');
//    Route::view('/agenda', 'agenda')->name('agenda');
//    Route::view('/lista-espera', 'lista_espera')->name('lista_espera');
// });

Route::prefix('test')->name('test.')->group(function () {
    Route::view('/livesearch', 'test.livesearch')->name('livesearch');
    Route::view('/fonasa', 'test.fonasa');
});

Route::prefix('medical-licence')->name('medical_licence.')->group(function () {
    Route::get('/find-user', [MedicalLicenceController::class, 'findUserForm'])->name('find-user-form');
    Route::post('/find-user', [MedicalLicenceController::class, 'findUser'])->name('find-user');
    Route::get('/create/{user}', [MedicalLicenceController::class, 'create'])->name('create');
    Route::post('/{user}', [MedicalLicenceController::class, 'store'])->name('store');
});

Route::prefix('soap')->name('soap.')->group(function () {
    Route::any('rayen', [SoapController::class, 'server'])->name('rayen');
});


/* Rutas SAMU */
// use App\Http\Controllers\Samu\ShiftController;
// use App\Http\Controllers\Samu\MobileInServiceController;
// use App\Http\Controllers\Samu\ShiftMobileController;
// use App\Http\Controllers\Samu\KeyController;
// use App\Http\Controllers\Samu\MobileController;
// use App\Http\Controllers\Samu\EventController;
// use App\Http\Controllers\Samu\CallController;
// use App\Http\Controllers\Samu\NoveltieController;
// use App\Http\Controllers\Samu\EstablishmentController;
// use App\Http\Controllers\Samu\GpsController;
// use App\Http\Controllers\Samu\CommuneController;
// use App\Http\Livewire\Samu\Coordinate\CoordinateIndex;
// use App\Http\Livewire\Samu\FindEvent;
// use App\Http\Livewire\Samu\MobileSelector;
// use App\Http\Livewire\Samu\TimestampsAndLocation;
// use App\Http\Livewire\Samu\GetLocation;
// use App\Http\Livewire\Samu\SearchCalls;
// use App\Http\Livewire\Samu\Procedures;
// use App\Http\Livewire\Samu\Supplies;
// use App\Http\Livewire\Samu\Stadistic;
// use App\Http\Livewire\Samu\Shift\ShiftSearcher;

// Route::prefix('samu')->name('samu.')->middleware('auth')->group(function () {

// 	Route::view('/', 'samu.welcome')->name('welcome');

// 	// Route::get('/procedures', Procedures::class)->name('procedures');
// 	// Route::get('/supplies', Supplies::class)->name('supplies');
// 	//Route::get('/stadistic', Stadistic::class)->name('stadistic');

// 	Route::get('/map', [CallController::class, 'maps'])->name('map');
// 	Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

// 	Route::prefix('shifts')->name('shift.')
// 	->middleware('permission:SAMU administrador|SAMU regulador|SAMU despachador')
// 	->group(function () {
// 		Route::get('/',				[ShiftController::class, 'index'])->name('index');
// 		Route::get('/create',		[ShiftController::class, 'create'])->name('create');
// 		Route::post('/store',		[ShiftController::class, 'store'])->name('store');
// 		Route::get('/searcher',		ShiftSearcher::class)->name('searcher');
// 		Route::get('/edit/{shift}',	[ShiftController::class, 'edit'])->name('edit');
// 		Route::put('/{shift}',		[ShiftController::class, 'update'])->name('update');
// 		Route::delete('/{shift}', 	[ShiftController::class, 'destroy'])->name('destroy');
//     });

// 	Route::prefix('mobiles-in-service')->name('mobileinservice.')
// 	->middleware('permission:SAMU administrador|SAMU regulador|SAMU despachador')
// 	->group(function () {
// 		Route::get('/',							[MobileInServiceController::class, 'index'])->name('index');
// 		Route::get('/create',					[MobileInServiceController::class, 'create'])->name('create');
// 		Route::post('/store',					[MobileInServiceController::class, 'store'])->name('store');
// 		Route::get('/{mobileInService}/edit',	[MobileInServiceController::class, 'edit'])->name('edit');
// 		Route::put('/{mobileInService}',		[MobileInServiceController::class, 'update'])->name('update');
// 		Route::get('/crewedit/{mobileCrew}',	[MobileInServiceController::class, 'crewedit'])->name('crewedit');
// 		Route::put('/crewupdate/{mobileCrew}',	[MobileInServiceController::class, 'crewupdate'])->name('crewupdate');
// 		Route::delete('/{mobileInService}', 	[MobileInServiceController::class, 'destroy'])->name('destroy');
// 		Route::get('/{mobileInService}/location',[MobileInServiceController::class, 'location'])->name('location');
//     });

//     Route::prefix('crews')->name('crew.')
// 	->middleware('permission:SAMU administrador|SAMU regulador|SAMU despachador')
// 	->group(function () {
// 		Route::view('/', 'samu.crew.index')->name('index');
// 		Route::view('/create', 'samu.crew.create')->name('create');
// 		Route::view('/edit', 'samu.crew.edit')->name('edit');

//     });

// 	Route::prefix('novelties')->name('noveltie.')
// 	->middleware('permission:SAMU administrador|SAMU regulador|SAMU operador|SAMU despachador')
// 	->group(function () {
// 		Route::get('/', 			[NoveltieController::class, 'index'])->name('index');
// 		Route::post('/store', 		[NoveltieController::class, 'store'])->name('store');
// 		Route::get('/edit/{noveltie}', [NoveltieController::class, 'edit'])->name('edit');
// 		Route::put('/update/{noveltie}',[NoveltieController::class, 'update'])->name('update');
// 	});


//     Route::prefix('calls')->name('call.')
// 	->middleware('permission:SAMU administrador|SAMU regulador|SAMU operador|SAMU despachador')
// 	->group(function () {
// 		Route::get('/',				[CallController::class, 'index'])->name('index');
// 		Route::get('/ots',			[CallController::class, 'ots'])->name('ots');
// 		Route::get('/create',		[CallController::class, 'create'])->name('create');
// 		Route::get('/edit/{call}',	[CallController::class, 'edit'])->name('edit');
// 		Route::post('/store',		[CallController::class, 'store'])->name('store');
// 		Route::delete('/{call}', 	[CallController::class, 'destroy'])->name('destroy');
// 		Route::put('/update/{call}',[CallController::class, 'update'])->name('update');
// 		Route::post('/sync-events/{call}',[CallController::class, 'syncEvents'])->name('syncEvents');
// 		Route::get('/search',SearchCalls::class)->name('search');
//     });

//     Route::prefix('events')->name('event.')
// 	->middleware('permission:SAMU administrador|SAMU despachador')
// 	->group(function () {
// 		Route::get('/', 			[EventController::class, 'index'])->name('index');
// 		Route::get('/{event}/duplicate', [EventController::class, 'create'])->name('duplicate');
// 		Route::get('/create/{call?}', [EventController::class, 'create'])->name('create');
// 		Route::post('/store/{call?}', [EventController::class, 'store'])->name('store');
// 		Route::post('/store/{event?}/duplicate', [EventController::class, 'store'])->name('store.duplicate');
// 		Route::get('/edit/{event}', [EventController::class, 'edit'])->name('edit');
// 		Route::put('/update/{event}',[EventController::class, 'update'])->name('update');
// 		Route::delete('/{event}', 	[EventController::class, 'destroy'])->name('destroy');
// 		Route::get('/{event}/reopen',[EventController::class, 'reopen'])
// 			->middleware('permission:SAMU administrador')->name('reopen');
// 		Route::match(['get','post'], '/filter',	[EventController::class, 'filter'])->name('filter');
// 		Route::get('/{event}/report',[EventController::class, 'report'])
// 			->middleware('permission:SAMU administrador')->name('report');
// 		Route::get('/find', FindEvent::class);
//     });

// 	Route::prefix('keys')->name('key.')
// 	->middleware('permission:SAMU administrador')
// 	->group(function () {
// 		Route::get('/' , 			[KeyController::class, 'index'])->name('index');
// 		Route::get('/create' , 		[KeyController::class, 'create'])->name('create');
// 		Route::post('/store', 		[KeyController::class, 'store'])->name('store');
// 		Route::put('/{key}',		[KeyController::class, 'update'])->name('update');
// 		Route::get('/edit/{key}',	[KeyController::class, 'edit'])->name('edit');
// 		Route::delete('/{key}',		[KeyController::class, 'destroy'])->name('destroy');
// 	});

// 	Route::prefix('mobiles')->name('mobile.')
// 	->middleware('permission:SAMU administrador')
// 	->group(function () {
// 		Route::get('/',				[MobileController::class, 'index'])->name('index');
// 		Route::get('/create',		[MobileController::class, 'create'])->name('create');
// 		Route::post('/store',		[MobileController::class, 'store'])->name('store');
// 		Route::get('/edit/{mobile}',[MobileController::class, 'edit'])->name('edit');
// 		Route::put('/{mobile}',		[MobileController::class, 'update'])->name('update');
// 		Route::delete('/{mobile}', 	[MobileController::class, 'destroy'])->name('destroy');
// 		Route::get('/{mobile}/gps', [GpsController::class, 'index'])->name('gps');
// 		Route::get('/gps', GetLocation::class);
// 	});

// 	Route::get('/movil/event/{event}', TimestampsAndLocation::class)->name('mobiles.timestamps_locations');
// 	Route::get('/movil', MobileSelector::class)->name('mobiles.mobile_selector');

// 	Route::prefix('establishments')->name('establishment.')
// 	->middleware('permission:SAMU administrador')
// 	->group(function () {
// 		Route::get('/', 			[EstablishmentController::class, 'index'])->name('index');
// 		Route::post('/', 			[EstablishmentController::class, 'store'])->name('store');
// 	});

// 	Route::prefix('communes')->name('commune.')
// 	->middleware('permission:SAMU administrador')
// 	->group(function () {
// 		Route::get('/', 			[CommuneController::class, 'index'])->name('index');
// 		Route::post('/', 			[CommuneController::class, 'store'])->name('store');
// 	});

// 	Route::prefix('coordinates')->name('coordinate.')
// 	->group(function () {
// 		Route::get('/', CoordinateIndex::class)->name('index');
// 		Route::get('/search', [CoordinateController::class, 'search'])->name('search');
// 		Route::post('/', [CoordinateController::class, 'store'])->name('store');
// 	});
// });

Route::get('/miubicacion', [CoordinateController::class, 'create'])->name('coordinate.create');
Route::post('/miubicacion', [CoordinateController::class, 'store'])->name('coordinate.store')->middleware('throttle:2');

//fin rutas samu


// Route::resource('absences', AbsenceController::class)->only([
//   'create', 'store'
// ]);

Route::prefix('absences')->name('absences.')->group(function () {
    Route::get('/',             [AbsenceController::class, 'index'])->name('index');
    Route::get('/create',         [AbsenceController::class, 'create'])->name('create');
    Route::post('/',             [AbsenceController::class, 'store'])->name('store');
    Route::get('/load',         [AbsenceController::class, 'load'])->name('load');
    Route::post('/import',         [AbsenceController::class, 'import'])->name('import');
    Route::delete('/{absence}', [AbsenceController::class, 'destroy'])->name('destroy');
});


//Rutas Chagas
use App\Http\Controllers\Epi\SuspectCaseController;
use App\Http\Controllers\Epi\TracingController;

Route::prefix('epi')->name('epi.')->group(function () {

    Route::get('/tutorials', function () {
        return view('epi.tutorials');
    });


    //Muestra el correo de delegeado de epidemiologia 
    Route::get('/delegate-mail', [SuspectCaseController::class, 'delegateMail'])->name('delegateMail');
    Route::put('/update-mail/{organization}', [SuspectCaseController::class, 'updateMail'])->name('updateMail');

    Route::prefix('chagas')->name('chagas.')->group(function () {
        Route::get('/tutorials', [SuspectCaseController::class, 'tutorials'])->name('tutorials');
        Route::get('download/{url}', [SuspectCaseController::class, 'download'])->name('download');
        Route::get('/{suspectCase}/edit', [SuspectCaseController::class, 'edit'])->name('edit');
        Route::put('/{suspectCase}', [SuspectCaseController::class, 'update'])->name('update');
        Route::get('/resultchagasnegative', [SuspectCaseController::class, 'resultchagasnegative'])->name('resultchagasnegative');
        Route::get('/print/{suspectCase}', [SuspectCaseController::class, 'printresultchagasnegative'])->name('printresultchagasnegative');
        Route::get('/{tray}', [SuspectCaseController::class, 'index'])->name('index');
        Route::get('/{user}/create', [SuspectCaseController::class, 'create'])->name('create');
        Route::post('/', [SuspectCaseController::class, 'store'])->name('store');
        Route::post('reception/{suspectcase}', [SuspectCaseController::class, 'reception'])->name('reception');



        // Nueva ruta que llama al método downloadFile
        Route::get('download/{fileName}', [SuspectCaseController::class, 'downloadFile'])->where('fileName', '.*')->name('downloadFile');

        // Nueva ruta única para borrado
        Route::get('file/{suspect_case}/{attribute}', [SuspectCaseController::class, 'deleteFile'])->name('deleteFile');
    });

    Route::prefix('tracings')->name('tracings.')->middleware('auth')->group(function () {
        Route::get('/', [TracingController::class, 'index'])->name('index');
        Route::get('/{suspectcase}/create', [TracingController::class, 'create'])->name('create');
        Route::post('/', [TracingController::class, 'store'])->name('store');
        Route::get('/{tracing}/edit', [TracingController::class, 'edit'])->name('edit');
        Route::put('/{tracing}', [TracingController::class, 'update'])->name('update');
        
    });

    Route::prefix('contacts')->name('contacts.')->middleware('auth')->group(function () {
        Route::get('/create/{patient?}', [ContactPatientController::class, 'create'])->name('create');
        Route::post('/post', [ContactPatientController::class, 'store'])->name('store');
        Route::delete('/{contact}', [ContactPatientController::class, 'destroy'])->name('destroy');
        
    });
});
//fin rutas EPI


//Rutas control-attention
Route::prefix('vista')->name('vista.')->group(function () {
    Route::view('/', 'vista.control')->name('index');
    Route::view('/edit', 'vista.attention')->name('attention');
    Route::view('/relevant', 'vista.relevant')->name('relevant');
    Route::view('/control', 'vista.control')->name('control');
});


//Rutas control-attention
Route::prefix('vista')->name('vista.')->group(function () {
    Route::view('/', 'vista.control')->name('index');
    Route::view('/edit', 'vista.attention')->name('attention');
    Route::view('/relevant', 'vista.relevant')->name('relevant');
    Route::view('/control', 'vista.control')->name('control');
});

Route::get('/test/rayen', [RayenController::class, 'getUrgencyStatus'])->name('getUrgencyStatus');
Route::get('/test/sendip', [TestController::class, 'sendIp']);
